<?php
$_dir="";
$_head="<title>User</title>";
require_once $_dir."res/content/main.php";
require_once $_dir."res/content/db_config.php";
$_page = "user.php";
$_page_2 = "index.php";
session_id("user");
session_start();
//-----------------------Page-Access-Rule-----------------------
if (!isset($_SESSION['logged']) && $_SESSION['logged'] != true) {
    header("location:".$_page_2);
}
//------------------User-Details-Fetch-----------------
$user = $_SESSION['user'];
$_SESSION['data'] = $data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM munish WHERE user = '$user' "));
//current-date
date_default_timezone_set("Asia/India");
$_date=date("d-m-Y");
$_time=date("h:i:s A");
//user-age
if($data['dob'] !=""){
    $age = $_date-$data['dob'];
}
//user-gender
if($data['gen']=="Male"){
    $setting_edit_gen['1']="selected";
}else if($data['gen']=="Female"){
    $setting_edit_gen['2']="selected";
}else if($data['gen']=="I am Shy"){
    $setting_edit_gen['3']="selected";
}else{
    $setting_edit_gen['0']="selected";  
}
//security-alert
if($data['setting_security']==false){
    $err2 .= "Your security question is not set. Please provide a security question for account security.<br><br><a href='".$_page."?v=setting>ques' class='btn btn-blue'>Set question</a><br><br>";
}
//email-alert
if($data['setting_email_verify']==false){
    $setting_email_status = "Not Verified";
    $err2 .="Your Email Id is not verified. <br>Some features may not work without verifying.<br>Please verify.<br><br><a href='".$_page."?v=setting>email' class='btn btn-blue'>Verify</a>";
}else{
    $setting_email_status = "Verified";
    $setting_email_otp_state="hide";
}
error_reporting();
$_theme=$data['setting_theme'];
if($_theme=="1"){?>
   <link rel="stylesheet" href="<?php echo $_dir;?>res/css/style_dark.css";
    <?php
}

//-----------------------Logout---------
if (array_key_exists('logout', $_POST)) {
    session_destroy();
    header("location:".$_page_2);
}

//---------------------SETTING->-CHANGE-PASSWORD-------------------------

if (array_key_exists('change_password', $_POST)) {
    $data = $_SESSION['data'];
    $user = $data['user'];
    $npass = $_POST['npass'];
    if($_POST['pass']==$data['pass']){
        $action = mysqli_query($con,"UPDATE munish SET pass='$npass' WHERE user='$user'");
        if($action){
            $err_type="err-grn";
            $err="Password has been changed.";
        }else{
            $err="Unable to change Password.<br>Error: update_password_failed";
        }    
    }else{
        $err="Current password you entered is Incorrect. Try again..";
    }
}

//----------------------------SETTING->-SECURITY-----------------------

if (array_key_exists('setting_security', $_POST)) {
    $ques = $_POST['ques'];
    $ans = $_POST['ans'];
    $user = $_SESSION['user'];
    $action = mysqli_query($con,"UPDATE munish SET ques='$ques', ans='$ans',setting_security='1' WHERE user='$user'");
    if($action){
        $err_type="err-grn";
        $err="Security Setting Updated. Refresh page to take effect<br><br><a href='".$_page."?v=setting>ques' class='btn btn-blue'>Refresh</a>";
    }else{
        $err="Unable to update security question.<br>Error: update_security_failed";
    }
}

//----------------------SETTING->-SECURITY->-SEND-OTP----------------

if(array_key_exists("send_otp",$_POST)){
    if($setting_email_status=="Verified"){
        $err="Email is already Verified!";
    }else{
        $email = $data['email'];
        $_SESSION['otp'] = $otp = rand(100000,999999);
        require_once 'res/content/mail.php';
        $mail->addAddress($email, '');
        $mail->setFrom("mk9569192204@gamil.com","Support - Munish Inc.");
        $mail->Subject = 'Your OTP';
        $mail->Body="<h1>Email Verification</h1><p>A request to verify Email Id was made by you. To continue, use the OTP below: </p><br>Your OTP is: <b>".$otp."</b><br><p>Please Don't share this with anyone.</p><h2>Munish Inc.</h2>Visit our website <a href='http://munish.rf.gd'>Click here</a>";
        if (!$mail->send()) {
            $err = "Unable to send Email.<br>Error: send_mail_failed.";
        }else{
            $err_type ="err-grn";
            $err = "An OTP has been sent to your Email address.";
        }
    }
}

