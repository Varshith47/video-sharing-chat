<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$videos = [];
if (isLoggedIn()) {
    $videos = getVideosForUser(getUserId());
} else {
    // For non-logged in users, show public videos (owned by admin user with ID 1)
    $sql = "SELECT v.*, u.username 
            FROM videos v
            JOIN users u ON v.user_id = u.id
            WHERE v.is_public = 1
            ORDER BY v.upload_date DESC
            LIMIT 6";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="hero-section mb-5">
    <div class="hero-content text-center text-white">
        <h1 class="display-4 fw-bold">Share Your Videos Securely</h1>
        <p class="lead">Upload and share your videos with selected users only</p>
        <?php if (!isLoggedIn()): ?>
        <div class="mt-4">
            <a href="register.php" class="btn btn-primary btn-lg me-2">Sign Up</a>
            <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><?php echo isLoggedIn() ? 'Your Videos' : 'Featured Videos'; ?></h2>
    </div>
    <?php if (isLoggedIn()): ?>
    <div class="col-md-4 text-end">
        <a href="upload.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Video
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($videos)): ?>
<div class="alert alert-info">
    <?php echo isLoggedIn() ? 'You have no videos yet. Upload your first video!' : 'No videos available. Register to upload videos.'; ?>
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
                <p class="card-text text-muted">Uploaded by <?php echo htmlspecialchars($video['username']); ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-eye"></i> <?php echo $video['views']; ?> views
                        <i class="fas fa-thumbs-up ms-2"></i> <?php echo getVideoLikesCount($video['id']); ?>
                    </small>
                    <small class="text-muted"><?php echo date('M j, Y', strtotime($video['upload_date'])); ?></small>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!isLoggedIn()): ?>
<div class="row mt-5">
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                <h4 class="card-title">Easy Upload</h4>
                <p class="card-text">Upload your videos quickly and easily with our intuitive interface.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h4 class="card-title">Secure Sharing</h4>
                <p class="card-text">Share your videos only with selected users for maximum privacy.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-comments fa-3x text-primary mb-3"></i>
                <h4 class="card-title">Engage</h4>
                <p class="card-text">Get feedback with likes and comments from your selected audience.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>