<?php


if (!isset($_SESSION['useremail'])) {
    header("Location: login.php");
    exit();
}
?>

<h1>Welcome Student</h1>
<p>You are logged in as: <?php echo $_SESSION['useremail']; ?></p>