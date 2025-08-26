<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

// Get filter parameters
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$jenis = $_GET['jenis'] ?? 'semua';

// Get statistics data
try {
    // Monthly statistics
    $stmt = $pdo->prepare("
        SELECT 
            DAY(tanggal_surat) as hari,
            COUNT(*) as total
        FROM surat_keluar 
        WHERE MONTH(tanggal_surat) = ? AND YEAR(tanggal_surat) = ?
        GROUP BY DAY(tanggal_surat)
        ORDER BY hari
    ");
    $stmt->execute([$bulan, $tahun]);
    $keluar_harian = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("
        SELECT 
            DAY(tanggal_surat) as hari,
            COUNT(*) as total
        FROM surat_masuk 
        WHERE MONTH(tanggal_surat) = ? AND YEAR(tanggal_surat) = ?
        GROUP BY DAY(tanggal_surat)
        ORDER BY hari
    ");
    $stmt->execute([$bulan, $tahun]);
    $masuk_harian = $stmt->fetchAll();
    
    // Yearly statistics
    $stmt = $pdo->prepare("
        SELECT 
            MONTH(tanggal_surat) as bulan,
            COUNT(*) as total
        FROM surat_keluar 
        WHERE YEAR(tanggal_surat) = ?
        GROUP BY MONTH(tanggal_surat)
        ORDER BY bulan
    ");
    $stmt->execute([$tahun]);
    $keluar_bulanan = $stmt->fetchAll();
    
    // Top categories
    $stmt = $pdo->query("
        SELECT 
            LEFT(perihal, 30) as kategori,
            COUNT(*) as total
        FROM surat_keluar 
        GROUP BY LEFT(perihal, 30)
        ORDER BY total DESC
        LIMIT 5
    ");
    $top_categories = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Report error: " . $e->getMessage());
    $keluar_harian = $masuk_harian = $keluar_bulanan = $top_categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-info">
                <h2>Laporan & Statistik</h2>
                <p>Data surat desa</p>
            </div>
            <div class="admin-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i>
                    Print Laporan
                </button>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- Filter -->
            <div class="search-section no-print">
                <h3>Filter Laporan</h3>
                <form method="GET">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label>Bulan:</label>
                            <select name="bulan">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo ($bulan == sprintf('%02d', $i)) ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun:</label>
                            <select name="tahun">
                                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($tahun == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-filter"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Charts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4>Surat Harian - <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?></h4>
                    <canvas id="chartHarian"></canvas>
                </div>
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4>Top Kategori Surat</h4>
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>

            <!-- Tables -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4>Statistik Surat Keluar</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Hari</th>
                                <th style="padding: 10px; text-align: right; border: 1px solid #ddd;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($keluar_harian as $data): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $data['hari']; ?></td>
                                <td style="padding: 8px; text-align: right; border: 1px solid #ddd;"><?php echo $data['total']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h4>Top Kategori Surat</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Kategori</th>
                                <th style="padding: 10px; text-align: right; border: 1px solid #ddd;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_categories as $data): ?>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($data['kategori']); ?>...</td>
                                <td style="padding: 8px; text-align: right; border: 1px solid #ddd;"><?php echo $data['total']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart data preparation
        const keluarData = <?php echo json_encode($keluar_harian); ?>;
        const masukData = <?php echo json_encode($masuk_harian); ?>;
        const categoryData = <?php echo json_encode($top_categories); ?>;

        // Daily chart
        const ctxHarian = document.getElementById('chartHarian').getContext('2d');
        new Chart(ctxHarian, {
            type: 'line',
            data: {
                labels: keluarData.map(d => d.hari),
                datasets: [{
                    label: 'Surat Keluar',
                    data: keluarData.map(d => d.total),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category chart
        const ctxKategori = document.getElementById('chartKategori').getContext('2d');
        new Chart(ctxKategori, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(d => d.kategori + '...'),
                datasets: [{
                    data: categoryData.map(d => d.total),
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>