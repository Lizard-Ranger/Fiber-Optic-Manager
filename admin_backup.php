<?php
// Handle backup download BEFORE any HTML or includes
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $backupFile = '/tmp/webapp_db_backup_' . date('Ymd_His') . '.sql';
    $filteredFile = '/tmp/webapp_db_backup_filtered_' . date('Ymd_His') . '.sql';
    $cmd = "mysqldump -u fiberuser -pfiberpass123 webapp_db > $backupFile";
    system($cmd);
    if (file_exists($backupFile)) {
        // Remove lines containing 'enable the sandbox mode'
        $lines = file($backupFile);
        $filtered = array_filter($lines, function($line) {
            return stripos($line, 'enable the sandbox mode') === false;
        });
        file_put_contents($filteredFile, implode('', $filtered));
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="webapp_db_backup.sql"');
        readfile($filteredFile);
        unlink($backupFile);
        unlink($filteredFile);
        exit();
    } else {
        echo 'Backup failed.';
        exit();
    }
}

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

// Handle restore upload
$restore_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['restore_file'])) {
    $file = $_FILES['restore_file']['tmp_name'];
    if (is_uploaded_file($file)) {
        $filename = $_FILES['restore_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $first_line = trim(file_get_contents($file, false, null, 0, 256));
        if ($ext !== 'sql') {
            $restore_message = '<div class="error-message">Invalid file: Please upload a .sql backup file.</div>';
        } elseif ($first_line === '') {
            $restore_message = '<div class="error-message">Invalid file: The file is empty.</div>';
        } elseif (stripos($first_line, '<html') !== false || stripos($first_line, '<!DOCTYPE html') !== false) {
            $restore_message = '<div class="error-message">Invalid file: The file contains HTML, not SQL.</div>';
        } elseif (
            strpos($first_line, '--') !== 0 &&
            stripos($first_line, 'create') !== 0 &&
            stripos($first_line, 'insert') !== 0
        ) {
            $restore_message = '<div class="error-message">Invalid file: The file does not appear to be a valid SQL dump.</div>';
        } else {
            $tmpfile = '/tmp/restore_' . uniqid() . '.sql';
            if (move_uploaded_file($file, $tmpfile)) {
                $cmd = "mysql -u fiberuser -pfiberpass123 webapp_db < " . escapeshellarg($tmpfile) . " 2>&1";
                exec($cmd, $output, $retval);
                unlink($tmpfile);
                if ($retval === 0) {
                    $restore_message = '<div class="success-message">Database restored successfully.</div>';
                } else {
                    $restore_message = '<div class="error-message">Restore failed.<br><pre>' . htmlspecialchars(implode("\n", $output)) . '</pre></div>';
                }
            } else {
                $restore_message = '<div class="error-message">Failed to move uploaded file.</div>';
            }
        }
    } else {
        $restore_message = '<div class="error-message">Invalid file upload.</div>';
    }
}
?>
<div class="auth-container">
    <h2>Database Backup / Restore</h2>
    <a href="?action=backup" class="btn btn-primary" style="margin-bottom:1rem;">Download Database Backup</a>
    <hr>
    <h3>Restore Database</h3>
    <?php if ($restore_message) echo $restore_message; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="restore_file">Upload SQL Backup File</label>
            <input type="file" id="restore_file" name="restore_file" accept=".sql" required>
        </div>
        <button type="submit" class="btn btn-secondary">Restore Database</button>
    </form>
    <p style="color:#c62828; margin-top:1rem;">Warning: Restoring a backup will overwrite all current data!</p>
</div>
<?php require_once 'includes/footer.php'; ?> 