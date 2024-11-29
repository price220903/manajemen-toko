<?php
require('fpdf/fpdf.php');
include 'database.php';

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Laporan Transaksi', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }

    function TransactionTable($header, $transactions)
    {
        $widths = array(10, 110, 60); // Lebar kolom untuk tabel transaksi (kolom status dihapus)

        $this->SetFont('Arial', '', 10);
        $no = 1;
        foreach ($transactions as $transaction) {
            foreach ($header as $index => $col) {
                 $this->SetFont('Arial', 'B', 12);
                $this->Cell($widths[$index], 7, $col, 1, 0, 'C'); // Rata tengah
            }
            $this->Ln();
            $this->Cell($widths[0], 6, $no++, 1, 0, 'C'); // Rata tengah
            $this->Cell($widths[1], 6, $transaction['tanggal_transaksi'], 1, 0, 'C'); // Rata tengah
            $this->Cell($widths[2], 6, 'Rp. ' . number_format($transaction['total_keseluruhan'], 2, ',', '.'), 1, 0, 'C'); // Rata tengah
            $this->Ln();

            $this->DetailTransactionTable(
                array('Nama Produk', 'Harga', 'Qty', 'Total Harga'),
                $transaction['details']
            );
            $this->Ln();
        }
    }

    function DetailTransactionTable($header, $details)
    {
        $this->SetFont('Arial', 'B', 12);
        $widths = array(60, 40, 20, 60); // Lebar kolom untuk tabel detail transaksi
        foreach ($header as $index => $col) {
            $this->Cell($widths[$index], 7, $col, 1, 0, 'C'); // Rata tengah
        }
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        foreach ($details as $detail) {
            $this->Cell($widths[0], 6, $detail['nama_produk'], 1, 0, 'C'); // Rata tengah
            $this->Cell($widths[1], 6, 'Rp. ' . number_format($detail['harga'], 2, ',', '.'), 1, 0, 'C'); // Rata tengah
            $this->Cell($widths[2], 6, $detail['qty'], 1, 0, 'C'); // Rata tengah
            $this->Cell($widths[3], 6, 'Rp. ' . number_format($detail['total_harga'], 2, ',', '.'), 1, 0, 'C'); // Rata tengah
            $this->Ln();
        }
    }
}

$pdf = new PDF();
$pdf->AddPage();

$transaction_header = array('No', 'Tanggal Transaksi', 'Total Transaksi'); // Kolom status dihapus

// Fetch data transaksi
$result = $conn->query("SELECT * FROM transaksi");
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $id_transaksi = $row['id'];
    $detail_result = $conn->query("SELECT * FROM detail_transaksi WHERE id_transaksi = $id_transaksi");
    $details = [];
    while ($detail_row = $detail_result->fetch_assoc()) {
        $details[] = $detail_row;
    }
    $row['details'] = $details;
    $transactions[] = $row;
}

$pdf->TransactionTable($transaction_header, $transactions);

$pdf->Output();
