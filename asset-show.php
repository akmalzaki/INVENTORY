<?php
include('connection.php');

$stmt = $conn->prepare("SELECT * FROM assets ORDER BY id ASC");
$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

return $stmt->fetchAll();
