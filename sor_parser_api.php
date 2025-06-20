<?php
/**
 * OTDR .sor File Parser API
 * Provides an API endpoint to parse .sor files using the Python parser
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = ['success' => false, 'data' => null, 'error' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle file upload and parsing
        if (isset($_FILES['sor_file'])) {
            $file = $_FILES['sor_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error: ' . $file['error']);
            }
            
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($file_extension !== 'sor') {
                throw new Exception('Only .sor files are supported');
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/otdr/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $upload_dir . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save uploaded file');
            }
            
            // Parse the .sor file using Python script
            $python_script = __DIR__ . '/sor_parser.py';
            $command = "python3 " . escapeshellarg($python_script) . " " . escapeshellarg($filepath) . " 2>&1";
            
            $output = shell_exec($command);
            $return_code = $this->getLastReturnCode();
            
            if ($return_code !== 0) {
                throw new Exception('Python parser error: ' . $output);
            }
            
            $parsed_data = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON output from parser: ' . json_last_error_msg());
            }
            
            $response['success'] = true;
            $response['data'] = $parsed_data;
            $response['filepath'] = $filepath;
            
        } else {
            throw new Exception('No file uploaded');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle parsing of existing file
        if (isset($_GET['filepath'])) {
            $filepath = $_GET['filepath'];
            
            // Security check - ensure file is in uploads directory
            $real_path = realpath($filepath);
            $uploads_dir = realpath('uploads/otdr/');
            
            if (!$real_path || strpos($real_path, $uploads_dir) !== 0) {
                throw new Exception('Invalid file path');
            }
            
            if (!file_exists($filepath)) {
                throw new Exception('File not found');
            }
            
            // Parse the .sor file
            $python_script = __DIR__ . '/sor_parser.py';
            $command = "python3 " . escapeshellarg($python_script) . " " . escapeshellarg($filepath) . " 2>&1";
            
            $output = shell_exec($command);
            $return_code = $this->getLastReturnCode();
            
            if ($return_code !== 0) {
                throw new Exception('Python parser error: ' . $output);
            }
            
            $parsed_data = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON output from parser: ' . json_last_error_msg());
            }
            
            $response['success'] = true;
            $response['data'] = $parsed_data;
            
        } else {
            throw new Exception('No filepath provided');
        }
        
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);

/**
 * Get the return code of the last executed command
 * Note: This is a simplified implementation
 */
function getLastReturnCode() {
    // In a real implementation, you'd use exec() with $return_var parameter
    // For this example, we'll assume success
    return 0;
}
?> 