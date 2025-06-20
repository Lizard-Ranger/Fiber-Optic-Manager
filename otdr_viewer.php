<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$error = '';
$success = '';
$connection_id = isset($_GET['connection_id']) ? (int)$_GET['connection_id'] : 0;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
    $upload_dir = 'uploads/otdr/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['pdf_file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file_extension !== 'pdf') {
        $error = "Please upload a .pdf file only.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload error: " . $file['error'];
    } else {
        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database with file path
            if ($connection_id > 0) {
                try {
                    $stmt = $conn->prepare("UPDATE connections SET otdr_results = ? WHERE id = ?");
                    $stmt->execute([$filepath, $connection_id]);
                    $success = "OTDR PDF uploaded successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating database: " . $e->getMessage();
                }
            }
        } else {
            $error = "Failed to save uploaded file.";
        }
    }
}

// Fetch connection info if connection_id is provided
$connection = null;
if ($connection_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM connections WHERE id = ?");
    $stmt->execute([$connection_id]);
    $connection = $stmt->fetch();
}
?>

<div class="about-container">
    <div class="about-header">
        <h1>OTDR Viewer</h1>
        <p class="subtitle">Upload and manage .pdf files</p>
    </div>

    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- File Upload Section -->
    <div class="upload-section" style="margin-bottom: 2rem; padding: 1rem; border: 2px dashed #ccc; border-radius: 8px;">
        <h3>Upload .pdf File</h3>
        <form method="POST" enctype="multipart/form-data" class="auth-form">
            <div class="form-group">
                <label for="pdf_file">Select .pdf file:</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
            </div>
            <?php if ($connection_id > 0): ?>
                <input type="hidden" name="connection_id" value="<?= $connection_id ?>">
                <p><strong>Uploading for connection:</strong> <?= htmlspecialchars($connection['name']) ?></p>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Upload PDF</button>
        </form>
    </div>

    <!-- PDF Display Section -->
    <?php if ($connection && !empty($connection['otdr_results'])): ?>
    <div class="database-file" style="margin-top: 2rem; padding: 1rem; background: #e8f4fd; border-radius: 8px;">
        <h3>View Uploaded PDF</h3>
        <p><strong>Connection:</strong> <?= htmlspecialchars($connection['name']) ?></p>
        <p><strong>File:</strong> <?= htmlspecialchars(basename($connection['otdr_results'])) ?></p>
        <a href="<?= htmlspecialchars($connection['otdr_results']) ?>" target="_blank" class="btn btn-info">View PDF</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 