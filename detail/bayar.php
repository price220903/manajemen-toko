<?php
session_start();
include '../database.php';

$id_transaksi = $_GET['id'];

// ambil total keseluruhan total_keseluruhan
$total_result = $conn->query("SELECT total_keseluruhan FROM transaksi WHERE id = $id_transaksi");
$total_row = $total_result->fetch_assoc();
$total_keseluruhan = $total_row['total_keseluruhan'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uang = $_POST['uang'];
    $kembalian = $uang - $total_keseluruhan;

    $conn->query("UPDATE transaksi SET status = 'sudah' WHERE id = $id_transaksi");
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Toko DSGallery</title>
    <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body>
    <nav class="menu-container">
        <a href="../index.php" class="menu-logo">
            <img src="../asset/img/dsgallery_logo.jpg" alt="logo" />
        </a>
    </nav>
    <main>
        <div class="container">
            <div class="tambah-content-button">
                <a href="index.php?id=<?php echo $id_transaksi; ?>">Kembali</a>
            </div>
            <h1>Pembayaran Transaksi</h1>
            <form method="post">
                <input type="text" placeholder="Total Keseluruhan: Rp <?php echo number_format($total_keseluruhan, 2, ',', '.'); ?>" readonly>
                <input type="number" name="uang" id="uang" placeholder="Masukkan Jumlah Uang">
                <input type="text" name="kembalian" id="kembalian" placeholder="kembalian" readonly>
                <button type="submit">Bayar</button>
            </form>
        </div>
    </main>
    <script>
        document.getElementById('uang').addEventListener('input', function() {
            var uang = parseFloat(this.value) || 0;
            var total_keseluruhan = <?php echo $total_keseluruhan; ?>;
            var kembalian = uang - total_keseluruhan;
            document.getElementById('kembalian').value = 'Kembalian: Rp ' + kembalian.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        });
    </script>
</body>

</html>