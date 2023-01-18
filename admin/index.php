<?php
$_head="<title>Admin</title>";
$_dir = "../";
require_once $_dir."res/content/main.php";

//---------------------------LOGIN---------------------------
session_id("admin");
session_start();
if (array_key_exists('login', $_POST)) {
	if ($_POST['pass']=="toor" && $_POST['user']=="root") {
			$_SESSION['admin'] = true;
			header('location:?v=admin');
	} else {
			$err = "Bad Credentials";
		}
}
if (array_key_exists('logout', $_POST)) {
	session_destroy();
	header('location:?');
}
?>

<div id='err' class='err <?php echo " ".$err_type." "; if($err == ""){echo "err-hide";} ?>'><div onclick="err('err')" class='close'>&times;</div>
    <?php echo $err;?>
</div>
<?php
if(isset($_SESSION['admin']) && $_SESSION['admin'] == true){ ?>
    <div class="bar">
        <a href="?v=admin">Home</a>
        <a href="?v=transactions">Transactions</a>
        <a href="?v=misc">Misc</a>
        <a class="right"><?php echo $_time2;?></a>
    </div>
<?php
if($_v=="transactions"){?>
    <div class="card history"><?php require_once "transactions";?></div>
<?php }
else if($_v=="admin"){?>
    <div class="card">
        <form method="post">
            <button type="submit" name="logout" class="btn-red">Logout</button>
        </form>
    </div>
<?php }
else if($_v=="misc"){?>
    <div class="card">
        <a target="_blank" href="http://localhost/phpmyadmin">db admin</a><br>
        <a target="_blank" href="<?php echo $_dir;?>res/css/">/res/css/</a><br>
        <a target="_blank" href="<?php echo $_dir;?>res/js/">/res/js/</a><br>
        <a target="_blank" href="<?php echo $_dir;?>res/css/style.css">/res/css/style.css</a><br>
        <a target="_blank" href="<?php echo $_dir;?>res/js/script.js">/res/js/script.js</a><br>
    </div>
<?php }
}
else{ ?>
    <div class="card">
        <h3>Admin Panel</h3><hr><br>
        <form method="post">
            <input placeholder="Username" name="user" required><br><br>
            <input type="password" placeholder="Password" name="pass" required><br><br>
            <button type="submit" name="login" class="btn-blue">Login</button>
            <button type="reset" class="btn-red">Clear</button>
        </form>
    </div>
<?php }
echo $_end;
?>