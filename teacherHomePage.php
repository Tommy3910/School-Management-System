<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}
?>

<h1>Welcome Teacher</h1>
<p>You are logged in as: <?php echo $_SESSION['useremail']; ?></p>