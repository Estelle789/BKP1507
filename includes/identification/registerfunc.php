<?php
include '../dbh-inic.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $type = $_POST["type"];
    $password = $_POST["password"];

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($phone) || empty($phone) || empty($password) || empty($type)) {
        header("Location: ../../index.php?please_fill_every_section");
        exit();
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../../index.php?invalidEmail");
        exit();
    } else if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) {
        header("Location: ../../index.php?info=invalid_characters");
        exit();
    } else {
        $sql = "SELECT id from  users where email = '$email' and verified = '1'";
        $existUser = $pdo->prepare($sql);
        $existUser->execute();
        $resultCheck = $existUser->rowCount();
        if ($resultCheck > 0) {
            header("Location: ../../index.php?info=exists");
            exit();
        } else {
            $query = "SELECT id FROM users WHERE email = ? and verified = '0'";
            $stmt = $pdo->prepare( $query );
            $stmt->bindParam(1, $email);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num>0){
 
                // you have to create a resend verification script
                echo "<div>Your email is already in the system but not yet verified.</div>";
            }
            else{
                  // now, compose the content of the verification email, it will be sent to the email provided during sign up
                // generate verification code, acts as the "key"
                $verificationCode = md5(uniqid("yourrandomstringyouwanttoaddhere", true));
 
                // send the email verification
                $verificationLink = "https://bookingpetz.com/fatih/bookingpetz/includes/activation/activate.php?code=" . $verificationCode;
 
                $htmlStr = "";
                $htmlStr .= "Hi " .$first_name .$last_name. ",<br /><br />";
 
                $htmlStr .= "Please click the button below to verify your account .<br /><br /><br />";
                $htmlStr .= "<a href='{$verificationLink}' target='_blank' style='padding:1em; font-weight:bold; background-color:blue; color:#fff;'>VERIFY EMAIL</a><br /><br /><br />";
 
                $htmlStr .= "Kind regards,<br />";
                $htmlStr .= "<a href='https://bookingpetz.com/' target='_blank'>The Bookingpetz.com team</a><br />";
 
 
                $name = "bookingpetz.com";
                $email_sender = "recruitment@bookingpetz.com";
                $subject = "Verification Link | bookingpetz.com | Account Activation";
                $recipient_email = $email;
 
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
                $headers .= "From: {$name} <{$email_sender}> \n";
 
                $body = $htmlStr;
 
                // send email using the mail function, you can also use php mailer library if you want
                if( mail($recipient_email, $subject, $body, $headers) ){
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $sql1 = "INSERT INTO users(first_name,
                    last_name,
                    username,
                    email,
                    phone,
                    password,
                    type,
                    verified,
                    verification_code) 
                    values(
                        '$first_name',
                        '$last_name',
                        '$username',
                        '$email',
                        '$phone',
                        '$hashedPassword',
                        '$type',
                        '0',
                        '$verificationCode')";
                    $resultInsert = $pdo->prepare($sql1);
                   
                    if( $resultInsert->execute()){
                        header("Location: ../../index.php?info=register");
                        exit();
                    }else{
                        die("Sending failed.");  
                    }
                   
            }
           
        }
    }

    }
}
