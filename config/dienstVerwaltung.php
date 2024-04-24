<?php
require_once "dbConfig.php";
session_start();

$loggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$permission = isset($_SESSION["permissionLevel"]) && $_SESSION["permissionLevel"] > 0 ? $_SESSION["permissionLevel"] : 3;

if ($_SESSION['permissionLevel'] != 1) {
    header("Location: ../php/User/login.php");
    exit;
}

// Handle form submissions for sortierung and Dienste
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['sortierung1']) && !empty(trim($_POST['sortierung1']))) {
        file_put_contents("sortierung1.txt", $_POST['sortierung1']);
    }

    if (isset($_POST['sortierung2']) && !empty(trim($_POST['sortierung2']))) {
        file_put_contents("sortierung2.txt", $_POST['sortierung2']);
    }

    if (isset($_POST['sortierung3']) && !empty(trim($_POST['sortierung3']))) {
        file_put_contents("sortierung3.txt", $_POST['sortierung3']);
    }

    if (isset($_POST['Dienste'])) {
        $newDienste = ", " . $_POST['Dienste'];
        $file = isset($_POST['Dienste1']) ? "Dienste1.txt" : (isset($_POST['Dienste2']) ? "Dienste2.txt" : "Dienste3.txt");
        file_put_contents($file, $newDienste, FILE_APPEND);
    }
}

// Read content of Sortierung.txt
$sortierung1 = file_get_contents("sortierung1.txt");
$sortierung2 = file_get_contents("sortierung2.txt");
$sortierung3 = file_get_contents("sortierung3.txt");

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfiguration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Archivo" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../css/styles.css" >

    <link rel="icon" type="image/x-icon" href="../assets/USB_Identifier.svg">

</head>

<body>



