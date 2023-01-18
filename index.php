<?php
$_dir="";
$_head="<title>Login</title>";
require_once $_dir."res/content/main.php";
require_once $_dir."res/content/db_config.php";
$_page = "index.php";
$_page_client = "user.php";
session_id("user");
session_start();
?>

<div class="bar">
    <a href="?">Login</a>
    <a href="?v=signup">Create Account</a>
    <a href="?v=reset">Reset Password</a>
</div>

<?php

//---------------------------LOGIN---------------------------

if (array_key_exists('login', $_POST)) {
	$user = $_POST['user'];
	$login_find = mysqli_query($con, "SELECT pass FROM munish WHERE user = '$user'");
	if (mysqli_num_rows($login_find) == 1) {
		$login_data = mysqli_fetch_assoc($login_find);
		if ($login_data['pass'] == $_POST['pass']) {
			$_SESSION['logged'] = true;
			$_SESSION['user'] = $user;
			header('location:'.$_page_client);
		} else {
			$err = "Incorrect Password";
		}
	} else {
		$err = "User not found..";
	}
}

//--------------------------SIGNUP-----------------

if (array_key_exists('signup', $_POST)) {
    $user = $_POST['user'];
	$signup_find = mysqli_query($con, "SELECT user FROM munish WHERE user = '$user' ");
	if (mysqli_num_rows($signup_find) == 0) {
        $email = $_POST['email'];
	    $pass = $_POST['pass'];        
        $signup_put = mysqli_query($con, "INSERT INTO munish (user,email,pass) VALUES('$user','$email','$pass')");
        if ($signup_put) {
            $err_type="err-grn";
            $err = "Account Created Successfully.. But not activated yet! Please verify email to activate<br>Add your Profile details on next login.<br><br><form method='post'><button name='signup_login' class='btn-blue'>Login now</button></form>";
		} else {
			$err = "Unable to Create Account! An Error occured.";
		}
	} else {
		$err = "Username is already taken..";
    }
}

if(array_key_exists('signup_login',$_POST)){
    $_SESSION['logged'] = true;
    header('location:'.$_page_client);
}

//---------------------------------RESET--------------------

if (array_key_exists('reset_user', $_POST)) {
	$_SESSION['user'] = $user = $_POST['user'];
	$reset_find = mysqli_query($con, "SELECT email,ques,ans,setting_security,setting_email_verify FROM munish WHERE user='$user' ");
    if (mysqli_num_rows($reset_find) == 1) {
        $reset_data = mysqli_fetch_assoc($reset_find);
        if($_POST['reset_option'] == "use_otp"){
            if($reset_data['setting_email_verify']==true){
                $_SESSION['otp'] = $otp = rand(100000, 999999);
                $email = $reset_data['email'];
                require_once 'res/content/mail.php';
                $mail->addAddress($email, '');
                $mail->Subject = 'Your Verification OTP';
                $mail->Body="<h1>Password Reset Request</h1><p>A request to reset account password was made by you. To continue process use the OTP below: </p><br>Your OTP is: <b>".$otp."</b><br><p>Please Don't share this with anyone.</p><h2>Munish Inc.</h2>Visit our website <a href='http://munish.rf.gd'>Click here</a>";
                if (!$mail->send()) {
                    $err= "Error: Mail not sent. ";
                }else{
                header("location:".$_page."?v=reset_otp");
                }
            }else{
                $err ="Your Email Id is not Verified! You can't use this option..";
            }
        }
        else if($_POST['reset_option'] == "use_ques"){
            if($reset_data['setting_security']==true){
                $_SESSION['ans'] = $reset_data['ans'];
                if ($reset_data['ques'] == 1)
                    $_SESSION['ques'] = "Your Favorite Pet?";
                else if ($reset_data['ques'] == 2)
                    $_SESSION['ques'] = "Your Favorite Teacher?";
                else if ($reset_data['ques'] == 3)
                    $_SESSION['ques'] = "Your Favorite City?";
                header("location:".$_page."?v=reset_ques");
            }else{
                $err = "Your security question is not set. You can't use this Option.";
            }
        }
	} else {
		$err = "No User Found..";
	}
}

//---------------------------------RESET-OTP-------------------

if (array_key_exists('reset_opt', $_POST)) {
    if ($_POST['otp'] == $_SESSION['otp']) {
        $pass = $_POST['pass'];
        $reset_put = mysqli_query($con, "UPDATE munish SET pass='$pass' WHERE user='$user'");
        if ($reset_put) {
			$err_type="err-grn";
            $err = "Password changed Successfully..<br><br><a href='?v=login'><button class='btn-blue'>Go Back to Login</button></a>";
		} else {
			$err = "Unable to Change password. Try Again..";
		}
        session_destroy();
    } else {
        $err = "Incorrect OTP";
    }		
}

