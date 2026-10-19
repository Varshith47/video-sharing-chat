<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$videos = getVideosForUser(getUserId());

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>My Videos</h2>
    </div>
    <div class="col-md-4 text-end">
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Video
        </a>
    </div>
</div>

<?php if (empty($videos)): ?>
<div class="alert alert-info">
    You have no videos yet. <a href="upload.php">Upload your first video!</a>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($videos as $video): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <a href="video.php?id=<?php echo $video['id']; ?>">
                <img src="<?php echo THUMBNAIL_UPLOAD_PATH . $video['thumbnail_path']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($video['title']); ?>">
            </a>
            <div class="card-body">
                <h5 class="card-title">
                    <a href="video.php?id=<?php echo $video['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($video['title']); ?></a>
                </h5>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-eye"></i> <?php echo $video['views']; ?> views
                        <i class="fas fa-thumbs-up ms-2"></i> <?php echo getVideoLikesCount($video['id']); ?>
                    </small>
                    <small class="text-muted"><?php echo date('M j, Y', strtotime($video['upload_date'])); ?></small>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <?php if ($video['user_id'] == getUserId()): ?>
                <a href="share-video.php?id=<?php echo $video['id']; ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-share-alt"></i> Share
                </a>
                <?php else: ?>
                <small class="text-muted">Shared with you by <?php echo htmlspecialchars($video['username']); ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>