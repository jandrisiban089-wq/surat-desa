<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && md5($password) === $admin['password']) {
                // Set session data
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nama'] = $admin['nama'];
                $_SESSION['admin_jabatan'] = $admin['jabatan'];
                $_SESSION['login_time'] = time();
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "Username atau password salah!";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h2>Login Administrator</h2>
            </div>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <div class="login-footer">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
            
</body>
</html>