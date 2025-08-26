<?php
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';

if ($type === 'keluar' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
    $stmt->execute([$id]);
    $surat = $stmt->fetch();
    
    if ($surat) {
        $tanggal = date('d M Y', strtotime($surat['tanggal_surat']));
        
        echo '
        <div style="font-family: Times, serif; line-height: 1.6;">
            <div style="text-align: center; border-bottom: 3px solid #000; padding-bottom: 20px; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 18px;">PEMERINTAH KABUPATEN BENGKULU SELATAN</h2>
                <h3 style="margin: 5px 0; font-size: 24px;">KECAMATAN KEDURANG</h3>
                <h4 style="margin: 5px 0; font-size: 20px;">DESA MUARA TIGA ILIR</h4>
                <p style="margin: 5px 0; font-size: 12px;">Alamat : Jalan Raya Desa Muara Tiga Ilir Kode Pos 38557</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <h3 style="text-decoration: underline; font-weight: bold;">' . strtoupper(htmlspecialchars($surat['perihal'])) . '</h3>
            </div>
            
            <div style="text-align: center; margin-bottom: 30px;">
                Nomor : ' . htmlspecialchars($surat['nomor_surat']) . '
            </div>
            
            <div style="text-align: justify; margin: 20px 0;">
                <p>Yang bertanda tangan di bawah ini :</p>
                <br>
                <p style="margin-left: 40px;">
                    Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' . htmlspecialchars($surat['penanda_tangan'] ?: 'ANDRI GUSTIAN') . '<br>
                    Jabatan&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' . htmlspecialchars($surat['jabatan_penanda_tangan'] ?: 'KEPALA DESA') . '
                </p>
                <br>
                <p>Menerangkan dengan sebenarnya bahwa :</p>
                <br>
                <div>' . nl2br(htmlspecialchars($surat['isi_surat'])) . '</div>
                <br>
                <p>Demikian keterangan ini kami berikan untuk dapat dipergunakan seperlunya.</p>
            </div>
            
            <div style="float: right; text-align: center; margin-top: 50px; width: 200px;">
                <p>Muara Tiga Ilir, ' . $tanggal . '</p>
                <p>' . htmlspecialchars($surat['jabatan_penanda_tangan'] ?: 'Kepala Desa') . '</p>
                <br><br><br>
                <p style="text-decoration: underline;">(' . htmlspecialchars($surat['penanda_tangan'] ?: 'ANDRI GUSTIAN') . ')</p>
            </div>
            <div style="clear: both;"></div>
        </div>';
    } else {
        echo '<p>Data surat tidak ditemukan</p>';
    }
} else {
    echo '<p>Parameter tidak valid</p>';
}
?>