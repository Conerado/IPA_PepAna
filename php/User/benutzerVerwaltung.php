<?php
session_start();
global $mySqlConn;
require_once "../../config/dbConfig.php";
// Check if user is logged in
$loggedIn = false;
$permission = 0;
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $loggedIn = true;


// Check user's permission level
}   if(isset($_SESSION["permissionLevel"]) > 0) {
    $permission = $_SESSION["permissionLevel"];
}
else {
    $loggedIn = false;
    $_SESSION["loggedin"] = false;
}


// Check if the user is logged in and has permission level 1
if ($_SESSION['permissionLevel'] != 1) {
    header("Location: login.php");
    exit;
} else

// If delete or permission level change is requested by the user then process the request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['deleteUser']) && isset($_POST['userID'])) {
            $userID = $_POST['userID'];
            // Delete user
            $sql = "DELETE FROM users WHERE id = $userID";
            if ($mySqlConn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Benutzer wurde gelöscht";
            } else {
                $_SESSION['error_message'] = "Der Benutzer konnte nicht gelöscht werden " . $mySqlConn->error;
            }
        } elseif (isset($_POST['changePermission']) && isset($_POST['userID']) && isset($_POST['permissionLevel'])) {
            $userID = $_POST['userID'];
            $permissionLevel = $_POST['permissionLevel'];
            // Update user permission level
            $sql = "UPDATE users SET permissionLevel = $permissionLevel WHERE id = $userID";
            if ($mySqlConn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Rechte wurden angepasst";
            } else {
                $_SESSION['error_message'] = "Ein Fehler ist aufgetaucht bei dem Versuch die Rechte anzupassen: " . $mySqlConn->error;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Benutzerverwaltung</title>

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
            <?php
            if($loggedIn && $permission == 1) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='../../config/dienstVerwaltung.php'>Konfiguration</a>
            </li>";
            }

            if($loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='logout.php'>Logout</a>
            </li>";
            }
            if (!$loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active'  tabindex='-1' aria-disabled='true' href='Login.php'>Login</a>
            </li>";
            }
            ?>
            <span class="navbar-text">
                <?php
                if(isset($_SESSION['username']))
                {
                    echo "Hallo ".$_SESSION['username'];
                }
                ?>
            </span>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Benutzerverwaltung</h2>
                    <?php
                    // Display all users
                    //Display permission levels as Role names
                    $roles = array(1 => 'Admin', 2 => 'Tageseinteilung', 3 => 'Keine Rechte');

                    $sql = "SELECT id, username, permissionLevel FROM users ORDER BY permissionLevel ASC, username ASC";
                    $result = $mySqlConn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<table border='1' class='table'><tr><th>Benutzer</th><th>Rechte</th></tr>";
                        while ($row = $result->fetch_assoc()) {
                            $roleName = isset($roles[$row["permissionLevel"]]) ? $roles[$row["permissionLevel"]] : 'Unknown';
                            echo "<tr><td>".$row["username"]."</td><td>".$roleName."</td>";
                            echo "<td>";
                            echo "<form method='post'>";
                            echo "<input type='hidden' name='userID' value='".$row["id"]."'>";
                            echo "<select name='permissionLevel'>";
                            foreach ($roles as $value => $role) {
                                echo "<option value='$value'>$role</option>";
                            }
                            echo "</select>";
                            echo "<button type='submit' name='changePermission'>Rechte anpassen</button>";
                            echo "<button type='submit' name='deleteUser'>Löschen</button>";
                            echo "</form>";
                            echo "</td></tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "0 results";
                    }
                    ?>
                    <?php if(isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>


                </div>
            </div>
        </div>
    </div>
</div>



</body>
</html>
