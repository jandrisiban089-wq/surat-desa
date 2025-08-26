<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

$success_message = '';
$error_message = '';
$saved_surat_id = null; // Variable untuk menyimpan ID surat yang baru dibuat

if ($_POST) {
    try {
        $nomor_surat = $_POST['nomor_surat'];
        $tanggal_surat = $_POST['tanggal_surat'];
        $sifat = $_POST['sifat'];
        $perihal = $_POST['perihal'];
        $isi_surat = $_POST['isi_surat'];
        $penanda_tangan = $_POST['penanda_tangan'];
        $jabatan_penanda_tangan = $_POST['jabatan_penanda_tangan'];
        
        // Cek apakah kolom 'sifat' ada di tabel
        $stmt_check = $pdo->prepare("SHOW COLUMNS FROM surat_keluar LIKE 'sifat'");
        $stmt_check->execute();
        $column_exists = $stmt_check->fetch();
        
        if ($column_exists) {
            // Jika kolom sifat ada, gunakan query lengkap
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (nomor_surat, tanggal_surat, sifat, perihal, isi_surat, penanda_tangan, jabatan_penanda_tangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nomor_surat, $tanggal_surat, $sifat, $perihal, $isi_surat, $penanda_tangan, $jabatan_penanda_tangan]);
        } else {
            // Jika kolom sifat tidak ada, gunakan query tanpa sifat
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (nomor_surat, tanggal_surat, perihal, isi_surat, penanda_tangan, jabatan_penanda_tangan) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nomor_surat, $tanggal_surat, $perihal, $isi_surat, $penanda_tangan, $jabatan_penanda_tangan]);
        }
        
        $surat_id = $pdo->lastInsertId();
        $saved_surat_id = $surat_id; // Simpan ID untuk keperluan print
        $success_message = "Surat berhasil disimpan dengan ID: " . $surat_id;
        
        // Optional: Tambah kolom sifat jika tidak ada
        if (!$column_exists) {
            try {
                $pdo->exec("ALTER TABLE surat_keluar ADD COLUMN sifat VARCHAR(50) DEFAULT 'Biasa' AFTER tanggal_surat");
                // Update record yang baru saja diinsert
                $stmt_update = $pdo->prepare("UPDATE surat_keluar SET sifat = ? WHERE id = ?");
                $stmt_update->execute([$sifat, $surat_id]);
            } catch (Exception $e) {
                // Jika gagal menambah kolom, abaikan error ini
                error_log("Failed to add sifat column: " . $e->getMessage());
            }
        }
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Ambil data surat jika ada ID yang tersimpan (untuk keperluan print setelah save)
$current_surat = null;
if ($saved_surat_id) {
    $stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
    $stmt->execute([$saved_surat_id]);
    $current_surat = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Surat Keluar - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .surat-preview {
            background: white;
            padding: 40px;
            margin: 20px 0;
            border: 1px solid #ddd;
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .kop-surat img {
            width: 80px;
            height: 80px;
            float: left;
            margin-right: 20px;
        }
        
        .kop-surat h2 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }
        
        .kop-surat h3 {
            font-size: 24px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .kop-surat h4 {
            font-size: 20px;
            margin: 5px 0;
            font-weight: bold;
        }
        
        .kop-surat p {
            font-size: 12px;
            margin: 5px 0;
        }
        
        .surat-header {
            text-align: center;
            margin: 30px 0;
        }
        
        .surat-header h3 {
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .surat-nomor {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .surat-content {
            text-align: justify;
            margin: 20px 0;
        }
        
        .ttd-section {
            float: right;
            text-align: center;
            margin-top: 50px;
            width: 200px;
        }
        
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .form-container {
                grid-template-columns: 1fr;
            }
        }
        
        /* Print styles - hanya print preview surat */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .surat-preview,
            .surat-preview * {
                visibility: visible;
            }
            
            .surat-preview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 20px !important;
                background: white !important;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        /* Style untuk modal print */
        .print-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .print-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: none;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .print-modal .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .print-modal .close:hover {
            color: black;
        }
        
        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-buttons button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
        }

        /* Style untuk button sukses */
        .success-actions {
            background: #e8f5e8;
            border: 1px solid #4CAF50;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }

        .success-actions .btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header no-print">
            <div class="admin-info">
                <h2>Form Surat Keluar</h2>
                <p>Buat surat keluar baru</p>
            </div>
            <div class="admin-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success no-print">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
            
            <?php if ($saved_surat_id): ?>
            <div class="success-actions no-print">
                <h4>Surat berhasil disimpan! Pilih aksi berikutnya:</h4>
                <button onclick="printSavedSurat()" class="btn btn-primary">
                    <i class="fas fa-print"></i>
                    Print Surat
                </button>
                <a href="print_surat.php?id=<?php echo $saved_surat_id; ?>&type=keluar" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-external-link-alt"></i>
                    Buka di Tab Baru
                </a>
                <button onclick="resetForm()" class="btn btn-info">
                    <i class="fas fa-plus"></i>
                    Buat Surat Baru
                </button>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error no-print">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container no-print">
            <div class="dashboard-content">
                <h3>Data Surat</h3>
                <form method="POST" id="suratForm">
                    <div class="form-group">
                        <label>Nomor Surat:</label>
                        <input type="text" name="nomor_surat" required value="<?php echo isset($_POST['nomor_surat']) ? $_POST['nomor_surat'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Surat:</label>
                        <input type="date" name="tanggal_surat" required value="<?php echo isset($_POST['tanggal_surat']) ? $_POST['tanggal_surat'] : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Sifat:</label>
                        <select name="sifat">
                            <option value="Biasa" <?php echo (isset($_POST['sifat']) && $_POST['sifat'] == 'Biasa') ? 'selected' : ''; ?>>Biasa</option>
                            <option value="Penting" <?php echo (isset($_POST['sifat']) && $_POST['sifat'] == 'Penting') ? 'selected' : ''; ?>>Penting</option>
                            <option value="Sangat Penting" <?php echo (isset($_POST['sifat']) && $_POST['sifat'] == 'Sangat Penting') ? 'selected' : ''; ?>>Sangat Penting</option>
                            <option value="Rahasia" <?php echo (isset($_POST['sifat']) && $_POST['sifat'] == 'Rahasia') ? 'selected' : ''; ?>>Rahasia</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Perihal:</label>
                        <input type="text" name="perihal" required placeholder="Contoh: SURAT KETERANGAN PERBAIKAN NAMA" value="<?php echo isset($_POST['perihal']) ? $_POST['perihal'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Isi Surat:</label>
                        <textarea name="isi_surat" rows="10" required placeholder="Tulis isi surat disini..."><?php echo isset($_POST['isi_surat']) ? $_POST['isi_surat'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Penanda Tangan:</label>
                        <input type="text" name="penanda_tangan" required value="<?php echo isset($_POST['penanda_tangan']) ? $_POST['penanda_tangan'] : $_SESSION['admin_nama']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Jabatan Penanda Tangan:</label>
                        <input type="text" name="jabatan_penanda_tangan" required value="<?php echo isset($_POST['jabatan_penanda_tangan']) ? $_POST['jabatan_penanda_tangan'] : $_SESSION['admin_jabatan']; ?>">
                    </div>
                    
                    <div class="form-group">
    
                        <button type="button" onclick="openPrintModal()" class="btn btn-secondary">
                            <i class="fas fa-print"></i>
                            Preview & Print
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="dashboard-content">
                <h3>Preview Surat</h3>
                <div class="surat-preview" id="suratPreview">
                    <div class="kop-surat">
    <div style="display: flex; align-items: center; justify-content: center;">
        <!-- LOGO DESA -->
        <div style="margin-right: 20px;">
            <img src="images/logo-desa.png" alt="Logo Desa" style="width: 80px; height: 80px; object-fit: contain;">
        </div>
        <!-- TEKS HEADER -->
        <div style="text-align: center;">
            <h2>PEMERINTAH KABUPATEN BENGKULU SELATAN</h2>
            <h3>KECAMATAN KEDURANG</h3>
            <h4>DESA MUARA TIGA ILIR</h4>
            <p>Alamat : Jalan Raya Desa Muara Tiga Ilir Kode Pos 38557</p>
        </div>
    </div>
</div>

                    
                    <div class="surat-header">
                        <h3 id="previewPerihal">SURAT KETERANGAN PERBAIKAN NAMA</h3>
                    </div>
                    
                    <div class="surat-nomor">
                        Nomor : <span id="previewNomor">140/18/KD/MRTI/III/2025</span>
                    </div>
                    
                    <div class="surat-content">
                        <div id="previewIsiSurat">
                            <p>Dengan hormat,</p>
                            <br>                        
                            <div id="previewIsi" style="text-align: justify;">
                                Isi surat akan muncul disini...
                            </div>
                            <br>
                            <p>Demikian surat ini kami buat agar dapat dipergunakan sebagaimana mestinya.</p>
                        </div>
                    </div>
                    
                    <div class="ttd-section">
                        <p>Muara Tiga Ilir, <span id="previewTanggal"><?php echo date('d M Y'); ?></span></p>
                        <p><span id="previewJabatanTtd">Kepala Desa</span></p>
                        <br><br><br>
                        <p style="text-decoration: underline;">(<span id="previewNamaTtd">ANDRI GUSTIAN</span>)</p>
                    </div>
                    
                    <div style="clear: both;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div id="printModal" class="print-modal">
        <div class="print-modal-content">
            <span class="close" onclick="closePrintModal()">&times;</span>
            <div class="print-buttons no-print">
                <button onclick="printDocument()" class="btn btn-primary">
                    <i class="fas fa-print"></i>
                    Print
                </button>
                <button onclick="closePrintModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Batal
                </button>
            </div>
            <div id="modalSuratContent"></div>
        </div>
    </div>

    <script>
        // Data surat yang baru disimpan untuk keperluan print
        const savedSuratId = <?php echo $saved_surat_id ? $saved_surat_id : 'null'; ?>;
        
        // Auto update preview
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('suratForm');
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', updatePreview);
            });
            
            // Generate nomor surat otomatis jika belum ada
            if (!document.querySelector('[name="nomor_surat"]').value) {
                generateNomorSurat();
            }
            updatePreview();
        });
        
        function updatePreview() {
            const nomor = document.querySelector('[name="nomor_surat"]').value;
            const tanggal = document.querySelector('[name="tanggal_surat"]').value;
            const perihal = document.querySelector('[name="perihal"]').value;
            const isiSurat = document.querySelector('[name="isi_surat"]').value;
            const penandaTangan = document.querySelector('[name="penanda_tangan"]').value;
            const jabatanPenandaTangan = document.querySelector('[name="jabatan_penanda_tangan"]').value;
            
            // Update preview elements
            if (document.getElementById('previewNomor')) {
                document.getElementById('previewNomor').textContent = nomor || 'Nomor Surat';
            }
            if (document.getElementById('previewPerihal')) {
                document.getElementById('previewPerihal').textContent = perihal || 'PERIHAL SURAT';
            }
            if (document.getElementById('previewJabatanTtd')) {
                document.getElementById('previewJabatanTtd').textContent = jabatanPenandaTangan || 'Jabatan';
            }
            if (document.getElementById('previewNamaTtd')) {
                document.getElementById('previewNamaTtd').textContent = penandaTangan || 'Nama Penanda Tangan';
            }
            if (document.getElementById('previewIsi')) {
                document.getElementById('previewIsi').innerHTML = isiSurat ? isiSurat.replace(/\n/g, '<br>') : 'Isi surat akan muncul disini...';
            }
            
            if (tanggal && document.getElementById('previewTanggal')) {
                const date = new Date(tanggal);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                const formattedDate = date.toLocaleDateString('id-ID', options);
                document.getElementById('previewTanggal').textContent = formattedDate;
            }
        }
        
        function generateNomorSurat() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const random = Math.floor(Math.random() * 900) + 100;
            
            const nomor = `${random}/${month}/KD/MRTI/III/${year}`;
            document.querySelector('[name="nomor_surat"]').value = nomor;
        }
        
        // Fungsi untuk print surat yang baru disimpan
        function printSavedSurat() {
            if (savedSuratId) {
                // Buka halaman print di tab baru
                window.open('print_surat.php?id=' + savedSuratId + '&type=keluar', '_blank');
            } else {
                alert('Tidak ada surat yang baru disimpan untuk dicetak.');
            }
        }
        
        // Fungsi untuk reset form untuk membuat surat baru
        function resetForm() {
            if (confirm('Apakah Anda yakin ingin membuat surat baru? Data form akan dikosongkan.')) {
                document.getElementById('suratForm').reset();
                generateNomorSurat();
                updatePreview();
                // Scroll ke atas
                window.scrollTo(0, 0);
            }
        }
        
        // Fungsi untuk membuka modal print (untuk preview)
        function openPrintModal() {
            // Validasi apakah form sudah diisi
            const nomor = document.querySelector('[name="nomor_surat"]').value;
            const perihal = document.querySelector('[name="perihal"]').value;
            const isiSurat = document.querySelector('[name="isi_surat"]').value;
            
            if (!nomor || !perihal || !isiSurat) {
                alert('Mohon lengkapi data surat terlebih dahulu!');
                return;
            }
            
            // Update preview terlebih dahulu
            updatePreview();
            
            // Copy content ke modal
            const suratContent = document.getElementById('suratPreview').cloneNode(true);
            suratContent.id = 'modalSuratPreview';
            
            // Hapus content lama dan masukkan yang baru
            const modalContent = document.getElementById('modalSuratContent');
            modalContent.innerHTML = '';
            modalContent.appendChild(suratContent);
            
            // Tampilkan modal
            document.getElementById('printModal').style.display = 'block';
        }
        
        // Fungsi untuk menutup modal
        function closePrintModal() {
            document.getElementById('printModal').style.display = 'none';
        }
        
        // Fungsi print yang diperbaiki
        function printDocument() {
            // Update content modal sekali lagi untuk memastikan
            updatePreview();
            
            // Tunggu sebentar untuk memastikan update selesai
            setTimeout(function() {
                // Print menggunakan window.print()
                window.print();
            }, 100);
        }
        
        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('printModal');
            if (event.target === modal) {
                closePrintModal();
            }
        }
        
        // Fungsi print langsung (alternatif tanpa modal)
        function printSurat() {
            // Validasi apakah form sudah diisi
            const nomor = document.querySelector('[name="nomor_surat"]').value;
            const perihal = document.querySelector('[name="perihal"]').value;
            const isiSurat = document.querySelector('[name="isi_surat"]').value;
            
            if (!nomor || !perihal || !isiSurat) {
                alert('Mohon lengkapi data surat terlebih dahulu!');
                return;
            }
            
            // Update preview terlebih dahulu untuk memastikan data terbaru
            updatePreview();
            
            // Tunggu sebentar untuk memastikan update selesil, lalu print
            setTimeout(function() {
                window.print();
            }, 100);
        }
    </script>
</body>
</html>