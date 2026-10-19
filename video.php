<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$video_id = (int)$_GET['id'];
$video = getVideoById($video_id);

if (!$video) {
    header('Location: index.php');
    exit();
}

// Check if user has access to this video
if (!hasAccessToVideo(getUserId(), $video_id)) {
    header('Location: index.php');
    exit();
}

// Increment views
incrementVideoViews($video_id);

// Handle like action
if (isLoggedIn() && isset($_POST['action']) && $_POST['action'] === 'like') {
    $response = toggleVideoLike(getUserId(), $video_id);
    echo json_encode($response);
    exit();
}

// Handle comment submission
$comment_error = '';
if (isLoggedIn() && isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
    $comment = trim($_POST['comment']);
    if (addVideoComment(getUserId(), $video_id, $comment) === false) {
        $comment_error = 'Failed to add comment. Please try again.';
    }
}

// Get video comments
$comments = getVideoComments($video_id);

// Get like count and check if current user liked the video
$like_count = getVideoLikesCount($video_id);
$user_liked = isLoggedIn() ? hasUserLikedVideo(getUserId(), $video_id) : false;

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <video controls class="w-100" poster="<?php echo THUMBNAIL_UPLOAD_PATH . $video['thumbnail_path']; ?>">
                    <source src="<?php echo VIDEO_UPLOAD_PATH . $video['file_path']; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <h2 class="mt-3"><?php echo htmlspecialchars($video['title']); ?></h2>
                <p class="text-muted">Uploaded by <?php echo htmlspecialchars($video['username']); ?> on <?php echo date('F j, Y', strtotime($video['upload_date'])); ?></p>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="me-3"><i class="fas fa-eye"></i> <?php echo $video['views']; ?> views</span>
                        <span id="like-count"><i class="fas fa-thumbs-up"></i> <?php echo $like_count; ?> likes</span>
                    </div>
                    <?php if (isLoggedIn()): ?>
                    <button id="like-button" class="btn btn-sm <?php echo $user_liked ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-thumbs-up"></i> <?php echo $user_liked ? 'Liked' : 'Like'; ?>
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <p><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <div class="mb-3 d-flex flex-wrap gap-2">
                    <a href="chat.php?mode=video&amp;video_id=<?php echo (int)$video['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-comments"></i> Watch &amp; Chat</a>
                <?php if ((int)$video['user_id'] === (int)getUserId()): ?>
                    <a href="share-video.php?id=<?php echo $video['id']; ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-share-alt"></i> Manage Sharing
                    </a>
                <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments (<?php echo count($comments); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (isLoggedIn()): ?>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <textarea class="form-control" name="comment" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                    <?php if ($comment_error): ?>
                    <div class="text-danger mt-2"><?php echo $comment_error; ?></div>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="login.php">Login</a> to post a comment.
                </div>
                <?php endif; ?>
                
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                    <p class="text-muted">No comments yet. Be the first to comment!</p>
                    <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <small class="text-muted"><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></small>
                        </div>
                        <p class="mb-0 mt-1"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">About the Uploader</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-circle fa-3x text-secondary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6><?php echo htmlspecialchars($video['username']); ?></h6>
                        
                    </div>
                </div>
                <p>This user has shared this video with you.</p>
            </div>
        </div>
        
        <?php if ($video['user_id'] == getUserId()): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Video Statistics</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Views
                        <span class="badge bg-primary rounded-pill"><?php echo $video['views']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Likes
                        <span class="badge bg-primary rounded-pill" id="sidebar-like-count"><?php echo $like_count; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Comments
                        <span class="badge bg-primary rounded-pill"><?php echo count($comments); ?></span>
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButton = document.getElementById('like-button');
    if (likeButton) {
        likeButton.addEventListener('click', function() {
            fetch('video.php?id=<?php echo $video_id; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=like'
            })
            .then(response => response.json())
            .then(data => {
                // Update like count
                document.getElementById('like-count').innerHTML = `<i class="fas fa-thumbs-up"></i> ${data.count} likes`;
                if (document.getElementById('sidebar-like-count')) {
                    document.getElementById('sidebar-like-count').textContent = data.count;
                }
                
                // Update button appearance
                if (data.action === 'liked') {
                    likeButton.classList.remove('btn-outline-primary');
                    likeButton.classList.add('btn-primary');
                    likeButton.innerHTML = '<i class="fas fa-thumbs-up"></i> Liked';
                } else {
                    likeButton.classList.remove('btn-primary');
                    likeButton.classList.add('btn-outline-primary');
                    likeButton.innerHTML = '<i class="fas fa-thumbs-up"></i> Like';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>