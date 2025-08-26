<?php
require_once 'config/database.php';

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    http_response_code(400);
    die('Parameter tidak lengkap');
}

$id = (int)$_GET['id'];
$type = $_GET['type'];

if ($type === 'keluar') {
    $stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
    $stmt->execute([$id]);
    $surat = $stmt->fetch();
    
    if (!$surat) {
        die('Surat tidak ditemukan');
    }
    
    $tanggal = date('d F Y', strtotime($surat['tanggal_surat']));
    $perihal = $surat['perihal'];
    $nomor = $surat['nomor_surat'];
} else {
    die('Tipe surat tidak didukung');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Surat - <?php echo htmlspecialchars($perihal); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .surat-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
        }
        
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .kop-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo-fallback {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2E8B57, #3CB371);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            border: 3px solid #228B22;
        }
        
        .logo-fallback i {
            font-size: 50px;
            color: #FFD700;
        }
        
        .kop-text {
            text-align: center;
            flex: 1;
        }
        
        .kop-surat h2 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
            color: #000;
        }
        
        .kop-surat h3 {
            font-size: 24px;
            margin: 5px 0;
            font-weight: bold;
            color: #000;
        }
        
        .kop-surat h4 {
            font-size: 20px;
            margin: 5px 0;
            font-weight: bold;
            color: #000;
        }
        
        .kop-surat p {
            font-size: 12px;
            margin: 5px 0;
            color: #000;
        }
        
        .surat-header {
            text-align: center;
            margin: 30px 0;
        }
        
        .surat-header h3 {
            text-decoration: underline;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
            color: #000;
        }
        
        .surat-nomor {
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
            color: #000;
        }
        
        .surat-content {
            text-align: justify;
            margin: 20px 0;
            font-size: 12px;
            color: #000;
        }
        
        .ttd-section {
            float: right;
            text-align: center;
            margin-top: 50px;
            width: 250px;
            font-size: 12px;
            color: #000;
        }
        
        .ttd-section p {
            margin: 5px 0;
        }
        
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .surat-container {
                padding: 20px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            <i class="fas fa-print"></i> Print Surat
        </button>
        <button onclick="window.close()" class="btn" style="background: #666; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>
    
    <div class="surat-container">
        <div class="kop-surat">
            <div class="kop-header">
                <?php 
                $logo_path = 'images/logo-desa';
                if (file_exists($logo_path)): 
                ?>
                    <div class="logo-container" style="width: 100px; height: 100px; margin-right: 20px; flex-shrink: 0;">
                        <img src="<?php echo $logo_path; ?>" alt="Logo Desa" style="width: 100%; height: 100%; object-fit: contain; border-radius: 10px;">
                    </div>
                <?php else: ?>
                    <div class="logo-fallback">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                <?php endif; ?>
                <div class="kop-text">
                    <h2>PEMERINTAH KABUPATEN BENGKULU SELATAN</h2>
                    <h3>KECAMATAN KEDURANG</h3>
                    <h4>DESA MUARA TIGA ILIR</h4>
                    <p>Alamat : Jalan Raya Desa Muara Tiga Ilir Kode Pos 38557</p>
                    <p>Email: desamuaratigailir@gmail.com | Telp: (0739) 123456</p>
                </div>
            </div>
        </div>
        
        <div class="surat-header">
            <h3><?php echo strtoupper(htmlspecialchars($surat['perihal'])); ?></h3>
        </div>
        
        <div class="surat-nomor">
            Nomor : <?php echo htmlspecialchars($surat['nomor_surat']); ?>
        </div>
        
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>Desa Muara Tiga Ilir, Kec. Kedurang</td>
                </tr>
            </table>
            <br>
            <p>Dengan hormat,:</p>
            <br>
            <div style="text-align: justify; margin-left: 20px; margin-right: 20px;">
                <?php echo nl2br(htmlspecialchars($surat['isi_surat'])); ?>
            </div>
            <br>
            <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>
        
        <div class="ttd-section">
            <p>Muara Tiga Ilir, <?php echo $tanggal; ?></p>
            <p><?php echo htmlspecialchars($surat['jabatan_penanda_tangan'] ?: 'Kepala Desa'); ?></p>
            <br><br><br>
            <p style="text-decoration: underline; font-weight: bold;">(<?php echo strtoupper(htmlspecialchars($surat['penanda_tangan'] ?: 'ANDRI GUSTIAN')); ?>)</p>
        </div>
        
        <div style="clear: both;"></div>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>