<?php
require_once 'includes/header.php';
require_once 'config/database.php';

// Handle CSV download
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fiber_connections_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Type', 'Length (m)', 'Status', 'Location', 'Notes', 'OTDR Results', 'Created At']);
    $stmt = $conn->query("SELECT name, type, length_m, status, location, notes, otdr_results, created_at FROM connections ORDER BY name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Fetch all connections
$stmt = $conn->query("SELECT name, type, length_m, status, location, notes, otdr_results, created_at FROM connections ORDER BY name");
$connections = $stmt->fetchAll();

// Summary statistics
$total = count($connections);
$total_length = 0;
foreach ($connections as $c) {
    $total_length += (int)$c['length_m'];
}
?>
<div class="about-container">
    <div class="about-header">
        <h1>Connections Report</h1>
        <p class="subtitle">Summary and export of all fiber-optic connections</p>
    </div>
    <div style="margin-bottom:2rem;">
        <strong>Total Connections:</strong> <?= $total ?><br>
        <strong>Total Fiber Length:</strong> <?= number_format($total_length, 2) ?> m
    </div>
    <a href="?download=csv" class="btn btn-primary" style="margin-bottom:1rem;">Download CSV</a>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0;">
                <th style="padding:8px; border:1px solid #ddd;">Name</th>
                <th style="padding:8px; border:1px solid #ddd;">Type</th>
                <th style="padding:8px; border:1px solid #ddd;">Length (m)</th>
                <th style="padding:8px; border:1px solid #ddd;">Status</th>
                <th style="padding:8px; border:1px solid #ddd;">Location</th>
                <th style="padding:8px; border:1px solid #ddd;">Notes</th>
                <th style="padding:8px; border:1px solid #ddd;">OTDR Results</th>
                <th style="padding:8px; border:1px solid #ddd;">Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total === 0): ?>
                <tr><td colspan="8" style="text-align:center; padding:16px;">No connections found.</td></tr>
            <?php else: ?>
                <?php foreach ($connections as $conn): ?>
                    <tr>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['name']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['type']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['length_m']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['status']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['location']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['notes']) ?></td>
                        <td style="padding:8px; border:1px solid #ddd; text-align:center;">
                            <?php if (!empty($conn['otdr_results'])): ?>
                                <a href="<?= htmlspecialchars($conn['otdr_results']) ?>" target="_blank" class="btn btn-info">View PDF</a>
                            <?php else: ?>
                                <a href="otdr_viewer.php?connection_id=<?= urlencode($conn['id']) ?>" target="_blank" class="btn btn-secondary">Upload PDF</a>
                            <?php endif; ?>
                        </td>
                        <td style="padding:8px; border:1px solid #ddd;"><?= htmlspecialchars($conn['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once 'includes/footer.php'; ?> 