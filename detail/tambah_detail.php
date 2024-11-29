<?php
session_start();
include '../database.php';

$id_transaksi = $_GET['id'];

if (isset($_POST['tambah'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $qty = $_POST['qty'];
    $total_harga = $harga * $qty;

    //menambahkan data ke table detail_transaksi
    $conn->query("INSERT INTO detail_transaksi (id_transaksi, nama_produk, harga, qty, total_harga) VALUES ($id_transaksi, '$nama_produk', '$harga', '$qty', '$total_harga')");

    //hitung total_keseluruhan baru
    $result = $conn->query("SELECT SUM(total_harga) AS total_keseluruhan FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
    $row = $result->fetch_assoc();
    $total_keseluruhan = $row['total_keseluruhan'];

    //meng-update total_keseluruhan di table transaksi
    $conn->query("UPDATE transaksi SET total_keseluruhan = $total_keseluruhan WHERE id = $id_transaksi");

    header("Location: index.php?id=$id_transaksi");
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
            <h1>Tambah Detail Transaksi</h1>
            <form method="post">
                <input type="hidden" name="id_transaksi" value="<?php echo $_GET['id']; ?>">
                <input type="text" name="nama_produk" id="nama_produk" placeholder="Masukkan Nama Barang">
                <input type="number" name="harga" id="harga" placeholder="Masukkan Harga Barang">
                <input type="number" name="qty" id="qty" placeholder="Masukkan Jumlah Barang">
                <button type="submit" name="tambah">Submit</button>
            </form>
        </div>
    </main>
</body>

</html>