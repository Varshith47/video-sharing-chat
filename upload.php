<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $videoFile = $_FILES['video'];
    $thumbnailFile = $_FILES['thumbnail'];
    $sharedUsers = isset($_POST['shared_users']) ? $_POST['shared_users'] : [];
    
    if (empty($title) || empty($description) || empty($videoFile['name']) || empty($thumbnailFile['name'])) {
        $error = 'All fields are required.';
    } else {
        $result = uploadVideo($title, $description, $videoFile, $thumbnailFile, $sharedUsers);
        
        if ($result['success']) {
            $success = 'Video uploaded successfully!';
            header('Refresh: 2; URL=video.php?id=' . $result['video_id']);
        } else {
            $error = $result['message'];
        }
    }
}

$users = getAllUsersExcept(getUserId());

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Upload Video</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                <form method="POST" action="upload.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Video Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="video" class="form-label">Video File (MP4, WebM, OGG, max 50MB)</label>
                        <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm,video/ogg" required>
                    </div>
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">Thumbnail Image (JPG, PNG, GIF, max 5MB)</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif" required>
                    </div>
                    
                    <?php if (!empty($users)): ?>
                    <div class="mb-3">
                        <label class="form-label">Share with (select multiple if needed)</label>
                        <select class="form-select" name="shared_users[]" multiple size="5">
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">Upload Video</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>