<?php
require_once 'includes/header.php';
?>

<div class="welcome-section">
    <h1>Welcome to Fiber-Optic Manager</h1>
    <p>Comprehensive management system for fiber-optic network infrastructure.</p>
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="cta-buttons">
            <a href="/login.php" class="btn">Login</a>
            <a href="/register.php" class="btn">Register</a>
        </div>
    <?php endif; ?>
</div>

<div class="features">
    <h2>Features</h2>
    <div class="feature-grid">
        <div class="feature-item">
            <h3>Network Management</h3>
            <p>Comprehensive fiber-optic network monitoring and management</p>
        </div>
        <div class="feature-item">
            <h3>Infrastructure Tracking</h3>
            <p>Detailed tracking of fiber-optic infrastructure components</p>
        </div>
        <div class="feature-item">
            <h3>Maintenance Scheduling</h3>
            <p>Efficient maintenance and repair scheduling system</p>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
