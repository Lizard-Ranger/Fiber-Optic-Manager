<?php
require_once 'includes/header.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || !$user['is_admin']) {
    echo '<div class="auth-container"><h2>Access Denied</h2><p>You do not have permission to access this page.</p></div>';
    require_once 'includes/footer.php';
    exit();
}
?>
<div class="auth-container">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="admin_connections.php">Manage Connections (Delete)</a></li>
        <li><a href="admin_backup.php">Database Backup / Restore</a></li>
    </ul>
</div>
<?php require_once 'includes/footer.php'; ?> 