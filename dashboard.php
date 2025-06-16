<?php
session_start();
if (!isset($_SESSION['user']))
    header('location: login_pages.php');

$user = $_SESSION['user'];

include('status_graph.php'); // File untuk data grafik batang
include('status_graph_co.php'); // File untuk data Checkout
include('connection.php'); // Koneksi ke database

// Pagination untuk status graph
$page_bar = isset($_GET['page_bar']) ? (int)$_GET['page_bar'] : 1; // Halaman untuk Bar Chart
$page_line = isset($_GET['page_line']) ? (int)$_GET['page_line'] : 1; // Halaman untuk Line Chart

// Batas jumlah data per halaman untuk masing-masing grafik
$limit_bar = 4;  // Limit untuk Bar Chart
$limit_line = 1; // Limit untuk Line Chart (satu tahun per halaman)

// Offset untuk pagination
$offset_bar = ($page_bar - 1) * $limit_bar; // Offset untuk Bar Chart
$offset_line = ($page_line - 1) * $limit_line; // Offset untuk Line Chart

// Mengambil jumlah total assets dari database
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM assets");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalAssets = $result['total'];
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
    $totalAssets = 0;
}

// Menghitung total halaman untuk pagination untuk setiap grafik
$totalPagesBar = ceil($totalAssets / $limit_bar); // Total halaman untuk pagination Bar Chart
// Menghitung jumlah total data checkout (jumlah tahun yang unik)
try {
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT YEAR(checkout_at)) as total_years FROM checkout");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalYears = $result['total_years']; // Jumlah tahun yang ada di data checkout
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
    $totalYears = 0;
}

// Menghitung total halaman untuk pagination Line Chart berdasarkan jumlah tahun
$totalPagesLine = ceil($totalYears / $limit_line); // Total halaman untuk pagination Line Chart

