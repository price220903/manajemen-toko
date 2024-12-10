    <?php
    session_start();
    include '../database.php';

    $id_transaksi = $_GET['id']; // Pastikan $id_transaksi terdefinisi dengan benar

    // Proses Hapus Detail Transaksi
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

    // Proses Tambah Detail Transaksi
    if (isset($_POST['tambah'])) {
        $id_transaksi = $_POST['id_transaksi'];
        $nama_produk = $conn->real_escape_string($_POST['nama_produk']); // Sanitasi input
        $harga = $_POST['harga'];
        $qty = $_POST['qty'];
        $total_harga = $harga * $qty;

        // Menambahkan data ke table detail_transaksi
        $conn->query("INSERT INTO detail_transaksi (id_transaksi, nama_produk, harga, qty, total_harga) 
                    VALUES ($id_transaksi, '$nama_produk', '$harga', '$qty', '$total_harga')");

        // Hitung total_keseluruhan baru
        $result = $conn->query("SELECT SUM(total_harga) AS total_keseluruhan FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
        if ($result) {
            $row = $result->fetch_assoc();
            $total_keseluruhan = $row['total_keseluruhan'];

            // Meng-update total_keseluruhan di table transaksi
            $conn->query("UPDATE transaksi SET total_keseluruhan = $total_keseluruhan WHERE id = $id_transaksi");
        }

        header("Location: index.php?id=$id_transaksi");
        exit();
    }

    // Proses Pembayaran Transaksi
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['uang'])) {
        $uang = $_POST['uang'];
        $total_keseluruhan = $_POST['total_keseluruhan']; // Total keseluruhan dari form sebelumnya
        $kembalian = $uang - $total_keseluruhan;

        // Mengupdate status transaksi menjadi 'sudah'
        $conn->query("UPDATE transaksi SET status = 'sudah' WHERE id = $id_transaksi");

        header("Location: ../index.php");
        exit();
    }

    // Menampilkan data table detail_transaksi
    $result = $conn->query("SELECT * FROM detail_transaksi WHERE id_transaksi = $id_transaksi");

    // Ambil total keseluruhan
    $total_result = $conn->query("SELECT total_keseluruhan FROM transaksi WHERE id = $id_transaksi");
    $total_row = $total_result->fetch_assoc();
    $total_keseluruhan = $total_row['total_keseluruhan'];

    // Ambil status transaksi
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
        <link rel="stylesheet" href="../asset/css/style-detail.css">
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
                    <button id="tambah_detail_button">Tambah Detail Transaksi</button>
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
                            <button id="bayar_button">Bayar</button>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </main>

        <div id="tambah_detail" class="modal">
            <div class="modal-content">
                <span class="detail_close">&times;</span>
                <h1>Tambah Detail Transaksi</h1>
                <form method="post">
                    <input type="hidden" name="id_transaksi" value="<?php echo $_GET['id']; ?>">
                    <input type="text" name="nama_produk" id="nama_produk" placeholder="Masukkan Nama Barang">
                    <input type="number" name="harga" id="harga" placeholder="Masukkan Harga Barang">
                    <input type="number" name="qty" id="qty" placeholder="Masukkan Jumlah Barang">
                    <button type="submit" name="tambah">Submit</button>
                </form>
            </div>
        </div>

        <div id="bayar" class="modal">
            <div class="modal-content">
                <span class="bayar_close">&times;</span>
                <h1>Bayar Transaksi</h1>
                <form method="post">
                    <input type="text" placeholder="Total Keseluruhan: Rp <?php echo number_format($total_keseluruhan, 2, ',', '.'); ?>" readonly>
                    <input type="number" name="uang" id="uang" placeholder="Masukkan Jumlah Uang">
                    <input type="text" name="kembalian" id="kembalian" placeholder="kembalian" readonly>
                    <button type="submit">Bayar</button>
                </form>
            </div>
        </div>

        <script>
            // Modal Tambah Detail Transaksi
            var modalTambahDetail = document.getElementById("tambah_detail");
            var btnTambahDetail = document.getElementById("tambah_detail_button");
            var spanTambahDetail = document.getElementsByClassName("detail_close")[0];

            // Ketika pengguna mengklik tombol, buka modal tambah detail transaksi
            btnTambahDetail.onclick = function() {
                modalTambahDetail.style.display = "block";
            }

            // Ketika pengguna mengklik <span> (x), tutup modal tambah detail transaksi
            spanTambahDetail.onclick = function() {
                modalTambahDetail.style.display = "none";
            }

            // Ketika pengguna mengklik di luar modal, tutup modal
            window.onclick = function(event) {
                if (event.target == modalTambahDetail) {
                    modalTambahDetail.style.display = "none";
                }
            }

            // Modal Bayar Transaksi
            var modalBayar = document.getElementById("bayar");
            var btnBayar = document.getElementById("bayar_button");
            var spanBayar = document.getElementsByClassName("bayar_close")[0];

            // Ketika pengguna mengklik tombol, buka modal bayar transaksi
            btnBayar.onclick = function() {
                modalBayar.style.display = "block";
            }

            // Ketika pengguna mengklik <span> (x), tutup modal bayar transaksi
            spanBayar.onclick = function() {
                modalBayar.style.display = "none";
            }

            // Ketika pengguna mengklik di luar modal, tutup modal
            window.onclick = function(event) {
                if (event.target == modalBayar) {
                    modalBayar.style.display = "none";
                }
            }

            // Fungsi untuk menghitung kembalian ketika memasukkan uang
            document.getElementById('uang').addEventListener('input', function() {
                var uang = parseFloat(this.value) || 0;
                var total_keseluruhan = <?php echo $total_keseluruhan; ?>;
                var kembalian = uang - total_keseluruhan;
                document.getElementById('kembalian').value = 'Kembalian: Rp ' + kembalian.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            });
        </script>
    </body>

    </html>