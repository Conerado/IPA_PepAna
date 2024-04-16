<?php
session_start();
global $mySqlConn;
require_once "../../config/dbConfig.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if the username exists
    $stmt = $mySqlConn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username exists, proceed with password update
        // Check if the new password and confirm password match
        if ($password === $confirm_password) {
            // Check if the password meets the criteria
            if (strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password)) {
                // Hash the new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Prepare the SQL statement
                $stmt = $mySqlConn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $stmt->bind_param("ss", $hashed_password, $username);

                // Execute the statement
                if ($stmt->execute()) {
                    echo "Password updated successfully";
                    header("Location: login.php");
                } else {
                    echo "Error updating password: " . $stmt->error;
                }
            } else {
                $error_message = "Password must be at least 8 characters long and contain at least one letter.";
            }
        } else {
            $error_message = "Passwords do not match";
        }
    } else {
        // Username does not exist
        $error_message = "Username does not exist";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passwort reset</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Archivo" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../../css/styles.css">

    <link rel="icon" type="image/x-icon" href="../../assets/USB_Identifier.svg">

    <style>
        body {
            font-family: "Archivo", sans-serif;
        }
    </style>

</head>

<body>

<header class="container-fluid">
    <nav class="navbar">
        <ul class="nav nav-pills navbar-expand-lg ">
            <li class="nav-item">
                <a class="navbar-brand" aria-current="page" href="../../index.php">
                    <img src="../../assets/USB_Logo.png" id="logo" alt="USB Logo">
                </a>
            </li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Password Reset</h2>
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="passwordReset.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Neues Passwort:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Passwort bestätigen:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <p">Das Passwort muss folgenden Richtlinien entsprechen:</p>
                        <ul>
                            <li>Mindestens 8 Zeichen lang sein</li>
                            <li>Mindestens eine Zahl enthalten</li>
                        </ul>
                        <button type="submit" class="btn btn-primary">Reset Password</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>





</body>
</html>
