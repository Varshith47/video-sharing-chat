</div> <!-- Close container div from header -->

<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>VideoShare Platform</h5>
                <p>Upload and share your videos with selected users.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white">Home</a></li>
                    <?php if (isLoggedIn()): ?>
                    <li><a href="upload.php" class="text-white">Upload Video</a></li>
                    <?php else: ?>
                    <li><a href="login.php" class="text-white">Login</a></li>
                    <li><a href="register.php" class="text-white">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope"></i> contact@videoshare.com</li>
                    <li><i class="fas fa-phone"></i> +1 (123) 456-7890</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date('Y'); ?> VideoShare. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="assets/js/script.js"></script>
</body>
</html>