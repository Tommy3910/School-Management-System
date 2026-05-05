<?php 

$host="localhost";
$dbUsername="root";
$dbPassword="";
$dbName="rad_project";

$conn = mysqli_connect($host,$dbUsername,$dbPassword,$dbName);

if(!$conn){
    die("Connection failed: ".mysqli_conenct_error());
}

if(isset($_POST['loginButton'])){

$useremail=mysqli_real_escape_string($conn,$_POST['Email']);
$password=mysqli_real_ecsape_string($conn,$_POST['password']);
}
mysqli_close($conn);
?>