<?php
session_start();
global $mySqlConn;


//include dbConfig.php
require_once "../../config/dbConfig.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare the SQL statement
    $stmt = $mySqlConn->prepare("SELECT password, permissionLevel FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row["password"];

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            echo "Login successful";

            $_SESSION['username'] = $username;
            $_SESSION['permissionLevel'] = $row['permissionLevel'];
            $_SESSION['loggedin'] = true;

            header("Location: ../../index.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Incorrect username or password";
        }
    } else {
        $_SESSION['error_message'] = "Incorrect username or password";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
                    <h2 class="card-title">Login</h2>
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                    <p class="mt-3">Noch kein Konto erstellt? <a href="Register.php" class="link-warning">Registrieren Sie sich hier</a></p> <br />
                    <p class="mt-3">Passwort vergessen? <a href="passwordReset.php" class="link-warning">Klicken Sie hier</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
