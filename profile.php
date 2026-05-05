<?php


$conn = mysqli_connect("localhost","root","","rad_project");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_POST['email']) && isset($_POST['password'])){

    $useremail = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE useremail='$useremail'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        // Plain password check (only for learning, NOT secure)
        if($password == $row['password']){

            $_SESSION['useremail'] = $useremail;

            if(str_contains($useremail, "@teacher")){
                header("Location: teacherHomePage.php");
                exit();
            } 
            else if(str_contains($useremail, "@student")){
                header("Location: studentHomePage.php");
                exit();
            } 
            else{
                die("Invalid email role");
            }

        } else {
            die("Wrong password");
        }

    } else {
        die("User not found");
    }
}
?>