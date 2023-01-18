<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo $_dir; ?>res/css/style.css">
    <script src="<?php echo $_dir; ?>res/js/jquery.js"></script>
    <script src="<?php echo $_dir; ?>res/js/script.js"></script>
    <?php echo $_head; 
        //include_once $_dir."res/css/style.php";
    ?>
</head>
<body>
<?php
    error_reporting(0);
    $_date=date("d-m-Y");
    $_time=date("h:i:s A");
    $_time2=date("h:i A");
    $_end="</body></html>";
    $_v = $_GET['v'];
?>