<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

$success_message = '';
$error_message = '';

if ($_POST) {
    try {
        $jenis_surat = $_POST['jenis_surat'];
        $nomor_surat = $_POST['nomor_surat'];
        $tanggal_surat = $_POST['tanggal_surat'];
        $perihal = $_POST['perihal'] ?? '';
        $asal_surat = $_POST['asal_surat'] ?? '';
        
        // Handle file upload
        if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = $_FILES['file_surat']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($fileExt, $allowedTypes)) {
                throw new Exception('Tipe file tidak diizinkan. Hanya PDF, JPG, dan PNG yang diperbolehkan.');
            }
            
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $fileName);
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $filePath)) {
                if ($jenis_surat === 'masuk') {
                    $stmt = $pdo->prepare("INSERT INTO surat_masuk (nomor_surat, tanggal_surat, perihal, asal_surat, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nomor_surat, $tanggal_surat, $perihal, $asal_surat, $filePath, $fileExt]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO surat_keluar (nomor_surat, tanggal_surat, perihal, file_path) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nomor_surat, $tanggal_surat, $perihal, $filePath]);
                }
                
                $success_message = "Surat berhasil diupload!";
            } else {
                throw new Exception('Gagal upload file.');
            }
        } else {
            throw new Exception('File harus dipilih.');
        }
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Surat - Sistem Surat Desa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .file-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .file-input:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #4CAF50;
            background: #f0f8f0;
        }
        
        .upload-area.dragover {
            border-color: #4CAF50;
            background: #e8f5e8;
        }
        
        .file-preview {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        
        .file-preview img {
            max-width: 300px;
            max-height: 300px;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-info">
                <h2>Upload Surat</h2>
                <p>Upload surat masuk dan surat keluar</p>
            </div>
            <div class="admin-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="dashboard-content">
                <h3>Data Surat</h3>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label>Jenis Surat:</label>
                        <select name="jenis_surat" id="jenisSurat" required>
                            <option value="">Pilih Jenis Surat</option>
                            <option value="masuk">Surat Masuk</option>
                            <option value="keluar">Surat Keluar</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Surat:</label>
                        <input type="text" name="nomor_surat" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Surat:</label>
                        <input type="date" name="tanggal_surat" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Perihal:</label>
                        <input type="text" name="perihal" placeholder="Contoh: Permohonan Izin Kegiatan">
                    </div>
                    
                    <div class="form-group" id="asalSuratGroup" style="display: none;">
                        <label>Asal Surat:</label>
                        <input type="text" name="asal_surat" placeholder="Contoh: Dinas Kesehatan">
                    </div>
                    
                    <div class="form-group">
                        <label>File Surat:</label>
                        <input type="file" name="file_surat" id="fileSurat" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #667eea; margin-bottom: 10px;"></i>
                            <h4>Upload File Surat</h4>
                            <p>Klik untuk memilih file</p>
                            <p style="font-size: 0.9rem; color: #666;">Format: PDF, JPG, PNG (Max: 10MB)</p>
                        </div>
                        <div id="fileInfo" style="display: none; margin-top: 10px; padding: 10px; background: #e8f5e8; border-radius: 5px;">
                            <i class="fas fa-check-circle" style="color: #4CAF50;"></i>
                            <span id="fileName"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i>
                            Upload Surat
                        </button>
                        <button type="button" onclick="previewFile()" class="btn btn-secondary">
                            <i class="fas fa-eye"></i>
                            Preview
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="dashboard-content">
                <h3>Preview File</h3>
                <div class="file-preview" id="filePreview">
                    <i class="fas fa-file" style="font-size: 4rem; color: #ccc;"></i>
                    <p style="color: #666;">Pilih file untuk melihat preview</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const jenisSurat = document.getElementById('jenisSurat');
            const asalSuratGroup = document.getElementById('asalSuratGroup');
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileSurat');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            
            // Show/hide asal surat field
            jenisSurat.addEventListener('change', function() {
                if (this.value === 'masuk') {
                    asalSuratGroup.style.display = 'block';
                    asalSuratGroup.querySelector('input').setAttribute('required', '');
                } else {
                    asalSuratGroup.style.display = 'none';
                    asalSuratGroup.querySelector('input').removeAttribute('required');
                }
            });
            
            // Upload area click
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // File input change
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileName.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                    fileInfo.style.display = 'block';
                    
                    // Update preview
                    showPreview(file);
                } else {
                    fileInfo.style.display = 'none';
                }
            });
        });
        
        function showPreview(file) {
            const preview = document.getElementById('filePreview');
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 5px;">
                        <p style="margin-top: 10px; font-weight: bold;">${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                preview.innerHTML = `
                    <i class="fas fa-file-pdf" style="font-size: 4rem; color: #e74c3c; margin-bottom: 15px;"></i>
                    <h4 style="margin: 10px 0;">${file.name}</h4>
                    <p style="color: #666;">File PDF - ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            } else {
                preview.innerHTML = `
                    <i class="fas fa-file" style="font-size: 4rem; color: #666; margin-bottom: 15px;"></i>
                    <h4 style="margin: 10px 0;">${file.name}</h4>
                    <p style="color: #666;">Ukuran: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            }
        }
        
        function previewFile() {
            const fileInput = document.getElementById('fileSurat');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Pilih file terlebih dahulu!');
                return;
            }
            
            if (file.type === 'application/pdf') {
                const url = URL.createObjectURL(file);
                window.open(url, '_blank');
            } else if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const newWindow = window.open('', '_blank');
                    if (newWindow) {
                        newWindow.document.write(`
                            <html>
                            <head><title>Preview - ${file.name}</title></head>
                            <body style="margin:0; display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f0f0f0;">
                                <img src="${e.target.result}" style="max-width:90%; max-height:90%; box-shadow:0 4px 8px rgba(0,0,0,0.1);">
                            </body>
                            </html>
                        `);
                    }
                };
                reader.readAsDataURL(file);
            } else {
                alert('Preview tidak tersedia untuk tipe file ini.');
            }
        }
    </script>
</body>
</html>