//-------------------------------RESET-QUES------------------------

if (array_key_exists('reset_ques', $_POST)) {
	if ($_POST['ans'] == $_SESSION['ans']) {
		$user = $_SESSION['user'];
		$pass = $_POST['pass'];
		$reset_put = mysqli_query($con, "UPDATE munish SET pass = '$pass' WHERE user = '$user' ");
		if ($reset_put) {
            $err_type="err-grn";
			$err = "Password Changed Successfully..<br><br><a href='?v=login'><button class='btn-blue'>Go Back to Login</button></a>";
		} else {
			$err = "Error".mysqli_error($con);
        }
        session_destroy();
	} else {
		$err = "Wrong Answer..";
	}
}

if (array_key_exists('reset_cancel', $_POST)) {
    session_destroy();
    header("location:".$_page."?v=reset");
}

//----------------------------------ERROR-----------------------

?>

<div id='err' class='err <?php echo " ".$err_type." "; if($err == ""){echo "err-hide";} ?>'><div onclick="err('err')" class='close'>&times;</div>
    <?php echo $err;?>
</div>

<?php

//-------------------SIGNUP-TAB-----------------------

if($_v=="signup"){ 
    if(isset($_SESSION['logged']) && $_SESSION['logged'] == true){
        header("location:".$_page_client);
    }
    ?>
    <div class="card">
        <h3>Sign Up</h3><hr>
		<form method="POST">
            <div class="card">
                <h4>Notice:</h4>
                1. Username must start with an '@' and contain atleast 3 characters.<br>
                2. Password must contain atleast 4 character.<br>
            </div><br><br>
            Username: <br>
            <input pattern="@+[a-z0-9.-_]{3,}" value="@" placeholder=" @username" name="user" required><br><br>
            Email: <br>
            <input type="email" placeholder=" Your Email Id" name="email" required><br><br>
            Password: <br>
            <input pattern=".{4,}" placeholder=" Password" type="password" name="pass" required><br><br>
			<button class="btn-blue" type="submit" name="signup"> Next</button>
		</form>
    </div>
<?php }

//-------------------------RESET-TAB-------------------------------

else if($_v=="reset"){ ?>
    <div class="card">
		<h3>Forgot your password?</h3><hr><br>
		<form method="POST">
            Enter your Username: <br>
            <input pattern="@+[a-z0-9.-_]{3,}" value="@" placeholder=" @username" name="user" required><br><br>
            <h3>How to Verify that it's you?</h3><hr>
            <input class="input_radio" type="radio" name="reset_option" value="use_otp" required> Send me OTP by Email<br>
            <input type="radio" name="reset_option" value="use_ques" required> Use Security question<br><br>
			<button class="btn-blue" type="submit" name="reset_user">Next</button>
		</form>
	</div>
<?php }

//-------------------------RESET-TAB2------------------------------

else if($_v=="reset_otp"){ ?>
    <div class="card">
        <h3>Enter OTP to verify</h3><hr><br>
        <form method="POST">
            Your OTP: <br>(OTP has been sent to your Email Id.)<br>
			<input placeholder=" 6-digit OTP" type="number" name="otp"><br><br>
			New Password: <br>
            <input pattern=".{4,}" placeholder=" Password" type="password" name="pass"><br><br>
            <button type="submit" class="btn-blue" name="reset_otp">Done</button>
            <button class="btn-red" name="reset_cancel">Cancel</button>
		</form>
	</div>
<?php }

//----------------------------RESET-TAB3---------------------------

else if($_v=="reset_ques"){ ?>
    <div class="card">
        <h3>Verify using Security Question</h3><hr><br>
		<form method="POST">
			Question: <?php echo $_SESSION['ques']; ?><br>
            <input name="ans" placeholder=" Answer"><br><br>
            New Password: <br>
            <input pattern=".{4,}" placeholder=" Password" type="password" name="pass"><br><br>
			<button class="btn-blue" type="submit" name="reset_ques">Done</button>	
            <button class="btn-red" name="reset_cancel">Cancel</button>
		</form>
    </div>
<?php }

//---------------------------LOGIN-TAB--------------------------

else{ 
    if(isset($_SESSION['logged']) && $_SESSION['logged'] == true){
        header("location:".$_page_client);
    }
    ?>
    <div class="card">
	    <h3>Login</h3><hr><br>
		<form method="POST">
            Username: <br>
            <input pattern="@+[a-z0-9.-_]{3,}" value="" placeholder=" @username" name="user" required><br><br>
            Password: <br>
            <input pattern=".{4,}" placeholder=" Password" type="password" name="pass" required><br><br>
			<button class="btn-blue" type="submit" name="login">Login</button>
			<button class="btn-red" type="reset" name="login">Clear</button>
		</form>
	</div>
<?php }

echo $_end;

?>
