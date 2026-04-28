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

$conn = new mysqli("localhost","root","","portfolio");

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$msg = "";

if(isset($_POST['send'])){

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO messages(name,email,message) VALUES(?,?,?)");
    $stmt->bind_param("sss", $name, $email, $message);
    $stmt->execute();

    // SMTP Email
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pradipsubedi9831@gmail.com'; // your email
        $mail->Password   = 'jgmtoakqnkbeuvtp';   // NOT normal password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & Receiver
        $mail->setFrom('pradipsubedi9831@gmail.com', 'Portfolio Contact');
        $mail->addAddress('pradipsubedi9831@gmail.com'); // where you receive

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message';
        $mail->Body    = "
            <h3>New Message</h3>
            <b>Name:</b> $name <br>
            <b>Email:</b> $email <br>
            <b>Message:</b><br>$message
        ";

        $mail->send();
        $msg = "Message Sent + Email Delivered";

    } catch (Exception $e) {
        $msg = "Saved but Email Failed: {$mail->ErrorInfo}";
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