<?php
$type = $_GET['report'];
$file_name = "Checkout_Report.xls";

header("Content-Disposition: attachment; filename=\"$file_name\"");
header("Content-Type: application/vnd.ms-excel");

// Database connection
include('connection.php');

// Validasi tipe laporan
if ($type === 'checkout') {
    $stmt = $conn->prepare("
        SELECT * 
        FROM checkout
        ORDER BY checkout_at DESC
    ");
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $checkouts = $stmt->fetchAll();

    // Kelompokkan data berdasarkan tahun checkout
    $grouped_data = [];
    foreach ($checkouts as $checkout) {
        $year = date('Y', strtotime($checkout['checkout_at']));
        $grouped_data[$year][] = $checkout;
    }

    // Mulai membuat tabel HTML
    echo '<html><body>';

    // Loop setiap kelompok tahun
    foreach ($grouped_data as $year => $data_group) {
        echo "<h3>Tahun: $year</h3>"; // Header untuk tahun

        echo '<table border="1" style="border-collapse: collapse; margin-bottom: 20px;">';

        // Header tabel dengan lebar kolom yang spesifik
        echo '
            <thead>
                <tr>
                    <th style="width: 200px;">Asset Name</th>
                    <th style="width: 100px;">Quantity Ordered</th>
                    <th style="width: 100px;">Quantity Remaining</th>
                    <th style="width: 200px;">Checked Out By</th>
                    <th style="width: 200px;">Checkout Date</th>
                </tr>
            </thead>
        ';

        // Baris data
        echo '<tbody>';
        foreach ($data_group as $checkout) {
            $checkout_date = date('M d, Y h:i:s A', strtotime($checkout['checkout_at']));

            echo '
                <tr>
                    <td>' . htmlspecialchars($checkout['asset_name']) . '</td>
                    <td>' . htmlspecialchars($checkout['quantity_ordered']) . '</td>
                    <td>' . htmlspecialchars($checkout['quantity_remaining']) . '</td>
                    <td>' . htmlspecialchars($checkout['checkout_by']) . '</td>
                    <td>' . $checkout_date . '</td>
                </tr>
            ';
        }
        echo '</tbody>';

        echo '</table>';
    }

    echo '</body></html>';
}
?>
