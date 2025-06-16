<?php
session_start();
include('connection.php'); // Database connection

try {
    // Check if asset ID is provided
    if (isset($_POST['asset_id'])) {
        $asset_id = $_POST['asset_id'];

        // Fetch asset data
        $stmt = $conn->prepare("SELECT * FROM assets WHERE id = ?");
        $stmt->execute([$asset_id]);
        $asset = $stmt->fetch(PDO::FETCH_ASSOC);

        // If asset doesn't exist, redirect
        if (!$asset) {
            $_SESSION['message'] = "Asset not found!";
            $_SESSION['msg_type'] = "error";
            header("location: asset-view.php");
            exit;
        }
    } else {
        // Redirect if no asset ID provided
        header("location: asset-view.php");
        exit;
    }

    // Handle form submission to update asset
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_asset'])) {
        
        $asset_name = $_POST['asset_name'];
        $asset_type = $_POST['asset_type'];
        $asset_info_detail = $_POST['assetInfo'];
       

        // Get quantities to add and remove
        $quantity_to_add = isset($_POST['quantity_add']) ? intval($_POST['quantity_add']) : 0;
        $quantity_to_remove = isset($_POST['quantity_rmv']) ? intval($_POST['quantity_rmv']) : 0;

        // Validate quantities
        if ($quantity_to_add < 0 || $quantity_to_remove < 0) {
            $_SESSION['message'] = "Quantities must be non-negative!";
            $_SESSION['msg_type'] = "error";
            header("location: asset-edit.php?asset_id=" . $asset_id);
            exit;
        }

        // Get current stock from the database
        $current_stock = $asset['stock'];

        // Calculate the new stock (add and remove)
        $new_stock = $current_stock + $quantity_to_add - $quantity_to_remove;

        // Ensure the stock doesn't go negative
        if ($new_stock < 0) {
            $_SESSION['message'] = "Stock cannot be negative!";
            $_SESSION['msg_type'] = "error";
            header("location: asset-edit.php?asset_id=" . $asset_id);
            exit;
        }

        // Handle image upload
        $img_name = $asset['img']; // Default to existing image
        if (!empty($_FILES['img']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['img']['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $_SESSION['message'] = "Invalid image type! Allowed types: JPEG, PNG, GIF.";
                $_SESSION['msg_type'] = "error";
                header("location: asset-edit.php?asset_id=" . $asset_id);
                exit;
            }

            if ($_FILES['img']['size'] > 2 * 1024 * 1024) { // 2MB limit
                $_SESSION['message'] = "Image size exceeds 2MB!";
                $_SESSION['msg_type'] = "error";
                header("location: asset-edit.php?asset_id=" . $asset_id);
                exit;
            }

            $img_name = time() . '_' . $_FILES['img']['name'];
            move_uploaded_file($_FILES['img']['tmp_name'], 'uploads/products/' . $img_name);
        }

        // Update asset in the database
        $query = "UPDATE assets 
                  SET 
                      asset_name = ?, 
                      asset_type = ?, 
                      asset_info_detail = ?, 
                     
                      stock = ?, 
                      img = ?, 
                      updated_at = NOW() 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            
            $asset_name,
            $asset_type,
            $asset_info_detail,
            
            $new_stock,
            $img_name,
            $asset_id
        ]);

        $_SESSION['message'] = "Asset updated successfully!";
        $_SESSION['msg_type'] = "success";
        header('location: asset-view.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['msg_type'] = "error";
    header("location: asset-view.php");
    exit;
}
?>