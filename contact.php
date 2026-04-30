<?php include 'config.php'; ?>
<?php include 'header.php'; ?>
<?php
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
<head>
<link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
<a href="index.php">Home</a>
<a href="projects.php">Projects</a>
<a href="experience.php">Experience</a>
<a href="services.php">Services</a>
<a href="contact.php" class="active">Contact</a>
</nav>

<section class="page">

    <h1 class="page-title">Contact</h1>

    <p class="text-block">
        Feel free to reach out for electrical projects, technical consultation, or collaboration.
    </p>

    <!-- STATUS MESSAGE -->
    <?php if(!empty($msg)): ?>
        <p class="text-block" style="color:#38bdf8; font-weight:500;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST">

        <input name="name" placeholder="Full Name" required>

        <input type="email" name="email" placeholder="Email Address" required>

        <textarea name="message" placeholder="Write your message..." rows="5" required></textarea>

        <button class="btn" name="send">Send Message</button>

    </form>

</section>

<?php include 'footer.php'; ?>

</body>
</html>