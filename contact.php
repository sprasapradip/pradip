// ================= contact.php =================
<?php
$conn = new mysqli("localhost","root","","portfolio");
$msg="";
if(isset($_POST['send'])){
 $stmt=$conn->prepare("INSERT INTO messages(name,email,message) VALUES(?,?,?)");
 $stmt->bind_param("sss",$_POST['name'],$_POST['email'],$_POST['message']);
 $msg=$stmt->execute()?"Sent":"Error";
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Contact</h2>
<p><?php echo $msg; ?></p>
<form method="POST">
<input name="name" placeholder="Name"><br>
<input name="email" placeholder="Email"><br>
<textarea name="message"></textarea><br>
<button name="send">Send</button>
</form>
</body>
</html>