//----------------------------SETTING->-SECURITY->-SUBMIT-OTP--------------

if(array_key_exists("submit_otp",$_POST)){
    $errs="err-red";
    if ($_POST['otp'] == $_SESSION['otp']) {
        $verify = mysqli_query($con, "UPDATE munish SET setting_email_verify=1 WHERE user = '$user'");
        if ($verify) {
            $err_type="err-grn";
            $err = "Email Id Verified Successfully.. Refresh page to take effect<br><br><a href='".$_page."?v=setting>email' class='btn btn-blue'>Refresh</a>";
        } else {
			$err = "Unable to Verify Email Id.<br>Error: update_setting-email-verify_failed.";
		}
        unset($_SESSION['otp']);
    } else {
        $err = "OTP you entered is Incorrect! Try Again.";
    }
}

//--------------------------------SETTING->-PROFILE-EDIT---------------

if(array_key_exists("setting_edit",$_POST)){
    $name=$_POST['name'];
    $gen=$_POST['gen'];
    $dob=$_POST['dob'];
    if(mysqli_query($con,"UPDATE munish SET name='$name',gen='$gen',dob='$dob' WHERE user='$user'")){
        $err_type ="err-grn";
        $err="Changes Saved.";
        $_SESSION['data'] = $data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM munish WHERE user = '$user' "));
    }else{
        $err="Unable to save changes!<br>Error: update_data_failed";
    }
}

//-------------------------SETTING->-TRANSFER---------------------

if(array_key_exists("send",$_POST)){
    if($data['setting_email_verify']==false){
        $err="Your Email is not verified. You cannot send mc. Please verify your Email in Setting.";
    }
    else{
    $user = $_SESSION['user'];
    $rec = $_POST['rec'];
    $amt = $_POST['amt'];
    $bal = $data['bal'];
    if($_POST['pass']==$data['pass']){
        if($rec == $user){
            $err="You cannot send to yourself.";
        }else{
            if($amt > $bal){
                $err="You Don't have enough mc. to send";
            }else{
                $file =fopen("admin/transactions","a");
                $data_rec = mysqli_fetch_assoc(mysqli_query($con,"SELECT bal FROM munish WHERE user='$rec'"));
                $bal_rec = $data_rec['bal'];
                fwrite($file,"\n<div><b>Transaction:</b><br> Date: ".$_date." | Time: ".$_time."<br> Amount: Mc.".$amt."<br>Sender: ".$user." (".$bal.")<br>Reciver: ".$rec." (".$bal_rec.")<br>");
                $bal_rec += $amt;
                $bal -= $amt;
                $history ="\n<div><b>Transaction:</b><br> Date: ".$_date." | Time: ".$_time."<br> Sent: Mc.".$amt."<br> To: ".$rec."<br> Remaining Balance: ".$bal."</div>";
                if(mysqli_query($con,"UPDATE munish SET bal='$bal',transfer_history=CONCAT(transfer_history,'$history') WHERE user='$user'") && mysqli_query($con,"UPDATE munish SET bal='$bal_rec' WHERE user='$rec'")){
                    $err_type ="err-grn";
                    $err="Mc. ".$amt." Sent to ".$rec."<br>Remaning Balance: ".$bal;
                    fwrite($file,"Sender left: ".$bal."<br>Reciver left: ".$bal_rec."</div>");   
                }else{
                    $err="Unable to send.<br>Error: update_bal_failed";
                    fwrite($file,"----------Failed----------</div>");   
                }
                fclose($file);
            }
        }
    }else{
        $err="Incorrect Password.";
    }
}
}

