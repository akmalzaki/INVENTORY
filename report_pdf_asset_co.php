<?php
require('fpdf186/fpdf.php');

// Class PDF untuk laporan
class PDF extends FPDF
{
    function FancyTable($header, $data)
    {
        // Validasi jika header atau data kosong
        if (empty($header) || empty($data)) {
            $this->Cell(0, 10, 'Tidak ada data untuk ditampilkan.', 0, 1, 'C');
            return;
        }

        // Warna header
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');

        // Header tabel
        $w = [40, 20, 20, 20, 40, 40];
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Reset warna dan font
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = false;

        // Data tabel
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row['asset_name'], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row['quantity_ordered'], 'LR', 0, 'C', $fill);
            $this->Cell($w[2], 6, $row['quantity_received'], 'LR', 0, 'C', $fill);
            $this->Cell($w[3], 6, $row['quantity_remaining'], 'LR', 0, 'C', $fill);
            $this->Cell($w[4], 6, $row['checkout_by'], 'LR', 0, 'L', $fill);
            $this->Cell($w[5], 6, date('M d, Y', strtotime($row['checkout_at'])), 'LR', 0, 'C', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// Ambil data dari database
include('connection.php');

try {
    $stmt = $conn->prepare("SELECT * FROM checkout ORDER BY checkout_at DESC");
    $stmt->execute();
    $checkout_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kelompokkan data berdasarkan tahun
    $grouped_data = [];
    if (!empty($checkout_data)) {
        foreach ($checkout_data as $data) {
            $year = date('Y', strtotime($data['checkout_at']));
            $grouped_data[$year][] = $data;
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Mulai membuat PDF
$pdf = new PDF();
$pdf->SetFont('Arial', '', 12);
$pdf->AddPage();

$pdf->Cell(80);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(30, 10, 'Checkout History Report', 0, 0, 'C');
$pdf->Ln(10);

// Kolom header tabel
$header = ['Asset Name', 'Ordered', 'Received', 'Remaining', 'Checked Out By', 'Checkout Date'];

// Tampilkan data per tahun
if (!empty($grouped_data)) {
    foreach ($grouped_data as $year => $data) {
        $pdf->SetFont('Arial', 'B', 11  );
        $pdf->Cell(0, 10, "Year: $year", 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->FancyTable($header, $data);
        $pdf->Ln(10);
    }
} else {
    $pdf->Cell(0, 10, 'Tidak ada data untuk ditampilkan.', 0, 1, 'C');
}

// Output file PDF
$pdf->Output();
