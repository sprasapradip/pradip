<?php
$conn = new mysqli("localhost","root","","portfolio");
$msg="";
if(isset($_POST['send'])){
 $stmt=$conn->prepare("INSERT INTO messages(name,email,message) VALUES(?,?,?)");
 $stmt->bind_param("sss",$_POST['name'],$_POST['email'],$_POST['message']);
 $msg=$stmt->execute()?"Message Sent":"Error";
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