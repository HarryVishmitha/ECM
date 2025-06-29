<!-- index page -->
<?php
require 'env.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - <?php echo $name ?></title>
    <link rel="stylesheet" href="inc/css/stylesheet.css">
    <script src="inc/js/script.js"></script>
</head>

<body>
    <?php
    // include top navbar
    include 'components/topnav.php';
    ?>
    <!-- Header section -->
    <header class="header-slider">
        <div class="slides">
            <img src="https://picsum.photos/id/1015/1920/400" alt="Scenic mountain">
            <img src="https://picsum.photos/id/1025/1920/400" alt="Forest path">
            <img src="https://picsum.photos/id/1035/1920/400" alt="City skyline">
        </div>
        <div class="controls">
            <button class="prev" aria-label="Previous slide">&#10094;</button>
            <button class="next" aria-label="Next slide">&#10095;</button>
        </div>
        <div class="dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </header>

    <!-- category view -->
    <div class="categories" id="categories">

    </div>
</body>

</html>