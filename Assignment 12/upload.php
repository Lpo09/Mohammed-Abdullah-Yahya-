<?php
declare(strict_types=1);

// ===== 1. Global settings =====
const MAX_SIZE        = 2 * 1024 * 1024; // 2MB
const UPLOAD_DIR      = __DIR__ . '/uploads/';
const ALLOWED_EXT     = ['pdf', 'png', 'jpg', 'jpeg'];
const ALLOWED_MIMES   = [
    'application/pdf',
    'image/png',
    'image/jpeg',
];

// Disable error display; show only safe messages to users
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

// ===== 2. Enforce POST method =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// ===== 3. Ensure a file was submitted =====
if (!isset($_FILES['file_uploaded']) ||
    $_FILES['file_uploaded']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    exit('No file was submitted.');
}

$file = $_FILES['file_uploaded'];

// ===== 4. Check upload errors =====
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Upload failed, error code: ' . $file['error']);
}

// Ensure the file was actually uploaded via HTTP (not a local file)
if (!is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    exit('Untrusted source.');
}

// ===== 5. Size check (Layer 1) =====
if ($file['size'] <= 0 || $file['size'] > MAX_SIZE) {
    http_response_code(413);
    exit('File size not allowed (limit 2MB).');
}

// ===== 6. Extension check via strict whitelist (Layer 2) =====
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, ALLOWED_EXT, true)) {
    http_response_code(415);
    exit('File extension not allowed.');
}

// Block dangerous names (double extension such as file.php.jpg)
if (preg_match('/\.(php\d?|phtml|phar|cgi|pl|py|sh|asp|jsp|htaccess)$/i', $originalName)) {
    http_response_code(415);
    exit('Filename contains a dangerous pattern.');
}

// ===== 7. Real MIME inspection via Fileinfo (Layer 3) =====
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$realMime = $finfo->file($file['tmp_name']);

if (!in_array($realMime, ALLOWED_MIMES, true)) {
    http_response_code(415);
    exit('Real file type not allowed: ' . htmlspecialchars($realMime));
}

// Verify extension ↔ MIME consistency (blocks shell.php renamed to .jpg)
$extMimeMap = [
    'pdf'  => 'application/pdf',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
];
if ($extMimeMap[$ext] !== $realMime) {
    http_response_code(415);
    exit('Mismatch between extension and actual content type.');
}

// ===== 8. Extra image validation: getimagesize =====
if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
    if (@getimagesize($file['tmp_name']) === false) {
        http_response_code(415);
        exit('File is not a valid image.');
    }
}

// ===== 9. PDF magic-signature check =====
if ($ext === 'pdf') {
    $fh  = fopen($file['tmp_name'], 'rb');
    $sig = fread($fh, 5);
    fclose($fh);
    if ($sig !== '%PDF-') {
        http_response_code(415);
        exit('Invalid PDF file.');
    }
}

// ===== 10. Generate cryptographically secure random name (Layer 4) =====
$safeName   = bin2hex(random_bytes(16)) . '.' . $ext;

// Path hardening (prevent path traversal)
$targetPath    = UPLOAD_DIR . basename($safeName);
$realTargetDir = realpath(UPLOAD_DIR);
if ($realTargetDir === false) {
    http_response_code(500);
    exit('Storage directory not found.');
}
if (strpos($targetPath, $realTargetDir . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(400);
    exit('Unsafe path.');
}

// ===== 11. Move the uploaded file =====
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    exit('Failed to move file.');
}

// Harden permissions (read-only for owner)
@chmod($targetPath, 0644);

// ===== 12. Success =====
echo "✅ File uploaded successfully.\n";
echo "Stored name: " . htmlspecialchars($safeName) . "\n";
echo "Type: "        . htmlspecialchars($realMime) . "\n";
echo "Size: "        . (int)($file['size'] / 1024) . " KB\n";