<?php
require('fpdf186/fpdf.php');

class PDF extends FPDF
{
    function _construct()
    {
        parent::__construct('L');
    }
    // Colored table
    function FancyTable($header, $data)
    {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        // Header
        $w = array(10, 35, 15, 30, 50, 50);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');

        $fill = false;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'C', $fill);
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row[2], 'LR', 0, 'C', $fill);
            $this->Cell($w[3], 6, $row[3], 'LR', 0, 'C', $fill);
            $this->Cell($w[4], 6, $row[4], 'LR', 0, 'L', $fill);
            $this->Cell($w[5], 6, $row[5], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

$type = $_GET['report'];
$report_headers = [
    'asset' => 'Asset Reports'
];

// Pull data from database.
include('connection.php');

if ($type == 'asset') {
    // Column headings - replace from MySQL database or hardcode it
    $header = array('id', 'asset_name', 'stock', 'created_by', 'created_at', 'updated_at');

    // Load product
    $stmt = $conn->prepare("
    SELECT 
    assets.id as pid, 
    assets.asset_name, 
    assets.stock, 
    assets.created_at, 
    assets.updated_at, 
    users.first_name, 
    users.last_name
FROM assets
INNER JOIN users 
ON assets.created_by = users.id
ORDER BY assets.created_at ASC;

");
$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

$assets = $stmt->fetchAll();

    $data = [];
    foreach ($assets as $asset) {
        $asset['created_by'] = $asset['first_name'] . ' ' . $asset['last_name'];
        unset($asset['first_name'], $asset['last_name'], $asset['password'], $asset['email']);

        array_walk($asset, function (&$str) {
            if ($str !== null) {  // Pastikan $str bukan null
                $str = preg_replace("/\t/", "\\t", $str);
                $str = preg_replace("/\r?\n/", "\\n", $str);
                if (strstr($str, '"')) {
                    $str = '"' . str_replace('"', '""', $str) . '"';
                }
            }
        });
        

        $data[] = [
            $asset['pid'],
            $asset['asset_name'],
            // $asset['description'],
            // $asset['img'],
            number_format($asset['stock']),
            $asset['created_by'],
            date('M d,Y', strtotime($asset['created_at'])),
            date('M d,Y', strtotime($asset['updated_at'])),
        ];
    }
}

// Start PDF
$pdf = new PDF();
$pdf->SetFont('Arial', '', 16);
$pdf->AddPage();

$pdf->Cell(80);
$pdf->Cell(30, 10, $report_headers[$type], 0, 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Ln();
$pdf->Ln();

$pdf->FancyTable($header, $data);
$pdf->Output();
?>