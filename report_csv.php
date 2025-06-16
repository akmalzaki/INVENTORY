<?php
$type = $_GET['report'];
$file_name = "Assets_Report.xls";

header("Content-Disposition: attachment; filename=\"$file_name\"");
header("Content-Type: application/vnd.ms-excel");

// Database connection
include('connection.php');

if ($type === 'asset') {
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

    // Start generating HTML table
    echo '<table border="1" style="border-collapse: collapse;">';

    // Header row with specific column widths
    echo '
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th style="width: 150px;">Asset Name</th>
                <th style="width: 80px;">Stock</th>
                <th style="width: 200px;">Created By</th>
                <th style="width: 200px;">Created At</th>
                <th style="width: 200px;">Updated At</th>
            </tr>
        </thead>
    ';

    // Data rows
    echo '<tbody>';
    foreach ($assets as $asset) {
        $created_by = $asset['first_name'] . ' ' . $asset['last_name'];
        $created_at = date('M d, Y', strtotime($asset['created_at']));
        $updated_at = date('M d, Y', strtotime($asset['updated_at']));

        echo '
            <tr>
                <td>' . $asset['pid'] . '</td>
                <td>' . $asset['asset_name'] . '</td>
                <td>' . $asset['stock'] . '</td>
                <td>' . $created_by . '</td>
                <td>' . $created_at . '</td>
                <td>' . $updated_at . '</td>
            </tr>
        ';
    }
    echo '</tbody>';

    echo '</table>';
}
?>
