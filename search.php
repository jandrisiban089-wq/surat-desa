<?php
session_start();
require_once 'config/database.php';

// Handle delete request
if (isset($_POST['delete_surat']) && isset($_SESSION['admin_id'])) {
    $surat_id = (int)$_POST['surat_id'];
    $surat_type = $_POST['surat_type'];
    
    try {
        if ($surat_type === 'masuk') {
            // Get file path before deleting record
            $stmt = $pdo->prepare("SELECT file_path FROM surat_masuk WHERE id = ?");
            $stmt->execute([$surat_id]);
            $file_data = $stmt->fetch();
            
            // Delete file if exists
            if ($file_data && !empty($file_data['file_path']) && file_exists($file_data['file_path'])) {
                unlink($file_data['file_path']);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
            $stmt->execute([$surat_id]);
            
        } elseif ($surat_type === 'keluar') {
            // Get file path before deleting record
            $stmt = $pdo->prepare("SELECT file_path FROM surat_keluar WHERE id = ?");
            $stmt->execute([$surat_id]);
            $file_data = $stmt->fetch();
            
            // Delete file if exists
            if ($file_data && !empty($file_data['file_path']) && file_exists($file_data['file_path'])) {
                unlink($file_data['file_path']);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
            $stmt->execute([$surat_id]);
        }
        
        $_SESSION['delete_success'] = "Surat berhasil dihapus!";
        
    } catch (Exception $e) {
        $_SESSION['delete_error'] = "Error: " . $e->getMessage();
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$jenis_surat = $_GET['jenis_surat'] ?? 'semua';

$surat_masuk = [];
$surat_keluar = [];

if ($tanggal_mulai && $tanggal_akhir) {
    if ($jenis_surat === 'semua' || $jenis_surat === 'masuk') {
        // Hanya ambil surat masuk yang memiliki file (dari upload)
        $stmt = $pdo->prepare("SELECT * FROM surat_masuk WHERE tanggal_surat BETWEEN ? AND ? AND file_path IS NOT NULL AND file_path != '' ORDER BY tanggal_surat DESC");
        $stmt->execute([$tanggal_mulai, $tanggal_akhir]);
        $surat_masuk = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    if ($jenis_surat === 'semua' || $jenis_surat === 'keluar') {
        // Hanya ambil surat keluar yang memiliki file (dari upload), bukan yang dibuat dari form
        $stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE tanggal_surat BETWEEN ? AND ? AND file_path IS NOT NULL AND file_path != '' ORDER BY tanggal_surat DESC");
        $stmt->execute([$tanggal_mulai, $tanggal_akhir]);
        $surat_keluar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Surat - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-results {
            margin-top: 30px;
        }
        
        .result-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .result-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .result-info {
            flex: 1;
        }
        
        .result-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start;
        }
        
        .surat-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .surat-masuk {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .surat-keluar {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .result-meta {
            color: #666;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .admin-only {
            display: <?php echo isset($_SESSION['admin_id']) ? 'inline-block' : 'none'; ?>;
        }
        
        /* Delete Modal Styles */
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .delete-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: none;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        
        .delete-modal-content h3 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .delete-modal-content p {
            margin-bottom: 20px;
        }
        
        .delete-modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        /* Navigation buttons styling */
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: 1px solid #6c757d;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: #545b62;
            border-color: #4e555b;
            transform: translateX(-2px);
        }
        
        /* Info box for search description */
        .search-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .search-info h4 {
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .result-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .result-actions {
                width: 100%;
                justify-content: center;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-building"></i>
                <h1>Hasil Pencarian Surat Desa</h1>
            </div>
        </header>

        <main>
            <!-- Navigation buttons -->
            <div class="nav-buttons">
                <button onclick="goBack()" class="btn btn-back btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </button>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-home"></i>
                    Beranda
                </a>
            </div>

            <!-- Search Info Box -->
            <div class="search-info">
                <h4><i class="fas fa-info-circle"></i> Informasi Pencarian</h4>
                <p>Pencarian ini menampilkan surat-surat yang diupload melalui sistem upload. Hanya surat masuk dan surat keluar yang memiliki file yang akan ditampilkan dalam hasil pencarian.</p>
            </div>

            <?php if (isset($_SESSION['delete_success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['delete_error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?>
                </div>
            <?php endif; ?>

            <div class="search-section">
                <h3>Filter Pencarian</h3>
                <form action="search.php" method="GET">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label>Tanggal Mulai:</label>
                            <input type="date" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir:</label>
                            <input type="date" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Surat:</label>
                            <select name="jenis_surat">
                                <option value="semua" <?php echo ($jenis_surat === 'semua') ? 'selected' : ''; ?>>Semua</option>
                                <option value="masuk" <?php echo ($jenis_surat === 'masuk') ? 'selected' : ''; ?>>Surat Masuk</option>
                                <option value="keluar" <?php echo ($jenis_surat === 'keluar') ? 'selected' : ''; ?>>Surat Keluar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-search">
                                <i class="fas fa-search"></i>
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($tanggal_mulai && $tanggal_akhir): ?>
                <div class="search-results">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Hasil Pencarian (<?php echo date('d/m/Y', strtotime($tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($tanggal_akhir)); ?>)</h3>
                        <div style="font-size: 0.9rem; color: #666;">
                            <i class="fas fa-file-upload"></i>
                            Surat yang diupload: <?php echo count($surat_masuk) + count($surat_keluar); ?>
                        </div>
                    </div>
                    
                    <?php if (empty($surat_masuk) && empty($surat_keluar)): ?>
                        <div class="no-results">
                            <i class="fas fa-search"></i>
                            <h4>Tidak ada surat ditemukan</h4>
                            <p>Tidak ada surat yang diupload pada rentang tanggal tersebut</p>
                            <p style="font-size: 0.9rem; color: #999;">Pastikan surat telah diupload melalui menu "Upload Surat"</p>
                        </div>
                    <?php else: ?>
                        
                        <?php foreach ($surat_masuk as $surat): ?>
                            <div class="result-card">
                                <div class="result-header">
                                    <div class="result-info">
                                        <span class="surat-type surat-masuk">
                                            <i class="fas fa-file-import"></i>
                                            Surat Masuk (Upload)
                                        </span>
                                        <h4><?php echo htmlspecialchars($surat['perihal'] ?? 'Surat Masuk'); ?></h4>
                                        <p><strong>Nomor:</strong> <?php echo htmlspecialchars($surat['nomor_surat'] ?? ''); ?></p>
                                        <?php if (!empty($surat['asal_surat'])): ?>
                                            <p><strong>Asal:</strong> <?php echo htmlspecialchars($surat['asal_surat']); ?></p>
                                        <?php endif; ?>
                                        <div class="result-meta">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d M Y', strtotime($surat['tanggal_surat'])); ?>
                                            <i class="fas fa-file"></i>
                                            <?php echo strtoupper($surat['file_type'] ?? 'file'); ?>
                                            <i class="fas fa-upload" title="Surat diupload"></i>
                                            Upload
                                        </div>
                                    </div>
                                    <div class="result-actions">
                                        <?php if (!empty($surat['file_path']) && file_exists($surat['file_path'])): ?>
                                            <button onclick="previewFile('<?php echo $surat['file_path']; ?>', '<?php echo $surat['file_type'] ?? ''; ?>')" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-eye"></i>
                                                Preview
                                            </button>
                                            <a href="download.php?file=<?php echo urlencode($surat['file_path']); ?>&name=<?php echo urlencode($surat['nomor_surat'] ?? 'surat'); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                            <button onclick="printFile('<?php echo $surat['file_path']; ?>')" class="btn btn-search btn-sm">
                                                <i class="fas fa-print"></i>
                                                Print
                                            </button>
                                        <?php else: ?>
                                            <span class="btn btn-secondary btn-sm" style="opacity: 0.5; cursor: not-allowed;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                File tidak tersedia
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- Tombol Hapus - hanya untuk admin -->
                                        <button onclick="confirmDelete(<?php echo $surat['id']; ?>, 'masuk', '<?php echo htmlspecialchars($surat['perihal'] ?? 'Surat Masuk'); ?>')" class="btn btn-danger btn-sm admin-only">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($surat_keluar as $surat): ?>
                            <div class="result-card">
                                <div class="result-header">
                                    <div class="result-info">
                                        <span class="surat-type surat-keluar">
                                            <i class="fas fa-file-export"></i>
                                            Surat Keluar (Upload)
                                        </span>
                                        <h4><?php echo htmlspecialchars($surat['perihal'] ?? 'Surat Keluar'); ?></h4>
                                        <p><strong>Nomor:</strong> <?php echo htmlspecialchars($surat['nomor_surat'] ?? ''); ?></p>
                                        <?php if (!empty($surat['sifat'])): ?>
                                            <p><strong>Sifat:</strong> <?php echo htmlspecialchars($surat['sifat']); ?></p>
                                        <?php endif; ?>
                                        <div class="result-meta">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d M Y', strtotime($surat['tanggal_surat'])); ?>
                                            <i class="fas fa-file"></i>
                                            <?php echo strtoupper(pathinfo($surat['file_path'], PATHINFO_EXTENSION)); ?>
                                            <i class="fas fa-upload" title="Surat diupload"></i>
                                            Upload
                                        </div>
                                    </div>
                                    <div class="result-actions">
                                        <?php if (!empty($surat['file_path']) && file_exists($surat['file_path'])): ?>
                                            <button onclick="previewFile('<?php echo $surat['file_path']; ?>', '<?php echo pathinfo($surat['file_path'], PATHINFO_EXTENSION); ?>')" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-eye"></i>
                                                Preview
                                            </button>
                                            <a href="download.php?file=<?php echo urlencode($surat['file_path']); ?>&name=<?php echo urlencode($surat['nomor_surat'] ?? 'surat_keluar'); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                            <button onclick="printFile('<?php echo $surat['file_path']; ?>')" class="btn btn-search btn-sm">
                                                <i class="fas fa-print"></i>
                                                Print
                                            </button>
                                        <?php else: ?>
                                            <span class="btn btn-secondary btn-sm" style="opacity: 0.5; cursor: not-allowed;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                File tidak tersedia
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- Tombol Hapus - hanya untuk admin -->
                                        <button onclick="confirmDelete(<?php echo $surat['id']; ?>, 'keluar', '<?php echo htmlspecialchars($surat['perihal'] ?? 'Surat Keluar'); ?>')" class="btn btn-danger btn-sm admin-only">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 Desa Muara Tiga Ilir - Sistem Repositori Surat</p>
        </footer>
    </div>

    <!-- Modal Preview -->
    <div id="previewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000;">
        <div style="position: relative; width: 90%; max-width: 800px; margin: 50px auto; background: white; border-radius: 10px; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3>Preview Surat</h3>
                <button onclick="closePreview()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div id="previewContent" style="padding: 20px; max-height: 70vh; overflow: auto;">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus surat ini?</p>
            <p id="deleteItemName" style="font-weight: bold; color: #dc3545;"></p>
            <p style="font-size: 0.9rem; color: #666;">Data yang dihapus tidak dapat dikembalikan!</p>
            
            <div class="delete-modal-buttons">
                <button onclick="closeDeleteModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Batal
                </button>
                <button onclick="deleteSurat()" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </div>
            
            <!-- Hidden form for delete -->
            <form id="deleteForm" method="POST" style="display: none;">
                <input type="hidden" name="delete_surat" value="1">
                <input type="hidden" name="surat_id" id="deleteSuratId">
                <input type="hidden" name="surat_type" id="deleteSuratType">
            </form>
        </div>
    </div>

    <script>
        let deleteData = {};
        
        // Function to go back to previous page
        function goBack() {
            // Check if there's a history to go back to
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // If no history, redirect to home page
                window.location.href = 'index.php';
            }
        }
        
        function previewFile(filePath, fileType) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');
            
            if (fileType === 'pdf') {
                content.innerHTML = `<iframe src="${filePath}" width="100%" height="500px"></iframe>`;
            } else if (['jpg', 'jpeg', 'png'].includes(fileType)) {
                content.innerHTML = `<img src="${filePath}" style="max-width: 100%; height: auto;">`;
            } else {
                content.innerHTML = `<p>Preview tidak tersedia untuk tipe file ini.</p>`;
            }
            
            modal.style.display = 'block';
        }
        
        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }
        
        function printFile(filePath) {
            window.open(filePath, '_blank');
        }
        
        // Delete functions
        function confirmDelete(id, type, name) {
            deleteData = { id: id, type: type, name: name };
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        function deleteSurat() {
            document.getElementById('deleteSuratId').value = deleteData.id;
            document.getElementById('deleteSuratType').value = deleteData.type;
            document.getElementById('deleteForm').submit();
        }
        
        // Close modal when clicking outside
        document.getElementById('previewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });
        
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
        
        // ESC key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePreview();
                closeDeleteModal();
            }
        });
        
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>