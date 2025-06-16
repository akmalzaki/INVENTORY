<?php
// Assuming you have a database connection set up already (e.g., $conn)
$sql = "SELECT DATE(checkout_at) as checkout_date, SUM(quantity_ordered) as total_checkout 
        FROM checkout 
        GROUP BY checkout_date 
        ORDER BY checkout_date ASC"; // Make sure checkout_at exists in your table
$stmt = $conn->query($sql);

$checkoutData = [];
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $checkoutData[] = [
            'checkout_date' => $row['checkout_date'],
            'total_checkout' => (int) $row['total_checkout'] // Ensure total_checkout is an integer
        ];
    }
}

// Encode the data as JSON
$checkout_json = json_encode($checkoutData);
?>