<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "rad_project");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data safely
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "Please fill all fields";
        exit();
    }

    // Query database
    $sql = "SELECT * FROM users WHERE useremail = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Check password (plain text for now)
        if ($password == $row['password']) {

            $_SESSION['email'] = $email;

            // 🔥 Redirect based on email
            if (strpos($email, '@student') !== false) {
                header("Location: student_home.php");
                exit();
            } 
            elseif (strpos($email, '@teacher') !== false) {
                header("Location: teacher_home.php");
                exit();
            } 
            else {
                echo "Invalid email type";
            }

        } else {
            echo "Wrong password";
        }

    } else {
        echo "User not found";
    }
}
?>