<header class="container-fluid">
    <nav class="navbar">
        <ul class="nav nav-pills navbar-expand-lg ">
            <li class="nav-item">
                <a class="navbar-brand" aria-current="page" href="../index.php">
                    <img src="../assets/USB_Logo.png" id="logo" alt="USB Logo">
                </a>
            </li>
            <?php

            if($loggedIn && $permission == 1) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='../php/User/benutzerVerwaltung.php'>Benutzerverwaltung</a>
            </li>";
            }
            if($loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='../php/User/logout.php'>Logout</a>
            </li>";
            }
            if (!$loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active'  tabindex='-1' aria-disabled='true' href='../php/User/login.php'>Login</a>
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

<div class="container-fluid">

    <div class="container card">
        <h2>Konfiguration</h2>
        <h3>Sortierung</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h4>Grüne Zone</h4>
            <input type="text" id="sortierung1" name="sortierung1">
            <p>Standardwert: <b>551,1 ,5539,1.5, 560,5 ,5531,5.5, 504,6,5519,6.5, 501,7,5518,7.1, 2401,7.5, 5507, 7.8, 1029,8,5490,8.5, 515,9, 5501, 9.5, 518,10,  561, 11, 5532,11.5, 1030,12,5566,12.1,565, 14, 5535,14.5, 1031,15,5567,15.1,   566, 20, 5536,20.5,5779, 1032, 21,5568,21.1, 2416,21.5, 5546,40, 528, 40.5, 5564,40.6,562, 41, 5533,41.5, 540,43,5520,43.1, 576, 105 ,5545,105.5, 200, 2435</b></p>
            <button type="submit" name="sortierung1">Sortierung anpassen</button>
        </form> <br />
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h4>Dienste</h4>
            <input type="text" id="sortierung2" name="sortierung2"> <!--   TODO: alter to second table   -->
            <p>Standardwert: <b>570,1, 5538,1.5, 3058,1.6, 5489, 1.8, 855,2, 5540,2.5, 520,3,5503,3.2, 3059,3.5,5508,3.6, 1028,4, 5543,4.5, 527,5,5561,5.1, 3301,6, 858,7, 7047,7.5, 571,8, 5541,8.5, 507,9,5563,9.1, 7048,10, 516,11, 5505,11.5, 1057,12, 5504,12.5, 508,13, 5502,13.5, 514,14,5496,14.5, 513,15, 5492,15.1</b></p>
            <button type="submit" name="sortierung2">Sortierung anpassen</button>
        </form> <br />
        </form> <br />
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <h4>Weisse Zone</h4>
            <input type="text" id="Sortierung3" name="Sortierung3"> <!--   TODO: alter to second table   -->
            <p>Standardwert: <b>992,1, 5547,1.5, 2428,2, 283,3, 5491, 3.1, 5548,3.2</b></p>
            <button type="submit" name="sortierung3">Sortierung anpassen</button>
        </form> <br />


        <hr>
        <h2>Dienste hinzufügen</h2>
        <h4>Grüne Zone</h4>
        <form action="" method="post">
            <input type="text" id="Dienste" name="Dienste">
            <button type="submit" name="Dienste">Dienste hinzufügen</button>
        </form>
        <h4>Dienste</h4>
        <form action="" method="post">
            <input type="text" id="Dienste" name="Dienste">
            <button type="submit" name="Dienste">Dienste hinzufügen</button>
        </form>
        <h4>Weisse Zone</h4>
        <form action="" method="post">
            <input type="text" id="Dienste" name="Dienste">
            <button type="submit" name="Dienste">Dienste hinzufügen</button>
        </form> <br />
        <?php

        $sql = "SELECT D.TITEL FROM ECBERN.DIENST D WHERE D.PA_CODE IN (518,5779,551,560,504,5519,501,5518,1029,5490,515,5501,561,1030,5566,566,1032,5568,5779 ,565,1031,5567,528,5564,562,540,5520,5520,576,2401,5507, 2416,5539,5531,5532,5536,5535,5546,5533,5545,5537,2435 ) ORDER BY decode (D.PA_CODE, $sortierung1)";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
            oci_execute($stmt);
        }
        echo "<div style='display: flex; justify-content: space-between;'>";

        echo "<table class='table table-responsive-sm w-25 p-3' caption-top>\n";
        echo "<caption class='caption-top dienst-caption'> <h3>Grüne Zone </h3></caption>";
        echo "<tr>\n";
        echo "    <th>TITEL</th>\n";
        echo "</tr>\n";

        while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
            echo "<tr>\n";
            foreach ($row as $item) {
                echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";

        $sql = "SELECT D.TITEL FROM ECBERN.DIENST D WHERE D.PA_CODE IN (516,5505,507,5563,508,5502,571,279,514,5496,570,3301,3302,855,858,7047,1028,513,5492,527,5561,520,5503,1057,5504,2302,5562,506, 7048,888,5541,5537,5538,5540,5543,3058,5489, 3059,5508, 803, 1340 ) ORDER BY decode (D.PA_CODE, $sortierung1)";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
            oci_execute($stmt);
        }

        echo "<table class='table table-responsive-sm w-25 p-3'>\n";
        echo "<caption class='caption-top dienst-caption'> <h3>Dienste </h3></caption>";
        echo "<tr>\n";
        echo "    <th>TITEL</th>\n";
        echo "</tr>\n";

        while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
            echo "<tr>\n";
            foreach ($row as $item) {
                echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";

        $sql = "SELECT D.TITEL FROM ECBERN.DIENST D WHERE D.PA_CODE IN (856,567,505,283,5491,992,2608,5560,5548,5547) ORDER BY decode (D.PA_CODE, $sortierung1)";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
            oci_execute($stmt);
        }

        echo "<table class='table table-responsive-sm w-25 p-3'>\n";
        echo "<caption class='caption-top dienst-caption'> <h3>Weisse Zone </h3></caption>";
        echo "<tr>\n";
        echo "    <th>TITEL</th>\n";
        echo "</tr>\n";

        while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
            echo "<tr>\n";
            foreach ($row as $item) {
                echo "    <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>\n";
            }
            echo "</tr>\n";
        }
        echo "</table>\n";
        echo "</div>";

        ?>


    </div>
    <?php
    //    ini_set('display_errors', 1);
    //    ini_set('display_startup_errors', 1);
    //    error_reporting(E_ALL);
    //    // The path to the text file
    //    $file = 'Sortierung.txt';
    //
    //    // The old and new entries
    //    //get old Entry from file
    //    $oldEntry = file_get_contents($file);
    //    //get new Entry from form
    //    $newEntry = "";
    //    // Check if the form is submitted
    //    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Sortierung'])) {
    //    // The old and new entries
    //    //get old Entry from file
    //    $oldEntry = file_exists($file) && filesize($file) > 0 ? file_get_contents($file) : '';
    //
    //    //get new Entry from form
    //    $newEntry = $_POST['Sortierung'];
    //
    //    // Open the file
    //    $handle = fopen($file, 'r+');
    //
    //    // Check if the file is not empty
    //    if (filesize($file) > 0) {
    //        // Read the contents of the file
    //        $content = fread($handle, filesize($file));
    //
    //        // Replace the old entry with the new entry
    //        $content = str_replace($oldEntry, $newEntry, $content);
    //
    //        // Truncate the file to zero length
    //        ftruncate($handle, 0);
    //
    //        // Rewind the file pointer to the start of the file
    //        rewind($handle);
    //
    //        // Write the new content to the file
    //        fwrite($handle, $content);
    //    }
    //
    //    // Close the file
    //    fclose($handle);
    //    }
    ?>

</div>
</div>

</body>
</html>