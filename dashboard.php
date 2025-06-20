<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's data
try {
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Error fetching user data";
}

// Fetch upcoming maintenance tasks
try {
    $stmt = $conn->query("SELECT m.*, c.name AS connection_name FROM maintenance m LEFT JOIN connections c ON m.connection_id = c.id WHERE m.scheduled_date >= CURDATE() ORDER BY m.scheduled_date ASC LIMIT 3");
    $maintenance_tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $maintenance_tasks = [];
}

// Network Overview stats
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total, SUM(length_m) AS total_length FROM connections");
    $row = $stmt->fetch();
    $total_connections = (int)($row['total'] ?? 0);
    $total_length = (float)($row['total_length'] ?? 0);

    $stmt = $conn->query("SELECT COUNT(*) AS active FROM connections WHERE status = 'Active'");
    $active_connections = (int)($stmt->fetch()['active'] ?? 0);

    $network_health = $total_connections > 0 ? round(($active_connections / $total_connections) * 100) : 100;
} catch (PDOException $e) {
    $total_connections = 0;
    $total_length = 0;
    $active_connections = 0;
    $network_health = 100;
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p>Fiber-Optic Network Management Dashboard</p>
    </div>

    <div class="dashboard-grid">
        <!-- Network Overview -->
        <div class="dashboard-card" style="min-width:340px; max-width:500px; width:100%; margin-bottom:2rem;">
            <h3>Network Overview</h3>
            <hr>
            <div class="stat-item">
                <span class="stat-label">Total Fiber Length</span>
                <span class="stat-value">
                    <?= number_format($total_length, 2) ?> m (<?= number_format($total_length / 1000, 2) ?> km)
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Active Connections</span>
                <span class="stat-value"><?= $active_connections ?></span>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <h3>Quick Actions</h3>
            <div class="card-content">
                <a href="add_connection.php" class="btn btn-primary" style="display:block; margin-bottom:10px;">Add New Connection</a>
                <a href="generate_report.php" class="btn btn-secondary" style="display:block; margin-bottom:10px;">Generate Report</a>
                <a href="network_map.php" class="btn btn-secondary" style="display:block; margin-bottom:10px;">View Network Map</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 