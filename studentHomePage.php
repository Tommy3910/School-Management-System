<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit(); 
}
?>

<h1>Welcome Student</h1>
<p>You are logged in as: <?php echo $_SESSION['useremail']; ?></p>