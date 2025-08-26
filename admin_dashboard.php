<?php
session_start();

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Update last login
try {
    $stmt = $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
} catch (PDOException $e) {
    error_log("Update last login error: " . $e->getMessage());
}

// Ambil statistik surat
try {
    // Total Surat Keluar
    $total_keluar = $pdo->query("SELECT COUNT(*) FROM surat_keluar")->fetchColumn();

    // Total Surat Masuk
    $total_masuk = $pdo->query("SELECT COUNT(*) FROM surat")->fetchColumn();

    // Surat Keluar Bulan Ini
    $keluar_bulan_ini = $pdo->query("
        SELECT COUNT(*) FROM surat_keluar
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();

    // Surat Masuk Bulan Ini
    $masuk_bulan_ini = $pdo->query("
        SELECT COUNT(*) FROM surat
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
          AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ")->fetchColumn();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_keluar = $total_masuk = $keluar_bulan_ini = $masuk_bulan_ini = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial,sans-serif; background:#f4f0f8; margin:0; padding:0; }
        .admin-container { width: 95%; margin:20px auto; }
        .admin-header { display:flex; justify-content:space-between; align-items:center; padding:15px; background:#6a0dad; color:#fff; border-radius:8px; }
        .admin-header h2 { margin:0; }
        .btn { padding:6px 12px; border-radius:4px; text-decoration:none; margin-left:5px; color:#fff; }
        .btn-secondary { background:#4b0082; }
        .btn-danger { background:#b30059; }
        .dashboard-content { margin-top:20px; }
        .stats-container { display:flex; flex-wrap:wrap; gap:20px; }
        .stat-card { flex:1 1 220px; background:#9b30ff; color:#fff; padding:20px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size:2em; margin:0; }
        .stat-card p, .stat-card small { margin:5px 0 0 0; }
        .stat-icon { font-size:2.5em; margin-bottom:10px; }
        .menu-container { margin-top:40px; }
        .menu-grid { display:flex; flex-wrap:wrap; gap:20px; }
        .menu-item { flex:1 1 200px; background:#b19cd9; color:#fff; padding:20px; border-radius:8px; text-decoration:none; box-shadow:0 4px 8px rgba(0,0,0,0.1); transition:0.3s; }
        .menu-item:hover { background:#9b30ff; }
        .menu-item h4 { margin-top:10px; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <div>
            <h2>Dashboard Admin</h2>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?></p>
            <small>Login terakhir: <?php echo date('d M Y H:i'); ?></small>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Beranda</a>
            <a href="?action=logout" class="btn btn-danger" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-export"></i></div>
                <h3><?php echo $total_keluar; ?></h3>
                <p>Total Surat Keluar</p>
                <small>Bulan ini: <?php echo $keluar_bulan_ini; ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-import"></i></div>
                <h3><?php echo $total_masuk; ?></h3>
                <p>Total Surat Masuk</p>
                <small>Bulan ini: <?php echo $masuk_bulan_ini; ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <h3><?php echo $total_keluar + $total_masuk; ?></h3>
                <p>Total Semua Surat</p>
                <small>Bulan ini: <?php echo $keluar_bulan_ini + $masuk_bulan_ini; ?></small>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <h3><?php echo date('d'); ?></h3>
                <p><?php echo date('M Y'); ?></p>
                <small><?php echo date('l'); ?></small>
            </div>
        </div>

        <div class="menu-container">
            <h3>Menu Utama</h3>
            <div class="menu-grid">
                <a href="surat_keluar.php" class="menu-item"><i class="fas fa-file-plus"></i><h4>Buat Surat Keluar</h4></a>
                <a href="upload_surat.php" class="menu-item"><i class="fas fa-cloud-upload-alt"></i><h4>Upload Surat</h4></a>
                <a href="search.php" class="menu-item"><i class="fas fa-search"></i><h4>Pencarian Surat</h4></a>
                <a href="laporan.php" class="menu-item"><i class="fas fa-chart-bar"></i><h4>Laporan</h4></a>
                <a href="surat_masuk.php" class="menu-item"><i class="fas fa-inbox"></i><h4>Surat Masuk</h4></a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
