<?php

session_start();
// if(!isset($_SESSION['user'])) header('location: login_pages.php'); 


$error_message = '';
if ($_POST) {
    include('connection.php');
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = 'SELECT * FROM users WHERE users.email="' . $username . '" AND users.password="' . $password . '"';

    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $user = $stmt->fetchAll()[0];
        $_SESSION['user'] = $user;

        header('Location: dashboard.php');
    } else
        $error_message = 'Please make sure that username and password are correct';
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Login Page</title>
    <link rel="stylesheet" href="css/ims.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap"
        rel="stylesheet">
</head>

<body id='loginBody'>

    <div class="wrapper">
        <div class="container">
            <div class="left-section">
                <img src="assets/bg1.jpeg" alt="Building Image">
            </div>
            <div class="right-section">
                <div class="login-box">
                    <h2>Inventory Management System <span class="brand"> KCIC</span></h2>

                    <form action="login_pages.php" method="POST">
                        <div class="input-box">

                            <input type="text" name="username" placeholder="Username">
                        </div>

                        <div class="input-box">
                            <input type="password" name="password" placeholder="Password">
                        </div>
                        
                        <div class="remember-me">
                            <label>
                                <input type="checkbox" name="remember" id="remember">
                                Remember me
                            </label>
                        </div>

                        <div class="buttons">
                            <button type="submit" class="btn-login">Login</button>

                        </div>

                        <?php
                        if (!empty($error_message)) { ?>
                            <div id="errorMessage">
                                <p> Error: <?= $error_message ?> </p>
                            </div>
                        <?php } ?>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
    const img = document.querySelector('.left-section img');
    const body = document.getElementById('loginBody');

    img.onload = function () {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;

        // Gambarkan gambar pada canvas
        ctx.drawImage(img, 0, 0, img.width, img.height);

        // Ambil warna dari beberapa titik pada gambar
        const color1 = getDominantColor(ctx, 0, 0, 10, 10); // Atas kiri
        const color2 = getDominantColor(ctx, img.width - 10, img.height - 10, 10, 10); // Bawah kanan

        // Terapkan gradasi warna pada background body
        body.style.background = `linear-gradient(90deg, ${color1} 23%, ${color2} 80%)`;
    };

    function getDominantColor(ctx, x, y, width, height) {
        const imageData = ctx.getImageData(x, y, width, height).data;
        let r = 0, g = 0, b = 0, count = 0;

        for (let i = 0; i < imageData.length; i += 4) {
            r += imageData[i];
            g += imageData[i + 1];
            b += imageData[i + 2];
            count++;
        }

        r = Math.floor(r / count);
        g = Math.floor(g / count);
        b = Math.floor(b / count);

        return `rgb(${r}, ${g}, ${b})`;
    }

    // ===== SCRIPT REMEMBER ME =====
    document.addEventListener("DOMContentLoaded", function () {
        const usernameInput = document.getElementById("usernameInput");
        const rememberCheckbox = document.getElementById("rememberCheckbox");

        // Cek apakah username pernah disimpan
        const savedUsername = localStorage.getItem("rememberedUsername");
        if (savedUsername) {
            usernameInput.value = savedUsername;
            rememberCheckbox.checked = true;
        }

        // Saat form dikirim
        document.querySelector("form").addEventListener("submit", function () {
            if (rememberCheckbox.checked) {
                localStorage.setItem("rememberedUsername", usernameInput.value);
            } else {
                localStorage.removeItem("rememberedUsername");
            }
        });
    });
</script>


</body>

</html>