<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'backup') {
        // Create database backup
        $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupPath = 'backups/' . $backupFile;
        
        // Create backups directory
        if (!is_dir('backups')) {
            mkdir('backups', 0755, true);
        }
        
        $command = "mysqldump --host=localhost --user=root --password= surat_desa > $backupPath";
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            // Download the backup file
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backupFile . '"');
            header('Content-Length: ' . filesize($backupPath));
            readfile($backupPath);
            unlink($backupPath); // Remove backup file after download
            exit;
        } else {
            $_SESSION['error'] = 'Gagal membuat backup database';
        }
    }
}

header('Location: admin_dashboard.php');
?>