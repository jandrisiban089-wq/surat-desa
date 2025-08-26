<?php
// File untuk testing koneksi database dan login
echo "<!DOCTYPE html>";
echo "<html><head><title>Test Connection</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".test{background:white;padding:20px;margin:10px 0;border-radius:5px;border-left:5px solid #28a745;}";
echo ".error{border-left-color:#dc3545;}";
echo ".warning{border-left-color:#ffc107;}";
echo "</style></head><body>";

echo "<h1>🔧 Test Sistem Surat Desa</h1>";

// Test 1: PHP Version
echo "<div class='test'>";
echo "<h3>✅ Test 1: PHP Version</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p>✅ PHP version OK</p>";
} else {
    echo "<p>❌ PHP version terlalu lama, minimal 7.4</p>";
}
echo "</div>";

// Test 2: Extensions
echo "<div class='test'>";
echo "<h3>✅ Test 2: PHP Extensions</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'session', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ {$ext}: Tersedia</p>";
    } else {
        echo "<p>❌ {$ext}: Tidak tersedia</p>";
    }
}
echo "</div>";

// Test 3: Database Connection
echo "<div class='test'>";
echo "<h3>🔌 Test 3: Database Connection</h3>";
try {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'surat_desa';
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Database connection: OK</p>";
    
    // Test tables
    $tables = ['admin', 'surat_keluar', 'surat_masuk', 'activity_log'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            $count = $stmt->fetchColumn();
            echo "<p>✅ Table {$table}: {$count} records</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Table {$table}: Error - " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solusi:</strong></p>";
    echo "<ul>";
    echo "<li>Pastikan XAMPP MySQL sudah running</li>";
    echo "<li>Pastikan database 'surat_desa' sudah dibuat</li>";
    echo "<li>Import file database_fixed.sql</li>";
    echo "</ul>";
}
echo "</div>";

// Test 4: Admin Data
if (isset($pdo)) {
    echo "<div class='test'>";
    echo "<h3>👤 Test 4: Admin Data</h3>";
    try {
        $stmt = $pdo->query("SELECT * FROM admin");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($admins)) {
            echo "<p class='error'>❌ Tidak ada data admin</p>";
            echo "<p><strong>Solusi:</strong> Import ulang database_fixed.sql</p>";
        } else {
            foreach ($admins as $admin) {
                echo "<p>✅ User: <strong>{$admin['username']}</strong> - {$admin['nama']} ({$admin['jabatan']})</p>";
                echo "<p style='font-family:monospace;font-size:12px;'>Password hash: {$admin['password']}</p>";
            }
            
            // Test password hash
            $test_passwords = [
                'admin123' => md5('admin123'),
                'password123' => md5('password123')
            ];
            echo "<p><strong>Test Password Hash:</strong></p>";
            foreach ($test_passwords as $plain => $hash) {
                echo "<p style='font-family:monospace;font-size:12px;'>{$plain} → {$hash}</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
}

// Test 5: Folder Permissions
echo "<div class='test'>";
echo "<h3>📁 Test 5: Folder Permissions</h3>";
$folders = ['uploads', 'generated', 'backups'];
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0755, true)) {
            echo "<p>✅ Folder {$folder}: Dibuat</p>";
        } else {
            echo "<p class='error'>❌ Folder {$folder}: Gagal dibuat</p>";
        }
    } else {
        if (is_writable($folder)) {
            echo "<p>✅ Folder {$folder}: Writable</p>";
        } else {
            echo "<p class='warning'>⚠️ Folder {$folder}: Tidak writable</p>";
        }
    }
}
echo "</div>";

// Test 6: Session
echo "<div class='test'>";
echo "<h3>🍪 Test 6: Session</h3>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "<p>✅ Session: Active</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
} else {
    echo "<p class='error'>❌ Session: Error</p>";
}
echo "</div>";

// Test 7: File Includes
echo "<div class='test'>";
echo "<h3>📄 Test 7: File Structure</h3>";
$required_files = [
    'index.php',
    'login.php', 
    'admin_dashboard.php',
    'config/database.php',
    'css/style.css',
    'js/script.js'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ {$file}: Exists</p>";
    } else {
        echo "<p class='error'>❌ {$file}: Missing</p>";
    }
}
echo "</div>";

// Test Login Form
echo "<div class='test'>";
echo "<h3>🔐 Test 8: Quick Login Test</h3>";
echo "<form method='POST' style='background:#f8f9fa;padding:15px;border-radius:5px;'>";
echo "<h4>Test Login:</h4>";
echo "<input type='text' name='test_username' placeholder='Username' value='admin' style='margin:5px;padding:8px;'>";
echo "<input type='password' name='test_password' placeholder='Password' value='admin123' style='margin:5px;padding:8px;'>";
echo "<button type='submit' name='test_login' style='margin:5px;padding:8px 15px;background:#007bff;color:white;border:none;border-radius:3px;'>Test Login</button>";
echo "</form>";

if (isset($_POST['test_login']) && isset($pdo)) {
    $test_user = $_POST['test_username'];
    $test_pass = $_POST['test_password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$test_user]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            $input_hash = md5($test_pass);
            $stored_hash = $admin['password'];
            
            echo "<p><strong>Test Result:</strong></p>";
            echo "<p>Username found: ✅</p>";
            echo "<p>Input password: {$test_pass}</p>";
            echo "<p>Input hash: {$input_hash}</p>";
            echo "<p>Stored hash: {$stored_hash}</p>";
            
            if ($input_hash === $stored_hash) {
                echo "<p style='color:green;'>✅ <strong>LOGIN BERHASIL!</strong></p>";
                echo "<p><a href='login.php' style='background:#28a745;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Go to Login Page</a></p>";
            } else {
                echo "<p style='color:red;'>❌ Password tidak cocok</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Username tidak ditemukan</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

echo "<div class='test'>";
echo "<h3>🚀 Langkah Selanjutnya</h3>";
echo "<ol>";
echo "<li>Jika semua test ✅, hapus file test_connection.php</li>";
echo "<li>Buka <a href='index.php'>index.php</a> untuk melihat halaman utama</li>";
echo "<li>Buka <a href='login.php'>login.php</a> untuk login admin</li>";
echo "<li>Gunakan username: <strong>admin</strong>, password: <strong>admin123</strong></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";