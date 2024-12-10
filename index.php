<?php
session_start();
include 'database.php';

$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query

if (isset($_POST['tambah_transaksi'])) {
    date_default_timezone_set('Asia/Jakarta');

    $tanggal_transaksi = date('Y-m-d H:i:s');

    $conn->query("INSERT INTO transaksi (tanggal_transaksi, status) VALUES ('$tanggal_transaksi', 'belum')");
}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];

    $conn->query("DELETE FROM detail_transaksi WHERE id_transaksi = $id");
    $conn->query("DELETE FROM transaksi WHERE id = $id");
}

// Get today's date
$tanggal_hari_ini = date('Y-m-d');

if (isset($_POST['filter'])) {
    $tanggal = $_POST['tanggal'];
} else {
    $tanggal = $tanggal_hari_ini;
}

$result = $conn->query("SELECT * FROM transaksi WHERE DATE(tanggal_transaksi) = '$tanggal' LIMIT $limit OFFSET $offset");
$total_result = $conn->query("SELECT COUNT(*) AS total FROM transaksi WHERE DATE(tanggal_transaksi) = '$tanggal'");

$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Toko DSGallery</title>
    <link rel="stylesheet" href="asset/css/style.css">
</head>

<body>
    <nav class="menu-container">
        <a href="index.php" class="menu-logo">
            <img src="asset/img/dsgallery_logo.jpg" alt="logo" />
        </a>
    </nav>
    <main>
        <div class="content-button">
            <form method="post" class="button-tambah">
                <button type="submit" name="tambah_transaksi">Tambah Transaksi</button>
            </form>
            <form action="index.php" method="post" class="button-tanggal">
                <input type="date" name="tanggal" id="tanggal">
                <button type="submit" name="filter">Sortir</button>
            </form>
            <form action="generate_pdf.php" method="get" class="button-cetak">
                <button type="submit">Cetak</button>
            </form>
        </div>
        <table>
            <tr>
                <th>No</th>
                <th>Tanggal Transaksi</th>
                <th>Total Transaksi</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php $no = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['tanggal_transaksi']; ?></td>
                    <td>Rp <?php echo number_format($row['total_keseluruhan'], 2, ',', '.'); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete">Hapus</button>
                        </form>
                        <a href="detail/index.php?id=<?php echo $row['id']; ?>">Detail</a>
                    </td>
                </tr>
            <?php endwhile ?>
        </table>
    </main>
</body>

</html>
