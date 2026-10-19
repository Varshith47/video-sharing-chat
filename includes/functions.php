<?php
require_once __DIR__ . '/config.php';

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags($conn->real_escape_string($data)));
}

// Function to register a new user
function registerUser($username, $email, $password) {
    global $conn;
    
    $username = sanitize($username);
    $email = sanitize($email);
    $password = password_hash($password, PASSWORD_BCRYPT);
    
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    
    return $conn->query($sql);
}

// Function to login user
function loginUser($username, $password) {
    global $conn;
    
    $username = sanitize($username);
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

// Function to upload video
function uploadVideo($title, $description, $videoFile, $thumbnailFile, $sharedUsers = [], $isPublic = false) {
    global $conn;
    
    $title = sanitize($title);
    $description = sanitize($description);
    $user_id = getUserId();
    $isPublic = $isPublic ? 1 : 0;
    
    // Handle video file upload
    $videoFileName = time() . '_' . basename($videoFile['name']);
    $videoTargetPath = VIDEO_UPLOAD_PATH . $videoFileName;
    
    $videoFileType = strtolower(pathinfo($videoTargetPath, PATHINFO_EXTENSION));
    $allowedVideoTypes = ['mp4', 'webm', 'ogg'];
    
    if (!in_array($videoFileType, $allowedVideoTypes)) {
        return ['success' => false, 'message' => 'Only MP4, WebM, and OGG video formats are allowed.'];
    }
    
    if ($videoFile['size'] > 50000000) { // 50MB limit
        return ['success' => false, 'message' => 'Video file is too large. Maximum size is 50MB.'];
    }
    
    if (!move_uploaded_file($videoFile['tmp_name'], $videoTargetPath)) {
        return ['success' => false, 'message' => 'Error uploading video file.'];
    }
    
    // Handle thumbnail file upload
    $thumbnailFileName = time() . '_' . basename($thumbnailFile['name']);
    $thumbnailTargetPath = THUMBNAIL_UPLOAD_PATH . $thumbnailFileName;
    
    $thumbnailFileType = strtolower(pathinfo($thumbnailTargetPath, PATHINFO_EXTENSION));
    $allowedThumbnailTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($thumbnailFileType, $allowedThumbnailTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, and GIF thumbnail formats are allowed.'];
    }
    
    if ($thumbnailFile['size'] > 5000000) { // 5MB limit
        return ['success' => false, 'message' => 'Thumbnail file is too large. Maximum size is 5MB.'];
    }
    
    if (!move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailTargetPath)) {
        return ['success' => false, 'message' => 'Error uploading thumbnail file.'];
    }
    
    // Insert video into database
    $sql = "INSERT INTO videos (user_id, title, description, file_path, thumbnail_path, is_public) 
            VALUES ('$user_id', '$title', '$description', '$videoFileName', '$thumbnailFileName', '$isPublic')";
    
    if ($conn->query($sql)) {
        $video_id = $conn->insert_id;
        
        // Only add shared users if video is not public
        if (!$isPublic && !empty($sharedUsers)) {
            foreach ($sharedUsers as $shared_user_id) {
                $shared_user_id = (int)$shared_user_id;
                $share_sql = "INSERT INTO video_access (video_id, user_id) VALUES ('$video_id', '$shared_user_id')";
                $conn->query($share_sql);
            }
        }
        
        return ['success' => true, 'video_id' => $video_id];
    }
    return ['success' => false, 'message' => 'Error saving video to database.'];
}

// Function to get all videos accessible to the current user
function getVideosForUser($user_id) {
    global $conn;
    
    $sql = "SELECT v.*, u.username 
            FROM videos v
            JOIN users u ON v.user_id = u.id
            LEFT JOIN video_access va ON v.id = va.video_id
            WHERE v.is_public = 1 OR v.user_id = '$user_id' OR va.user_id = '$user_id'
            GROUP BY v.id
            ORDER BY v.upload_date DESC";
    
    $result = $conn->query($sql);
    $videos = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
    }
    
    return $videos;
}

