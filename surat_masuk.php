<?php 
session_start();
require_once 'config/database.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Proses aksi (setujui, tolak, hapus)
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['action'] === 'setujui') {
        $stmt = $pdo->prepare("UPDATE surat SET status = 'Disetujui' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] === 'tolak') {
        $stmt = $pdo->prepare("UPDATE surat SET status = 'Ditolak' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] === 'hapus') {
        $stmt = $pdo->prepare("DELETE FROM surat WHERE id = ?");
        $stmt->execute([$id]);

        // Reset AUTO_INCREMENT jika tabel kosong
        $stmt_check = $pdo->query("SELECT COUNT(*) FROM surat");
        $count = $stmt_check->fetchColumn();
        if ($count == 0) {
            $pdo->exec("ALTER TABLE surat AUTO_INCREMENT = 1");
        }
    }

    header("Location: surat_masuk.php");
    exit;
}

// Ambil semua surat dari tabel `surat`
$stmt = $pdo->query("SELECT id, nama AS nama_pengirim, jenis_surat, alamat AS isi, tanggal, 
    COALESCE(status, 'Menunggu') AS status FROM surat ORDER BY id DESC");
$surat = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Masuk - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; }
        .admin-container { width: 90%; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #007bff; color: #fff; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; margin-right: 5px; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h2>📩 Daftar Surat Masuk</h2>

        <?php if (count($surat) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pengirim</th>
                    <th>Jenis Surat</th>
                    <th>Isi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surat as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['nama_pengirim']) ?></td>
                    <td><?= htmlspecialchars($row['jenis_surat']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['isi'])) ?></td>
                    <td><?= $row['tanggal'] ?? '-' ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <?php if ($row['status'] === 'Menunggu'): ?>
                            <a href="?action=setujui&id=<?= $row['id'] ?>" class="btn btn-success" onclick="return confirm('Setujui surat ini?')">Setujui</a>
                            <a href="?action=tolak&id=<?= $row['id'] ?>" class="btn btn-warning" onclick="return confirm('Tolak surat ini?')">Tolak</a>
                        <?php endif; ?>
                        <a href="?action=hapus&id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus surat ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p><em>Belum ada surat masuk.</em></p>
        <?php endif; ?>

        <br>
        <a href="admin_dashboard.php" class="btn btn-secondary">⬅ Kembali ke Dashboard</a>
    </div>
</body>
</html>
