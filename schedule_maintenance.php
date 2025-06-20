<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$error = '';
$success = '';

// Fetch connections for dropdown
$stmt = $conn->query("SELECT id, name FROM connections ORDER BY name");
$connections = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $connection_id = $_POST['connection_id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $scheduled_date = $_POST['scheduled_date'] ?? '';

    if (empty($title) || empty($scheduled_date)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO maintenance (connection_id, title, description, scheduled_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$connection_id ?: null, $title, $description, $scheduled_date]);
            $success = "Maintenance task scheduled!";
        } catch(PDOException $e) {
            $error = "Error scheduling maintenance: " . $e->getMessage();
        }
    }
}
?>
<div class="auth-container">
    <h2>Schedule Maintenance</h2>
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="auth-form">
        <div class="form-group">
            <label for="connection_id">Connection (optional)</label>
            <select id="connection_id" name="connection_id">
                <option value="">-- General Maintenance --</option>
                <?php foreach ($connections as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="scheduled_date">Scheduled Date</label>
            <input type="date" id="scheduled_date" name="scheduled_date" required>
        </div>
        <button type="submit" class="btn btn-primary">Schedule Maintenance</button>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 