<?php
include('connection.php');

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4; // Jumlah data per halaman
$offset = ($page - 1) * $limit;

$sql = "SELECT asset_name AS colAssets, stock AS colStocks FROM assets LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

if (!$stmt) {
    die("Query Error: " . $conn->errorInfo()[2]);
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for a 'json' query parameter to decide whether to output JSON
if (isset($_GET['json'])) {
    echo json_encode($data);
} else {
    // Output HTML if not in JSON mode
    foreach ($data as $item) {
       
    }
}
?>
