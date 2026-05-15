<?php

function output_header($pageCss) { ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="FitPeak — Gym management platform. Classes, trainers and equipment all in one place.">
        <meta name="theme-color" content="#0b0b0b">
        <title>FitPeak</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="../css/base.css">
        <link rel="stylesheet" href="../css/components.css">
        <link rel="stylesheet" href="../css/pages/<?php echo $pageCss; ?>">
    </head>
    <body>

    <input type="checkbox" id="nav-toggle" class="nav-toggle">

    <header>
        <nav class="navbar" aria-label="Main navigation">
            <a href="../index.php" class="logo">FIT<span>PEAK</span></a>

            <label for="nav-toggle" class="burger">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../pages/classes.php">Classes</a></li>
                <li><a href="../pages/trainers.php">Trainers</a></li>
                <li><a href="../pages/equipment.php">Equipment</a></li>
                <li><a href="../pages/login.php" class="nav-login">Login</a></li>
                <li><a href="../pages/register.php" class="nav-register">Sign Up</a></li>
            </ul>
        </nav>
    </header>

<?php } ?>