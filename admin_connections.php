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

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM connections WHERE id = ?");
    $stmt->execute([$del_id]);
    echo '<div class="success-message">Connection deleted.</div>';
}

$stmt = $conn->query("SELECT id, name, location, status, length_m, otdr_results FROM connections ORDER BY name");
$connections = $stmt->fetchAll();
?>
<div class="about-container">
    <h2>Manage Connections</h2>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:8px; border:1px solid #ddd;">Name</th>
                <th style="padding:8px; border:1px solid #ddd;">Location</th>
                <th style="padding:8px; border:1px solid #ddd;">Status</th>
                <th style="padding:8px; border:1px solid #ddd;">Length (m)</th>
                <th style="padding:8px; border:1px solid #ddd;">OTDR Results</th>
                <th style="padding:8px; border:1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($connections as $conn): ?>
                <tr>
                    <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['name']) ?> </td>
                    <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['location']) ?> </td>
                    <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['status']) ?> </td>
                    <td style="padding:8px; border:1px solid #ddd;"> <?= htmlspecialchars($conn['length_m']) ?> </td>
                    <td style="padding:8px; border:1px solid #ddd; text-align:center;">
                        <?php if (!empty($conn['otdr_results'])): ?>
                            <a href="<?= htmlspecialchars($conn['otdr_results']) ?>" target="_blank" class="btn btn-info">View PDF</a>
                        <?php else: ?>
                            <a href="otdr_viewer.php?connection_id=<?= urlencode($conn['id']) ?>" target="_blank" class="btn btn-secondary">Upload PDF</a>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px; border:1px solid #ddd;">
                        <a href="?delete=<?= $conn['id'] ?>" onclick="return confirm('Are you sure you want to delete this connection?');" class="btn btn-secondary">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once 'includes/footer.php'; ?> 