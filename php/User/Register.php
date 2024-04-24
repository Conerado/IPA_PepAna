<?php
session_start();
global $mySqlConn;
require_once "../../config/dbConfig.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Check if the username already exists
    $stmt = $mySqlConn->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Dieser Benutzername existiert bereits";
        header("Location: Register.php");
        exit;
    }
// Password validation
    if (strlen($password) < 8) {
        $_SESSION['error_message'] = "Das Passwort muss mindestens 8 Zeichen enthalten";
        header("Location: Register.php");
        exit;
    }
    if (!preg_match('/\d/', $password)) {
        $_SESSION['error_message'] = "Das Passwort muss mindestens eine Zahl enthalten";
        header("Location: Register.php");
        exit;
    }

// Hashing the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL statement
    $stmt = $mySqlConn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    $stmt->bind_param("ss", $username, $hashed_password);

// Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully";
        header("Location: login.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrierung</title>

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
                    <h2 class="card-title">Register</h2>
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Benutzername:</label>
                            <input type="text" id="username" class="form-control" name="username" required><br><br>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort:</label>
                            <input type="password" id="password" class="form-control" name="password" required><br><br>
                        </div>
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>



                        <?php endif; ?>
                        <p">Das Passwort muss folgenden Richtlinien entsprechen:</p>
                        <ul>
                            <li>Mindestens 8 Zeichen lang sein</li>
                            <li>Mindestens eine Zahl enthalten</li>
                        </ul>
                        <button type="submit" class="btn btn-primary" value="Register">Registrieren</button>
                    </form>
                    <p class="mt-3">Haben Sie schon ein Konto? <a href="login.php" class="link-warning">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>



</body>
</html>
