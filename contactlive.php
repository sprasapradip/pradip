<?php
$conn = new mysqli("localhost","root","","portfolio");
$msg="";
if(isset($_POST['send'])){
 $stmt=$conn->prepare("INSERT INTO messages(name,email,message) VALUES(?,?,?)");
 $stmt->bind_param("sss",$_POST['name'],$_POST['email'],$_POST['message']);
 $msg=$stmt->execute()?"Message Sent":"Error";
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$msg = "";

// ==========================
// SMTP CONFIG
// ==========================
function configureSMTP($mail){
    $mail->isSMTP();
    $mail->Host       = 'mail.pradipsubedi1.com.np';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mail@pradipsubedi1.com.np';
    $mail->Password   = '53146083@Pradip';
    $mail->SMTPSecure = 'ssl'; // try 'tls' if needed
    $mail->Port       = 465;

    // Recommended
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    // Debug (0 = off, 2 = verbose)
    $mail->SMTPDebug  = 0;
}

// ==========================
// FORM PROCESS
// ==========================
if(isset($_POST['send'])){

    // Validate & sanitize
    $name    = htmlspecialchars(trim($_POST['name']));
    $email   = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if(!$email){
        $msg = "Invalid email address";
    } else {

        // ==========================
        // 1. ADMIN EMAIL
        // ==========================
        $adminSent = false;

        try {
            $mail = new PHPMailer(true);
            configureSMTP($mail);

            $mail->setFrom('mail@pradipsubedi1.com.np', 'Pradip Subedi Portfolio');
            $mail->addAddress('mail@pradipsubedi1.com.np');

            // reply goes to user
            $mail->addReplyTo($email, $name);

            $mail->isHTML(true);
            $mail->Subject = "New Contact Message";

            $mail->Body = "
                <h2>New Inquiry</h2>
                <table style='border-collapse:collapse'>
                    <tr><td><b>Name:</b></td><td>$name</td></tr>
                    <tr><td><b>Email:</b></td><td>$email</td></tr>
                </table>
                <p><b>Message:</b><br>$message</p>
                <hr>
                <small>From website contact form</small>
            ";

            $mail->send();
            $adminSent = true;

        } catch (Exception $e) {
            $msg = "Admin email failed: " . $mail->ErrorInfo;
        }

        // ==========================
        // 2. AUTO REPLY
        // ==========================
        $userSent = false;

        if($adminSent){
            try {
                $mail2 = new PHPMailer(true);
                configureSMTP($mail2);

                $mail2->setFrom('mail@pradipsubedi1.com.np', 'Pradip Subedi');
                $mail2->addAddress($email, $name);

                $mail2->isHTML(true);
                $mail2->Subject = "We received your message";

                $mail2->Body = "
                    <p>Dear $name,</p>

                    <p>Thank you for contacting me. Your message has been received.</p>

                    <p>I will get back to you shortly.</p>

                    <hr>

                    <p><b>Your Message:</b></p>
                    <blockquote>$message</blockquote>

                    <br>

                    <p>
                    Regards,<br>
                    <b>Pradip Subedi</b><br>
                    Electrical & Electronics Engineer<br>
                    <a href='https://pradipsubedi1.com.np'>pradipsubedi1.com.np</a>
                    </p>
                ";

                $mail2->send();
                $userSent = true;

            } catch (Exception $e) {
                $msg = "Auto-reply failed: " . $mail2->ErrorInfo;
            }
        }

        // ==========================
        // FINAL STATUS
        // ==========================
        if($adminSent && $userSent){
            $msg = "Message sent successfully";
        } elseif($adminSent){
            $msg = "Message saved, but auto-reply failed";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
<nav>
<a href="index.php">Home</a>
<a href="projects.php">Projects</a>
<a href="experience.php">Experience</a>
<a href="services.php">Services</a>
<a href="contact.php">Contact</a>
</nav>
<section>
<h2>Contact</h2>
<p><?php echo $msg; ?></p>
<form method="POST">
<input name="name" placeholder="Name" required>
<input name="email" placeholder="Email" required>
<textarea name="message" placeholder="Message" required></textarea>
<button class="btn" name="send">Send</button>
</form>
</section>
</body>
</html>