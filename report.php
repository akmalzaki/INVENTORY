<?php
// Start the session.
session_start();

if (!isset($_SESSION['user'])) {
    header('location: login_pages.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script src="https://use.fontawesome.com/0c7a3095b5.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php'); ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/app-topnav.php'); ?>
            <div class="dashboard_content" id="dashboard_content">
                <div class="reportsWrapper">
                    <div class="reportsContainer">
                        <!-- Export Assets -->
                        <div class="reportBox">
                            <h1 class="report_header">Export Assets</h1>
                            <div class="alignRight">
                                <a href="report_csv.php?report=asset" class="reportExportBtnExc">Excel</a>
                                <a href="report_pdf.php?report=asset" class="reportExportBtnPDF">PDF</a>
                            </div>
                        </div>

                        <!-- Export Checkout -->
                        <div class="reportBox">
                            <h1 class="report_header">Export Checkout</h1>
                            <div class="alignRight">
                                <a href="report_csv_asset_co.php?report=checkout" class="reportExportBtnExc">Excel</a>
                                <a href="report_pdf_asset_co.php?report=checkout" class="reportExportBtnPDF">PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>

</html>