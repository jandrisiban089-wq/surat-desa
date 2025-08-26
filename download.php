<?php
$file = $_GET['file'] ?? '';
$name = $_GET['name'] ?? 'download';

if (!$file || !file_exists($file)) {
    http_response_code(404);
    die('File tidak ditemukan');
}

// Security check - ensure file is in uploads directory
$realPath = realpath($file);
$uploadsPath = realpath('uploads/');

if (!$uploadsPath || strpos($realPath, $uploadsPath) !== 0) {
    http_response_code(403);
    die('Akses ditolak');
}

$fileInfo = pathinfo($file);
$fileExt = strtolower($fileInfo['extension']);
$fileName = $name . '.' . $fileExt;

// Set appropriate headers based on file type
$mimeTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png'
];

$mimeType = $mimeTypes[$fileExt] ?? 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($file);
exit;
?>