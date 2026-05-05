<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            padding: 50px;
        }

        .form-container {
            background: orange;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .error {
            color: red;
            font-size: 0.9em;
            display: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h2>Login</h2>

 <form action="profile.php" method="POST">

<input type="text" name="email">
<input type="password" name="password">

<button type="submit">Login</button>
</form>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
    let isValid = true;

    let email = document.getElementById('email').value.trim();
    let password = document.getElementById('password').value.trim();

    // Reset errors
    document.querySelectorAll('.error').forEach(e => e.style.display = 'none');

    // Email check
    if (email === "" || !email.includes('@')) {
        document.getElementById('emailError').style.display = 'block';
        isValid = false;
    }

    // Password check
    if (password === "") {
        document.getElementById('passwordError').style.display = 'block';
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

</body>
</html>