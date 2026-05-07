<?php

function output_header(){ ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <!-- FitPeak Homepage: Main landing page with hero, features, pricing, and CTA -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="FitPeak — Gym management platform. Classes, trainers and equipment all in one place.">
        <meta name="theme-color" content="#0b0b0b">
        <title>FitPeak — The Gym That Grows With You</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Stylesheets -->
        <link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/components.css">
        <link rel="stylesheet" href="css/pages/index.css">
    </head> 

    <header>
        <nav class="navbar" aria-label="Main navigation">
            <a href="index.html" class="logo">FIT<span>PEAK</span></a>

            <label for="nav-toggle" class="burger">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <ul class="nav-menu">
                <li><a href="index.html" aria-current="page" class="active">Home</a></li>
                <li><a href="classes.html">Classes</a></li>
                <li><a href="trainers.html">Trainers</a></li>
                <li><a href="equipment.html">Equipment</a></li>
                <li><a href="login.html" class="nav-login">Login</a></li>
                <li><a href="register.html" class="nav-register">Sign Up</a></li>
            </ul>
        </nav>
    </header>
    
    <?php } ?>
