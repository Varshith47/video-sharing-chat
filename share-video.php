<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my-videos.php');
    exit();
}

$video_id = (int)$_GET['id'];
$video = getVideoById($video_id);

// Check if the video belongs to the current user
if (!$video || $video['user_id'] != getUserId()) {
    header('Location: my-videos.php');
    exit();
}

$users = getAllUsersExcept(getUserId());
$shared_users = getVideoSharedUsers($video_id);
$shared_user_ids = array_column($shared_users, 'id');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shared_users = isset($_POST['shared_users']) ? $_POST['shared_users'] : [];
    
    if (shareVideoWithUsers($video_id, $shared_users)) {
        $success = 'Video sharing settings updated successfully!';
        $shared_user_ids = $shared_users;
    } else {
        $error = 'Failed to update sharing settings. Please try again.';
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Share Video: <?php echo htmlspecialchars($video['title']); ?></h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <p>Select users to share this video with:</p>
                        <?php if (!empty($users)): ?>
                        <select class="form-select" name="shared_users[]" multiple size="10">
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo in_array($user['id'], $shared_user_ids) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                        <?php else: ?>
                        <div class="alert alert-info">No other users available to share with.</div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="video.php?id=<?php echo $video_id; ?>" class="btn btn-outline-secondary">Back to Video</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>