<?php
require __DIR__ . '/config/database.php'; // pakai PDO dari database.php

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = sanitizeInput($_POST['nama']);
    $nik    = sanitizeInput($_POST['nik']);
    $alamat = sanitizeInput($_POST['alamat']);
    $jenis  = sanitizeInput($_POST['jenis_surat']);
    $ket    = isset($_POST['keterangan']) ? sanitizeInput($_POST['keterangan']) : null;

    if ($jenis === 'Lainnya' && !empty($ket)) {
        $jenis = "Lainnya - " . $ket;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO surat (nama,  alamat, jenis_surat) 
                               VALUES (:nama, : :alamat, :jenis)");
        $stmt->execute([
            ':nama'   => $nama,
            ':nik'    => $nik,
            ':alamat' => $alamat,
            ':jenis'  => $jenis
        ]);
        $success = "✅ Surat berhasil diajukan!";
    } catch (PDOException $e) {
        $error = "❌ Gagal menyimpan: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pengajuan Surat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f0f8;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #6a0dad; /* ungu gelap */
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            margin-top: 15px;
            width: 100%;
            background: #6a0dad; /* ungu */
            border: none;
            color: white;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #8b2be2; /* ungu lebih terang saat hover */
        }
        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
        .success { background: #e6ccff; color: #4b0082; } /* ungu muda / teks ungu gelap */
        .error   { background: #f8d7da; color: #721c24; } /* merah tetap agar terlihat */
        .hidden  { display: none; }
    </style>
    <script>
        function toggleKeterangan() {
            const jenis = document.getElementById('jenis_surat').value;
            const ketField = document.getElementById('keterangan-field');
            if (jenis === 'Lainnya') {
                ketField.classList.remove('hidden');
            } else {
                ketField.classList.add('hidden');
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Form Pengajuan Surat</h2>

    <?php if (!empty($success)): ?>
        <div class="alert success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>


        <label>Alamat</label>
        <textarea name="alamat" required></textarea>

        <label>Jenis Surat</label>
        <select name="jenis_surat" id="jenis_surat" onchange="toggleKeterangan()" required>
            <option value="">-- Pilih Jenis Surat --</option>
            <option value="Surat Keterangan Domisili">Surat Keterangan Domisili</option>
            <option value="Surat Keterangan Usaha">Surat Keterangan Usaha</option>
            <option value="Surat Keterangan Tidak Mampu">Surat Keterangan Tidak Mampu</option>
            <option value="Surat Kehilangan">Surat Kehilangan</option>
            <option value="Lainnya">Lainnya</option>
        </select>

        <div id="keterangan-field" class="hidden">
            <label>Keterangan</label>
            <input type="text" name="keterangan" placeholder="Tuliskan jenis surat lainnya">
        </div>

        <button type="submit">Kirim</button>
    </form>
</div>
</body>
</html>