if(array_key_exists("setting_theme",$_POST)){
    $_theme=$_POST['theme'];
    if(mysqli_query($con,"UPDATE munish SET setting_theme='$_theme' WHERE user='$user'")){
        $err_type="err-grn";
        $err="Theme Changed.<br>Loading theme..<span class='loading'></span>";
        ?><meta http-equiv="refresh" content="2"><?php
    }else{
        $err="Unable to save Changes.";
    }
}

//----------------------------------MAIN--------------------------

?>

<div id='err' class='err <?php echo " ".$err_type." "; if($err == ""){echo "err-hide";} ?>'><div onclick="err('err')" class='close'>&times;</div>
    <?php echo $err;?>
</div>

<div class="bar">
    <a href="?v=setting">Setting</a>
    <a href="?v=transfer">Send MC</a>
    <a href="?v=setting>theme">Theme</a>
    <a class="right"><?php echo $_time2;?></a>
</div>
<?php

//-----------------------------SETTING-----------------------

if($_v=="setting"){ ?>
    <div id="setting" class="window">
        <div class="title">Setting</div>
        <a class="btn-red x" href="?">&times;</a>
        <h3>Setting</h3><hr>
        <a class="btn opt" href="?v=setting>account">My Account</a><br>
        <a class="btn opt" href="?v=setting>edit">Edit Details</a><br>
        <a class="btn opt" href="?v=setting>theme">Theme</a><br>
        <a class="btn opt" href="?v=setting>change_pass">Change Password</a><br>
        <a class="btn opt" href="?v=setting>ques">Security Question</a><br>
        <a class="btn opt" href="?v=setting>email">Email Id</a><br>
    </div>
<?php }

//--------------------SETTING>EDIT---------------------------

else if($_v=="setting>edit"){ ?>
    <div class="window">
        <div class="title">Edit Details</div>
        <a class="btn-red x" href="?v=setting">&times;</a>
            <h3>My Details</h3><hr>
            <form method="post">
                Username: <br><input value="<?php echo $data['user'];?>" disabled><br><br>
                Name: <br><input name="name" value="<?php echo $data['name'];?>"><br><br>
                Email Id: <br><input value="<?php echo $data['email'];?>" disabled><br><br>
                Gender:
                <select name="gen">
                    <option <?php echo $setting_edit_gen['0'];?> value=""></option>
                    <option <?php echo $setting_edit_gen['1'];?> value="Male">Male</option>
			        <option <?php echo $setting_edit_gen['2'];?> value="Female">Female</option>
			        <option <?php echo $setting_edit_gen['3'];?> value="You are Shy">I am Shy</option>
                </select><br><br>
                Date of Birth: <br><input type="date" value="<?php echo $data['dob'];?>" name="dob"><br><br>
                <button class="btn-blue" name="setting_edit">Save Changes</button>
            </form>
        </div>
<?php }

//--------------------------SETTING>CHANGE-PASS--------------------

else if($_v=="setting>change_pass"){ ?>
    <div class="window">
    <div class="title">Change Password</div>
        <a class="btn-red x" href="?v=setting">&times;</a>
            <div class="card">If you forgot your password, then click reset<br><br><a href="<?php echo $_page_2."?v=reset"; ?>"><button class="btn-blue">Reset</button></a></div>
            <h3>Change Password</h3><hr>
            <form method="post">
                Current Password: <br>
                <input pattern=".{4,}" placeholder="Current Password" type="password" name="pass" required><br><br>
                New Password: <br>(Contain atleast 4 character)<br>
                <input pattern=".{4,}" placeholder="New Password" type="password" name="npass" required><br><br>
                <button class="yellow" type="submit" name="change_password"> Change</button><br><br>
            </form>
        </div>
<?php }

//-------------------------------SETTING>QUES-------------------------

