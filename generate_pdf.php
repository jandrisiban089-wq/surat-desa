<?php
require_once 'config/database.php';

// Simple HTML to PDF conversion
function generateSimplePDF($content, $filename) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // Clean HTML content
    $content = strip_tags($content, '<p><br><h1><h2><h3><h4><div><span>');
    $content = html_entity_decode($content);
    
    // Simple PDF structure
    $pdf = "%PDF-1.4\n";
    $pdf .= "1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n";
    $pdf .= "2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n";
    $pdf .= "3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n/Resources <<\n/Font <<\n/F1 5 0 R\n>>\n>>\n>>\nendobj\n";
    
    // Content stream
    $stream = "BT\n/F1 12 Tf\n50 750 Td\n";
    $lines = explode("\n", $content);
    $y = 750;
    
    foreach ($lines as $line) {
        if ($y < 50) break;
        $line = str_replace(['(', ')'], ['\\(', '\\)'], $line);
        $stream .= "(" . substr($line, 0, 80) . ") Tj\n0 -15 Td\n";
        $y -= 15;
    }
    
    $stream .= "ET\n";
    
    $pdf .= "4 0 obj\n<<\n/Length " . strlen($stream) . "\n>>\nstream\n" . $stream . "\nendstream\nendobj\n";
    $pdf .= "5 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Times-Roman\n>>\nendobj\n";
    $pdf .= "xref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000053 00000 n \n0000000100 00000 n \n0000000200 00000 n \n0000000300 00000 n \n";
    $pdf .= "trailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n400\n%%EOF";
    
    echo $pdf;
}

if (isset($_POST['content']) && isset($_POST['nomor'])) {
    // From surat_keluar.php form
    $content = $_POST['content'];
    $nomor = $_POST['nomor'];
    $filename = "Surat_" . preg_replace('/[^a-zA-Z0-9]/', '_', $nomor) . ".pdf";
    generateSimplePDF($content, $filename);
    
} elseif (isset($_GET['id']) && isset($_GET['type'])) {
    // From search results
    $id = (int)$_GET['id'];
    $type = $_GET['type'];
    
    if ($type === 'keluar') {
        $stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();
        
        if ($surat) {
            $content = generateSuratKeluarHTML($surat);
            $filename = "Surat_Keluar_" . preg_replace('/[^a-zA-Z0-9]/', '_', $surat['nomor_surat']) . ".pdf";
            generateSimplePDF($content, $filename);
        }
    }
} else {
    http_response_code(400);
    echo "Parameter tidak lengkap";
}

function generateSuratKeluarHTML($surat) {
    $tanggal = date('d M Y', strtotime($surat['tanggal_surat']));
    
    return "
PEMERINTAH KABUPATEN BENGKULU SELATAN
KECAMATAN KEDURANG  
DESA MUARA TIGA ILIR
Alamat : Jalan Raya Desa Muara Tiga Ilir Kode Pos 38557

" . strtoupper($surat['perihal']) . "

Nomor : " . $surat['nomor_surat'] . "

Dengan hormat, :

" . $surat['isi_surat'] . "

Demikian surat undangan ini kami buat agar bapak/ibu hadir tepat waktu, atas perhatiannya kami ucapkan terima kasih.

                                        Muara Tiga Ilir, " . $tanggal . "
                                        " . ($surat['jabatan_penanda_tangan'] ?: 'Kepala Desa') . "
                                        
                                        
                                        (" . ($surat['penanda_tangan'] ?: 'ANDRI GUSTIAN') . ")
";
}
?>