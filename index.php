<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem RepositoriSurat Desa</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: #f4f6f9;
      color: #333;
    }

    /* foto belakang bigrund*/
    header {
      height: 80vh;
      background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
      url('images/WhatsApp Image 2025-08-17 at 21.49.14_d5db91bd.jpg') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
      padding: 40px;
    }

    header h1 {
      font-size: 36px;
      margin-bottom: 15px;
      animation: fadeInDown 1s ease-in-out;
    }

    header p {
      font-size: 18px;
      max-width: 600px;
      animation: fadeInUp 1.2s ease-in-out;
    }

    /* Animasi */
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    main {
      max-width: 1100px;
      margin: -60px auto 40px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    .card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0px 6px 18px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h2 {
      margin-bottom: 15px;
      font-size: 22px;
      color: #007bff;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 14px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      color: white;
      transition: all 0.3s;
    }

    .btn-primary {
      background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #0056b3, #003d80);
    }

    .btn-success {
      background: linear-gradient(135deg, #28a745, #1e7e34);
    }

    .btn-success:hover {
      background: linear-gradient(135deg, #1e7e34, #155d27);
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 5px;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .btn-search {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #ff9800, #e68900);
      border: none;
      border-radius: 8px;
      cursor: pointer;
      color: white;
      font-weight: bold;
      transition: 0.3s;
    }

    .btn-search:hover {
      background: linear-gradient(135deg, #e68900, #cc7700);
    }

    footer {
      text-align: center;
      background: #222;
      color: white;
      padding: 15px;
      margin-top: 30px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      main {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Sistem Repositori Surat Desa Muara Tiga Ilir</h1>
    <p>Akses terbuka untuk pencarian dan pengelolaan surat menyurat desa</p>
  </header>

  <main>
    <!-- Card 1: Aksi -->
    <div class="card">
      <h2>Aksi Cepat</h2>
      <div class="action-buttons">
        <a href="login.php" class="btn btn-primary">
          <i class="fas fa-user-shield"></i> Login Admin
        </a>
        <a href="form_surat.php" class="btn btn-success">
          <i class="fas fa-file-signature"></i> Isi Form Surat
        </a>
      </div>
    </div>

    <!-- Card 2: Pencarian -->
    <div class="card">
      <h2>Pencarian Surat</h2>
      <form action="search.php" method="GET">
        <div class="form-group">
          <label>Tanggal Mulai:</label>
          <input type="date" name="tanggal_mulai" required>
        </div>
        <div class="form-group">
          <label>Tanggal Akhir:</label>
          <input type="date" name="tanggal_akhir" required>
        </div>
        <div class="form-group">
          <label>Jenis Surat:</label>
          <select name="jenis_surat">
            <option value="semua">Semua</option>
            <option value="masuk">Surat Masuk</option>
            <option value="keluar">Surat Keluar</option>
          </select>
        </div>
        <button type="submit" class="btn-search">
          <i class="fas fa-search"></i> Cari Surat
        </button>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 Desa Muara Tiga Ilir - Sistem Repositori Surat</p>
  </footer>
</body>
</html>