// Mengambil data checkout per bulan dan tahun dari tabel checkout
try {
    $stmt = $conn->prepare("SELECT YEAR(checkout_at) as checkout_year, MONTH(checkout_at) as checkout_month, SUM(quantity_ordered) as total_checkout 
                            FROM checkout 
                            GROUP BY checkout_year, checkout_month
                            ORDER BY checkout_year, checkout_month");
    $stmt->execute();
    $checkout_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $checkout_json = json_encode($checkout_data);
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}

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
        <?php include('partials/app-sidebar.php') ?>
        <div class="dashboard_content_container" id="dashboard_content_container">
            <?php include('partials/app-topnav.php') ?>
            <div class="dashboard_content">
                <div class="dashboard_content_main">

                    <!-- Chart Container with Flexbox -->
                    <div class="chart-container">
                        <!-- Bar Chart -->
                        <div class="chart-box">
                            <h1 class="section_header">Assets Stock</h1>
                            <canvas id="myChart"></canvas>
                            <!-- Pagination Controls -->
                            <div class="pagination_controls">
                                <?php if ($page_bar > 1): ?>
                                    <a href="?page_bar=<?= $page_bar - 1 ?>" class="pagination_button">Previous</a>
                                <?php endif; ?>
                                <span class="pagination_button active"><?= $page_bar ?></span>
                                <?php if ($page_bar < $totalPagesBar): ?>
                                    <a href="?page_bar=<?= $page_bar + 1 ?>" class="pagination_button">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Line Chart -->
                        <div class="chart-box">
                            <h1 class="section_header">Checkout Assets Per Month</h1>
                            <canvas id="checkoutLineChart"></canvas>
                            <!-- Pagination Controls -->
                            <div class="pagination_controls">
                                <?php if ($page_line > 1): ?>
                                    <a href="?page_line=<?= $page_line - 1 ?>" class="pagination_button">Previous</a>
                                <?php endif; ?>
                                <span class="pagination_button active"><?= $page_line ?></span>
                                <?php if ($page_line < $totalPagesLine): ?>
                                    <a href="?page_line=<?= $page_line + 1 ?>" class="pagination_button">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
    // Grafik Batang (Data dari status_graph.php)
    fetch('status_graph.php?json=1&page=<?= $page_bar ?>&limit=<?= $limit_bar ?>')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.colAssets);
            const values = data.map(item => item.colStocks);

               // Warna untuk data ganjil dan genap
        const adjustedBackgroundColors = labels.map((_, i) =>
        i % 2 === 0 ? 'rgba(205, 188, 185)' : 'rgba(102, 0, 0)'
    );

            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '',
                        data: values,
                        backgroundColor: adjustedBackgroundColors,
                        borderWidth: 1,
                        borderRadius: 20
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#ddd' }, ticks: { stepSize: 2 } }
                    },
                    plugins: {
                        legend: { display: false }, 
                        tooltip: { enabled: true, backgroundColor: 'rgba(0,0,0,0.7)', bodyFont: { size: 14 } }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching bar chart data:', error));

       // Fungsi untuk menghasilkan warna random dalam format RGBA
function getRandomColor() {
    const r = Math.floor(Math.random() * 256);  // Random red
    const g = Math.floor(Math.random() * 256);  // Random green
    const b = Math.floor(Math.random() * 256);  // Random blue
    const a = 1; // Opacity penuh
    return `rgba(${r}, ${g}, ${b}, ${a})`;
}

// Grafik Garis (Data Checkout per Bulan)
const checkoutData = <?php echo $checkout_json; ?>;  // Data Checkout dari PHP

// Menyiapkan data labels dan data total checkout
const monthNames = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

// Menyusun data berdasarkan tahun
const dataByYear = {};

checkoutData.forEach(item => {
    const month = item.checkout_month - 1;  // PHP menggunakan 1-12 untuk bulan, JavaScript menggunakan 0-11
    const yearMonth = `${monthNames[month]}`;

    // Kelompokkan data berdasarkan tahun
    if (!dataByYear[item.checkout_year]) {
        dataByYear[item.checkout_year] = [];
    }
    dataByYear[item.checkout_year].push({
        month: yearMonth,
        total_checkout: item.total_checkout
    });
});

// Menyaring data berdasarkan halaman (tahun)
const years = Object.keys(dataByYear).sort((a, b) => b - a);  // Mendapatkan semua tahun dari data
const currentYear = years[<?= $page_line - 1 ?>];  // Menentukan tahun yang relevan untuk halaman ini

const filteredData = dataByYear[currentYear];

// Menyiapkan label bulan/tahun (pastikan semua bulan ada, bahkan yang tidak ada data)
const labels = monthNames; // Menampilkan semua bulan dari Jan hingga Dec
const checkoutPerMonth = monthNames.map(month => {
    // Mencari data untuk bulan tertentu, jika tidak ada, set ke 0
    const monthData = filteredData.find(item => item.month === month);
    return monthData ? monthData.total_checkout : 0;
});

// Menghasilkan warna acak untuk tahun saat ini
const color = getRandomColor(); // Warna yang akan digunakan untuk garis dan titik

// Menyiapkan data untuk grafik garis
const datasets = [{
    label: `${currentYear}`,
    data: checkoutPerMonth,
    backgroundColor: color,  // Warna latar belakang titik
    borderColor: color,  // Warna garis
    borderWidth: 2,
    fill: false, 
    pointRadius: 5,
    pointBackgroundColor: color,  // Warna titik
    pointBorderColor: '#fff',
    pointBorderWidth: 2,
    lineTension: 0.4
}];

// Membuat grafik garis dengan Chart.js
const ctx = document.getElementById('checkoutLineChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: datasets
    },
    options: {
        responsive: true,
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 12 }, color: '#555' } },
            y: {
                beginAtZero: true,
                grid: { borderDash: [5, 5], color: '#ddd' },
                ticks: { font: { size: 14 }, color: '#555' }
            }
        },
        plugins: {
            legend: { position: 'top', labels: { font: { size: 14 }, color: '#333' } },
            tooltip: { enabled: true, backgroundColor: 'rgba(0,0,0,0.7)', bodyFont: { size: 14 } }
        }
    }
});

    </script>
</body>

</html>
