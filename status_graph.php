<?php
header('Content-Type: application/json');
include('connection.php');

// Logika pagination sederhana yang diterima dari URL
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query sederhana untuk mengambil data aset tanpa filter
$sql = "SELECT asset_name AS colAssets, asset_location as colLocation, stock AS colStocks 
        FROM assets 
        ORDER BY id ASC 
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>