// Function to get video by ID (including uploader's join date)
function getVideoById($video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $sql = "SELECT v.*, u.username, u.created_at as user_created_at
            FROM videos v
            JOIN users u ON v.user_id = u.id
            WHERE v.id = '$video_id'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Function to check if user has access to video
function hasAccessToVideo($user_id, $video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $user_id = (int)$user_id;
    
    // Check if video is public
    $public_sql = "SELECT 1 FROM videos WHERE id = '$video_id' AND is_public = 1";
    $public_result = $conn->query($public_sql);
    if ($public_result->num_rows > 0) {
        return true;
    }
    
    // Original access check
    $sql = "SELECT 1 FROM videos WHERE id = '$video_id' AND user_id = '$user_id'
            UNION
            SELECT 1 FROM video_access WHERE video_id = '$video_id' AND user_id = '$user_id'";
    
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Function to get all users except current user
function getAllUsersExcept($user_id) {
    global $conn;
    
    $user_id = (int)$user_id;
    $sql = "SELECT id, username FROM users WHERE id != '$user_id'";
    
    $result = $conn->query($sql);
    $users = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

// Function to get users with access to video
function getVideoSharedUsers($video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $sql = "SELECT u.id, u.username 
            FROM video_access va
            JOIN users u ON va.user_id = u.id
            WHERE va.video_id = '$video_id'";
    
    $result = $conn->query($sql);
    $users = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

// Function to share video with users
function shareVideoWithUsers($video_id, $user_ids) {
    global $conn;
    
    $video_id = (int)$video_id;
    
    // First remove all existing shares for this video
    $delete_sql = "DELETE FROM video_access WHERE video_id = '$video_id'";
    $conn->query($delete_sql);
    
    // Add new shares
    if (!empty($user_ids)) {
        foreach ($user_ids as $user_id) {
            $user_id = (int)$user_id;
            $insert_sql = "INSERT INTO video_access (video_id, user_id) VALUES ('$video_id', '$user_id')";
            $conn->query($insert_sql);
        }
    }
    
    return true;
}

// Function to update video public status
function updateVideoPublicStatus($video_id, $is_public) {
    global $conn;
    
    $video_id = (int)$video_id;
    $is_public = $is_public ? 1 : 0;
    
    $sql = "UPDATE videos SET is_public = '$is_public' WHERE id = '$video_id'";
    return $conn->query($sql);
}

// Function to get video likes count
function getVideoLikesCount($video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $sql = "SELECT COUNT(*) as like_count FROM likes WHERE video_id = '$video_id'";
    $result = $conn->query($sql);
    
    return $result->fetch_assoc()['like_count'];
}

// Function to check if user liked video
function hasUserLikedVideo($user_id, $video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $user_id = (int)$user_id;
    $sql = "SELECT 1 FROM likes WHERE video_id = '$video_id' AND user_id = '$user_id'";
    $result = $conn->query($sql);
    
    return $result->num_rows > 0;
}

// Function to toggle like on video
function toggleVideoLike($user_id, $video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $user_id = (int)$user_id;
    
    if (hasUserLikedVideo($user_id, $video_id)) {
        // Unlike
        $sql = "DELETE FROM likes WHERE video_id = '$video_id' AND user_id = '$user_id'";
        $conn->query($sql);
        return ['action' => 'unliked', 'count' => getVideoLikesCount($video_id)];
    } else {
        // Like
        $sql = "INSERT INTO likes (video_id, user_id) VALUES ('$video_id', '$user_id')";
        $conn->query($sql);
        return ['action' => 'liked', 'count' => getVideoLikesCount($video_id)];
    }
}

// Function to get video comments
function getVideoComments($video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $sql = "SELECT c.*, u.username 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.video_id = '$video_id'
            ORDER BY c.created_at DESC";
    
    $result = $conn->query($sql);
    $comments = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
    }
    
    return $comments;
}

// Function to add comment to video
function addVideoComment($user_id, $video_id, $comment) {
    global $conn;
    
    $video_id = (int)$video_id;
    $user_id = (int)$user_id;
    $comment = sanitize($comment);
    
    $sql = "INSERT INTO comments (video_id, user_id, comment) VALUES ('$video_id', '$user_id', '$comment')";
    
    return $conn->query($sql) ? $conn->insert_id : false;
}

// Function to increment video views
function incrementVideoViews($video_id) {
    global $conn;
    
    $video_id = (int)$video_id;
    $sql = "UPDATE videos SET views = views + 1 WHERE id = '$video_id'";
    $conn->query($sql);
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user ID
function getUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}
?>