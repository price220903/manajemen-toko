    <?php
    session_start();
    include '../database.php';

    $id_transaksi = $_GET['id']; // Pastikan $id_transaksi terdefinisi

    if (isset($_GET['hapus'])) {
        $id_detail = $_GET['hapus'];

        // Ambil total_harga dari detail transaksi yang akan dihapus
        $result = $conn->query("SELECT total_harga FROM detail_transaksi WHERE id = $id_detail");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_harga = $row['total_harga'] ?? 0;

            // Hapus detail transaksi
            $conn->query("DELETE FROM detail_transaksi WHERE id = $id_detail");

            // Hitung total_keseluruhan baru
            $result = $conn->query("SELECT SUM(total_harga) AS total_keseluruhan FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
            if ($result) {
                $row = $result->fetch_assoc();
                $total_keseluruhan = $row['total_keseluruhan'] ?? 0;

                // Meng-update total_keseluruhan di table transaksi
                $conn->query("UPDATE transaksi SET total_keseluruhan = $total_keseluruhan WHERE id = $id_transaksi");
            }
        }
    }

    // menampilkan data table detail_transaksi
    $result = $conn->query("SELECT * FROM detail_transaksi WHERE id_transaksi = $id_transaksi");

    // ambil total keseluruhan
    $total_result = $conn->query("SELECT total_keseluruhan  FROM transaksi WHERE id = $id_transaksi");
    $total_row = $total_result->fetch_assoc();
    $total_keseluruhan = $total_row['total_keseluruhan'];

    //ambil status
    $ambil_status = $conn->query("SELECT status FROM transaksi WHERE id = $id_transaksi");
    $status_row = $ambil_status->fetch_assoc();
    $status = $status_row['status'];

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
            <div class="detail-content-button">
                <a href="../index.php">Kembali</a>
                <?php if ($status == 'belum'): ?>
                    <a href="tambah_detail.php?id=<?php echo $id_transaksi; ?>">Tambah Detail</a>
                <?php endif; ?>

            </div>
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>QTY</th>
                    <th>Total Harga</th>
                    <th>Action</th>
                </tr>
                <?php $no = 1;
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++ ?></td>
                        <td><?php echo $row['nama_produk'] ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 2, ',', '.'); ?></td>
                        <td><?php echo $row['qty'] ?></td>
                        <td>Rp <?php echo number_format($row['total_harga'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if ($status == 'belum'): ?>
                                <a href="index.php?id=<?php echo $id_transaksi; ?>&hapus=<?php echo $row['id']; ?>">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile ?>
                <tr class="total">
                    <td colspan="4">Total Transaksi</td>
                    <td>Rp <?php echo number_format($total_keseluruhan, 2, ',', '.'); ?></td>
                    <td>
                        <?php if ($total_keseluruhan > 0 && $status == 'belum'): ?>
                            <a href="bayar.php?id=<?php echo $id_transaksi; ?>">Bayar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </main>
    </body>

    </html>