else if($_v=="setting>ques"){ ?>
<div class="window">
<div class="title">Security Question</div>
        <a class="btn-red x" href="?v=setting">&times;</a>
        <h3>Change Security question</h3><hr>
            <form method="POST">
                Security Question: (Select a question)<br>
                <select name="ques">
			        <option value="1">Your Favorite Pet.</option>
			        <option value="2">Your Favorite Teacher.</option>
			        <option value="3">Your Favorite City</option>
		        </select><br><br>
                Answer to Security Question: <br>
                <input required placeholder=" Security Answer" name="ans" required><br><br>
                <button class="btn-blue" type="submit" name="setting_security">Done</button>
            </form>
        </div>
<?php }

//----------------------SETTING>EMAIL

else if($_v=="setting>email"){ ?>
<div class="window">
<div class="title">Email Id</div>
<a class="btn-red x" href="?v=setting">&times;</a>
            <h3>Email ID</h3><hr>
            Your Email Id: <?php echo $data['email'];?><br><br><b>Status: <?php echo $setting_email_status;?></b>
            <form method="POST" class="<?php echo $setting_email_otp_state;?>">
                <br><hr>Send OTP to your Email:<br><br>
                <button class="btn-blue" type="submit" name="send_otp">Send OTP</button>
                <br><br><hr>Verify OTP: <br>
                <input placeholder="6-digit OTP" name="otp"><br><br>
                <button class="btn-blue" type="submit" name="submit_otp">Verify</button>
            </form>
        </div>
    </div>  
<?php }

//--------------------------SETTING>ACCOUNT----------------------

else if($_v=="setting>account"){ ?>
    <div class="window">
        <div class="title">Account</div>
        <a class="btn-red x" href="?v=setting">&times;</a>
        <h3>My Account</h3><hr>
        <b>Username: <?php echo $data['user'];?></b><br>
        Name: <?php echo $data['name']; ?><br>
        Email: <?php echo $data['email']; ?><br>
        Gender: <?php echo $data['gen']; ?><br>
        Age: <?php echo $age; ?><br>
        Date of Birth: <?php echo $data['dob']; ?><br>
    </div>
<?php }

//--------------------------TRANSFER-----------------------

else if($_v=="transfer"){ ?>
<div class="window">
        <div class="title">Send Mc v1.0</div>
        <a class="btn-red x" href="?">&times;</a>
        <h3>Send mc</h3><hr>
        <form method="post">
            Username of Reciver:<br>
            <input type="text" name="rec" required><br><br>
            Amount:<br>
            <input type="number" name="amt" required><br><br>
            Your Password:<br>
            <input type="password" name="pass" required><br><br>
            <button class="btn-blue" type="submit" name="send">Send</button>
        </form>
        <a href="?v=transfer_history" class=" btn btn-ylo">View History</a>
    </div>
<?php }


else if($_v=="transfer_history"){ ?>
    <div class="window">
        <div class="title">History</div>
        <a class="btn-red x" href="?v=transfer">&times;</a>
        <h3>History</h3><hr>
        <div class="history"><?php echo $data['transfer_history'];?></div>
    </div>  
<?php }

else if($_v=="setting>theme"){ ?>
    <div class="window">
        <div class="title">Theme</div>
        <a class="btn-red x" href="?v=setting">&times;</a>
        <h3>Window Theme</h3><hr>
            <form method="POST">
                Select Theme: <br>
                <select name="theme">
			        <option value="0">Light</option>
			        <option value="1">Dark</option>
		        </select><br><br>
                <button class="btn-blue" type="submit" name="setting_theme">Change</button>
            </form>
        </div>
<?php }
//-------------------------MAIN-------------------------

else{ ?>
<div id='err2' class='err <?php echo " ".$err_type." "; if($err2 == ""){echo "err-hide";} ?>'><div onclick="err('err2')" class='close'>&times;</div>
    <?php echo $err2;?>
</div>
<div class="card">
    <h2>Hi, <?php echo $data['name'];?></h2><hr>
		<form method="POST">
            <button class="btn-red"  name="logout">Log Out</button>
        </form>
    <h3>Balance: Mc. <?php echo $data['bal'];?></h3>
</div>
<?php }
$_end;
?>