<?php
session_start();

global $mySqlConn, $tempSortierung;

// Include the database configuration file
require_once "config/dbConfig.php";

//get Sorting from text files
$file1 = "config/sortierung1.txt";
$sortierung1 = file_get_contents($file1);

$file2 = "config/sortierung2.txt";
$sortierung2 = file_get_contents($file2);

$file3 = "config/sortierung3.txt";
$sortierung3 = file_get_contents($file3);

// Set default permission level and start session
$permission = 3;

$loggedIn = false;
$information = "";

// Store the selected date in a session variable if submitted
if (isset($_POST['date'])) {
// Store the selected date in a session variable
    $_SESSION['selected_date'] = $_POST['date'];
}
// Check if user is logged in
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



// Disable textarea if user's permission level is not 1 or 2
$writing = "disabled";
if ($permission <= 2) {
    $writing = "";
}

//get date form form or use current date
$datum = $_SESSION['selected_date'] ?? date('Y-m-d', strtotime('+1 day'));

// Get information from form and insert into database if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Tagesinformation']) && isset($_POST['submit'])) {
    $information = $_POST['Tagesinformation'];
    $stmt = $mySqlConn->prepare("INSERT INTO dailyInformation (date, Information) VALUES (?, ?) ON DUPLICATE KEY UPDATE Information = ?");
    $stmt->bind_param("sss", $datum, $information, $information);

    if ($stmt->execute()) {

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css" >

    <link rel="icon" type="image/x-icon" href="assets/USB_Identifier.svg">
</head>

<body ondragstart="return false">
<!--      Nav-bar      -->
<header class="container-fluid">
    <nav class="navbar">
        <ul class="nav nav-pills navbar-expand-lg ">
            <li class="nav-item">
                <a class="navbar-brand" aria-current="page" href="index.php">
                    <img src="assets/USB_Logo.png" id="logo" alt="USB Logo">
                </a>
            </li>
            <?php
            if($loggedIn && $permission == 1) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='config/dienstVerwaltung.php'>Konfiguration</a>
            </li>";
            }

            if($loggedIn && $permission == 1) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='php/User/benutzerVerwaltung.php'>Benutzerverwaltung</a>
            </li>";
            }
            if($loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active' href='php/User/logout.php'>Logout</a>
            </li>";
            }
            if (!$loggedIn) {
                echo "<li class='nav-item'>
                <a class='nav-link active'  tabindex='-1' aria-disabled='true' href='php/User/Login.php'>Login</a>
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
<!--   Main Content   -->

<div class="container-fluid">
    <div class="container card mb-3">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="Tagesinformation" class="form-label">Tagesinformation </label>
            <textarea <?php echo $writing ?>  name="Tagesinformation" id="Tagesinformation" rows="5" cols="50" class="form-control">
    <?php

    //return Daily Information from mysql Database
    $cSQL = "SELECT * FROM dailyInformation WHERE date = '".$datum."'";
    if (isset($mySqlConn)) {
        $rs=$mySqlConn->query($cSQL);
    }
    if ($rs->num_rows > 0) {
        while($row = $rs->fetch_assoc()) {
            echo $row["Information"];
        }
    } else {
        echo "Keine Tagesinformationen vorhanden";
    }
    ?>
</textarea>
            <?php
            // Display save button if user has writing permission
            if ($writing !== "disabled") {
                echo "<button type=\"submit\" name=\"submit\" class=\"btn btn-primary\">Speichern</button>";
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
                    // Set a session variable to indicate success
                    $_SESSION['success'] = true;
                }
            }
            ?>
        </form>

        <?php
        // Check if the success session variable is set
        if (isset($_SESSION['success'])) {
            // Display the success message
            echo '<div class="alert alert-success" role="alert">Der Eintrag wurde gespeichert!</div>';
            // Unset the session variable
            unset($_SESSION['success']);
        }
        ?>
        </form>


    </div>
    <div class="d-flex flex-row-reverse">
        <div class="p2">
            <table class="container float-right">
                <tr>
                    <td><img src="assets/L.png" alt="Nur Vormittag" width="50" height="50"></td>
                    <td style="color: black">Nur Vormittag</td>
                </tr>
                <tr>
                    <td><img src="assets/R.png" alt="Nur Nachmittag" width="50" height="50"></td>
                    <td style="color: black">Nur Nachmittag</td>
                </tr>
            </table>
        </div>
    </div>

    <h2>Übersicht</h2>
    <!-- Date Selector -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" onsubmit="return false;">
        <?php
            echo "<label for='date'> Hier ist die Datumauswahl:</label>";
        ?>
        <input type="date" id="date" name="date" value="<?php echo $datum; ?>" "
        onchange="this.form.submit()">
    </form>
    <div class="row">


        <?php

        $sql = "SELECT MA.NACHNAME, MA.VORNAME, MA.KUERZEL, D.TITEL, MA.TEL_INTERN, MA.TEL_PRIVAT, MA.TEL_SONST, MA.PAGER, PL.DIENSTPOSITION, D.PA_CODE, PL.KNOTEN_ID, FnGLAZSaldo(MA.ID, TO_DATE(:datum, 'YYYY-MM-DD')) AS SALDO
        FROM ECBERN.DIENST D, ECBERN.PLANUNG PL, ECBERN.MITARBEITER MA
        WHERE (MA.ID = PL.MITARBEITER_ID) AND D.PA_CODE = PL.PA_CODE AND PL.KNOTEN_ID in (1527,28303) AND (PL.DATUM = TO_DATE(:datum, 'YYYY-MM-DD')) AND D.PA_CODE NOT IN (568,569,10,1,568,709,702,31,40, 857,70,71,577,525,53,54,5,20,706, 25, 516,5505,507,5563,508,5502,279,514,5496,570,3302,855,1028,513,5492,527,5561,571,575,3301,192,281, 97,856,567,505,283,5491,992,506, 7048,520,5503,509,5506, 1057,5504,2302,5562,5541,5537,5538,5540,5543,5544,5560,5548,5547 ) AND  D.PA_CODE IN (518,5779,551,560,504,5519,501,5518,1029,5490,515,5501,561,1030,5566,566,1032,5568,5779 ,565,1031,5567,528,5564,562,540,5520,5520,576,2401,5507, 2416,5539,5531,5532,5536,5535,5546,5533,5545,5537,2435 )
        ORDER BY decode (D.PA_CODE, $sortierung1),PL.KNOTEN_ID";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
        }
        oci_bind_by_name($stmt, ':datum', $datum);

        // Execute the query
        /**
         * @param array $row
         * @return string
         */
        function getStr(array $row): string
        {
            if (strpos($row["TITEL"], "OA") !== false || strpos($row["TITEL"], "Tagdienst_Koord") !== false ){
                echo "<tr class='table-secondary OA' >";
            } else {
                echo "<tr class='text-end'>";
            }
            if ($row["DIENSTPOSITION"] == "L") {
                echo "<td><img src='assets/L.png' alt='Frühdienst' width='50' height='50'></td>";
            } else
                if ($row["DIENSTPOSITION"] == "R") {
                    echo "<td><img src='assets/R.png' alt='Frühdienst' width='50' height='50'></td>";
                } else {
                    echo "<td> </td>";
                }
            $contactInfo = "";
            if (!$row["TEL_INTERN"] && !$row["TEL_PRIVAT"] && !$row["TEL_SONST"] && !$row["PAGER"]) {
                $contactInfo .= " | keine Telefonnummer hinterlegt";
            }


//            if (!$row["TEL_INTERN"] == null) {
//                $contactInfo .= " |I " . $row['TEL_INTERN'];
//            }
//            if (!$row["TEL_PRIVAT"] == null) {
//                $contactInfo .= " |P " . $row['TEL_PRIVAT'];
//            }
//            if (!$row["TEL_SONST"] == null) {
//                $contactInfo .= " |S " . $row['TEL_SONST'];
//            }
//            if (!$row["PAGER"] == null) {
//                $contactInfo .= " |P " . $row['PAGER'];
//            }

// Blurring Phone number for privacy -> Remove before PROD (use code above)
            if (!$row["TEL_INTERN"] == null) {
                $blurredNumber = substr($row['TEL_INTERN'], 0, 5) . str_repeat('*', strlen($row['TEL_INTERN']) - 5);
                $contactInfo .= " |I " . $blurredNumber;
            }
            if (!$row["TEL_PRIVAT"] == null) {
                $blurredNumber = substr($row['TEL_PRIVAT'], 0, 5) . str_repeat('*', strlen($row['TEL_PRIVAT']) - 5);
                $contactInfo .= " |P " . $blurredNumber;
            }
            if (!$row["TEL_SONST"] == null) {
                $blurredNumber = substr($row['TEL_SONST'], 0, 5) . str_repeat('*', strlen($row['TEL_SONST']) - 5);
                $contactInfo .= " |S " . $blurredNumber;
            }
            if (!$row["PAGER"] == null) {
                $blurredNumber = substr($row['PAGER'], 0, 5) . str_repeat('*', strlen($row['PAGER']) - 5);
                $contactInfo .= " |P " . $blurredNumber;
            }

//Hover Function
            echo "
<td>
<div class='CustomHover'>
<h3>
" . $row['KUERZEL'] . " | " . $row['VORNAME'] . " " . $row['NACHNAME'] . "
</h3>

 
<div class='focus-content'>
" . $contactInfo . "
</div>
</div>
</td>";
            echo "<td>";
            return $contactInfo;
        }

        if (oci_execute($stmt)) {
            echo "
<div class='row'>
<div id='tbl-flex'>

    <div class='col-lg-4 col-md-6'>
        <div class='table-container'>
<table class='table caption-top'>
<caption><h2>Grüne Zone</h2></caption>
    <thead class='thead-dark'>";
            echo "<tr><th scope='col'>Zeit</th><th scope='col'>Name</th><th scope='col'>Station/Dienst</th>
";
//check if user is loggedIn, then display Overtime
            if ($_SESSION["loggedin"] && $permission < 3) {
                echo "<th>Überstunden</th>";
            }
// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {

// Convert PEP-title to predefined names
                $contactInfo = getStr($row);
                switch ($row['TITEL']) {
                    case "Tagdienst_Koord":
                        echo "<h5>CHEF OST</h5>";
                        break;
                    case "L = HNO":
                        echo "HNO";
                        break;
                    case "C = Ebene 01":
//Had to take string apart, since the full string wasn't recognized. Might be due to the underscore (ChirAllgThrx_aa)
                    case (strpos($row['TITEL'], "ChirAllg") !== false);
                        echo "OP-Ost 01";
                        break;
                    case "Weisse Zone":
                    case '"Line-Dienst"':
                        echo "Vf[V]"; //Temp
                        break;
                    case "C_ChirAllg Thrx aa":
                        echo "Thorax";
                        break;
                    case "H_HerzGefass_AA":
                        echo "Herz/Gefäss";
                        break;
                    case "W = OP West":
                    case "West_Ortho_OA":
                        echo "OP-West";
                        break;
                    case "Chef West OA":
                        echo "<h5>CHEF WEST</h5>";
                        break;
                    case "T_TraumaNF_AA":
                        echo "Ortho/Trauma";
                        break;
                    case "NeuroKopf_OA":
                    case "N_NeuroKopf_AA":
                        echo "Neuro";
                        break;
                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";
                if ($_SESSION["loggedin"] && $permission < 3) {

                    echo "<td class='text-end'>";
                    $hours = floor((float)($row['SALDO'] / 60 / 60));
                    $minutes = (int)($row['SALDO'] / 60) % 60;
                    $minutes = abs($minutes);
// Pad the minutes with leading zeros - looks better
                    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
                    $result = $hours . ":" . $minutes . " ";
                    if ($row['SALDO'] > 0) {
                        echo "<p style='color: green'>" . $result;
                    } else {
                        echo "<p style='color: red'>" . $result;
                    }
                    echo "</td>";
                }


                echo "</tr>";
            }
            echo "</table>
</div>

</div>

";
        } else {
            $e = oci_error($stmt);
        }


        $datum = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : date('Y-m-d');
        $sql = "SELECT DISTINCT MA.NACHNAME, MA.VORNAME, MA.KUERZEL, D.TITEL, MA.TEL_INTERN, MA.TEL_PRIVAT, MA.TEL_SONST, MA.PAGER, PL.DIENSTPOSITION, D.PA_CODE, PL.KNOTEN_ID, FnGLAZSaldo(MA.ID, TO_DATE(:datum, 'YYYY-MM-DD')) AS SALDO
		FROM ECBERN.DIENST D, ECBERN.PLANUNG PL, ECBERN.MITARBEITER MA
		WHERE (MA.ID = PL.MITARBEITER_ID) AND  D.PA_CODE = PL.PA_CODE AND PL.KNOTEN_ID in (1527,28303) AND (PL.DATUM = TO_DATE(:datum, 'YYYY-MM-DD')) AND D.PA_CODE IN (516,5505,507,5563,508,5502,571,279,514,5496,570,3301,3302,855,858,7047,1028,513,5492,527,5561,520,5503,1057,5504,2302,5562,506, 7048,888,5541,5537,5538,5540,5543,3058,5489, 3059,5508, 803, 1340) AND D.TITEL<>'HR' 
		ORDER BY decode (D.PA_CODE, $sortierung2), PL.KNOTEN_ID";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
        }
        oci_bind_by_name($stmt, ':datum', $datum);

        // Execute the query
        if (oci_execute($stmt)) {
            echo "
<div id='tbl-flex'>
    <div class='col-lg-4 col-md-6'>
            <div class='table-container'>
<table class='table caption-top'>
<caption> <h3>Dienste</h3> </caption>
    <thead class='thead-dark'>";
            echo "<tr><th scope='col'>Zeit</th><th scope='col'>Name</th><th scope='col'>Station/Dienst</th>
";
//check if user is loggedIn, then display Overtime
            if ($_SESSION["loggedin"] && $permission < 3) {
                echo "<th>Überstunden</th>";
            }
            "
</tr>";

// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
                if (strpos($row["TITEL"], "OA") !== false || strpos($row["TITEL"], "PN 22:00-07:00") !== false){
                    echo "<tr class='table-secondary OA' >";
                } else {
                    echo "<tr class='text-end'>";
                }
                if ($row["DIENSTPOSITION"] == "L" || $row["DIENSTPOSITION"] == "R") {
                    echo "<tr class='table-secondary'>";
                }
                if($row["DIENSTPOSITION"] == "L") {
                    echo "<td><img src='assets/L.png' alt='Frühdienst' width='50' height='50'></td>";
                }
                else
                    if($row["DIENSTPOSITION"] == "R") {
                        echo "<td><img src='assets/R.png' alt='Frühdienst' width='50' height='50'></td>";
                    }
                    else {
                        echo "<td> </td>";
                    }
                $contactInfo = "";
                if(!$row["TEL_INTERN"] && !$row["TEL_PRIVAT"] && !$row["TEL_SONST"] && !$row["PAGER"]) {
                    $contactInfo .= " | keine Telefonnummer hinterlegt";
                }


                if (!$row["TEL_INTERN"] == null) {
                    $blurredNumber = substr($row['TEL_INTERN'], 0, 5) . str_repeat('*', strlen($row['TEL_INTERN']) - 5);
                    $contactInfo .= " |I " . $blurredNumber;
                }
                if (!$row["TEL_PRIVAT"] == null) {
                    $blurredNumber = substr($row['TEL_PRIVAT'], 0, 5) . str_repeat('*', strlen($row['TEL_PRIVAT']) - 5);
                    $contactInfo .= " |P " . $blurredNumber;
                }
                if (!$row["TEL_SONST"] == null) {
                    $blurredNumber = substr($row['TEL_SONST'], 0, 5) . str_repeat('*', strlen($row['TEL_SONST']) - 5);
                    $contactInfo .= " |S " . $blurredNumber;
                }
                if (!$row["PAGER"] == null) {
                    $blurredNumber = substr($row['PAGER'], 0, 5) . str_repeat('*', strlen($row['PAGER']) - 5);
                    $contactInfo .= " |P " . $blurredNumber;
                }

//Hover Function
                echo "
<td>
<div class='CustomHover'>
<h3>
" . $row['KUERZEL'] . " | " . $row['VORNAME'] . " " . $row['NACHNAME'] . "
</h3>

 
<div class='focus-content'>
" . $contactInfo . "
</div>
</div>
</td>";
                echo "<td>";
                switch ($row['TITEL']) {
                    case "Planung OP_Koord":
                        echo "P0-PROGRAMM";
                        break;
                    case "L = HNO":
                        echo "HNO";
                        break;
                    case "C = Ebene 01":
                    case (strpos($row['TITEL'], "ChirAllg") !== false);
                        echo "OP-Ost 01";
                        break;
                    case "Weisse Zone":
                    case '"Line-Dienst"':
                        echo "Vf[V]"; //Temp
                        break;
                    case "C_ChirAllg Thrx aa":
                        echo "Thorax";
                        break;
                    case "H_HerzGefass_AA":
                        echo "Herz/Gefäss";
                        break;
                    case "W = OP West":
                    case "West_Ortho_OA":
                        echo "OP-West";
                        break;
                    case "Chef West OA":
                        echo "<h5>CHEF WEST</h5>";
                        break;
                    case "T_TraumaNF_AA":
                        echo "Ortho/Trauma";
                        break;
                    case "NeuroKopf_OA":
                    case "N_NeuroKopf_AA":
                        echo "Neuro";
                        break;
                    case "Spatdienst_OA":
                        echo "P2";
                        break;
                    case "D2 = Dienst_AA":
                        echo "D2";
                        break;
                    case "S2 = Sectio spat AA":
                    case "Sectio Spat OA":
                        echo "S2";
                        break;
                    case "PN 22:00-07:00":
                        echo "Pn_OA";
                        break;
                    case "H2 07.00 - 07.00":
                    case "H2Pras_u_Pikett":         //sometimes H2 shows up twice, try to correct
                        echo "H2";
                        break;
                    case "WE_Nachtdienst_OA":
                        echo "P3";
                        break;
                    case "D1 = Dienst_AA":
                        echo "D1";
                        break;
                    case "B=Mitteldienst":
                        echo "Ost Spätdienst";
                        break;
                    case "S = Prameddienst":
                        echo "Pramädi";
                        break;
                    case "Spat West":
                        echo "West Spaetdienst";
                        break;
                    case "D3 = Dienst_AA":
                        echo "D3";
                        break;
                    case "N3=Notarzt_AA":
                        echo "N3";
                        break;
                    case "S3=Sektiodienst":
                        echo "S3";
                        break;

                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";

                if ($_SESSION["loggedin"] && $permission < 3) {

                    echo "<td class='text-end'>";
                    $hours = floor((float)($row['SALDO'] / 60 / 60));
                    $minutes = (int)($row['SALDO'] / 60) % 60;
                    $minutes = abs($minutes);
                    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT); // Pad the minutes with leading zeros
                    $result = $hours . ":" . $minutes . " ";
                    if ($row['SALDO'] > 0) {
                        echo "<p style='color: green'>" . $result;
                    } else {
                        echo "<p style='color: red'>" . $result;
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table>
</div>
</div>
</div>";
        } else {
            $e = oci_error($stmt);
        }
        $dayOfWeek = date('w', strtotime($datum));

        // Check if it's Saturday or Sunday
        if ($dayOfWeek != 0 && $dayOfWeek != 6) {
        // It's a weekday, execute the code

        // Weisse Zone
        $sql = "SELECT MA.NACHNAME, MA.VORNAME, MA.KUERZEL, D.TITEL, MA.TEL_INTERN, MA.TEL_PRIVAT, MA.TEL_SONST, MA.PAGER, PL.DIENSTPOSITION, D.PA_CODE, PL.KNOTEN_ID, FnGLAZSaldo(MA.ID, TO_DATE(:datum, 'YYYY-MM-DD')) AS SALDO
    FROM ECBERN.DIENST D, ECBERN.PLANUNG PL, ECBERN.MITARBEITER MA
    WHERE (MA.ID = PL.MITARBEITER_ID) AND D.PA_CODE = PL.PA_CODE AND PL.KNOTEN_ID in (1527,28303) AND (PL.DATUM = TO_DATE(:datum, 'YYYY-MM-DD')) AND  D.PA_CODE IN (856,567,505,283,5491,992,2608,5560,5548,5547)
    ORDER BY decode (D.PA_CODE, $sortierung3),PL.KNOTEN_ID";

        if (isset($oracleConn)) {
            $stmt = oci_parse($oracleConn, $sql);
        }
        oci_bind_by_name($stmt, ':datum', $datum);

        // Execute the query
        if (oci_execute($stmt)) {
            echo "
<div id='tbl-flex'>
    <div class='col-lg-4 col-md-6'>
            <div class='table-container'>
<table class='table caption-top'>
<caption> <h3>Weisse Zone</h3> </caption>
    <thead class='thead-dark'>";
            echo "<tr><th scope='col'>Zeit</th><th scope='col'>Name</th><th scope='col'>Station/Dienst</th>";

//check if user is logged in, then display Overtime
            if ($_SESSION["loggedin"] && $permission < 3) {
                echo "<th>Überstunden</th>";
            }
// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {

                $contactInfo = getStr($row);

                switch ($row['TITEL']) {
                    case "Tagdienst_Koord":
                        echo "<h5>CHEF OST</h5>";
                        break;
                    case "L = HNO":
                        echo "HNO";
                        break;
                    case "C = Ebene 01":
                    case (strpos($row['TITEL'], "ChirAllg") !== false);
                        echo "OP-Ost 01";
                        break;
                    case "Weisse Zone 2":
                        echo "WZ spaet";
                        break;
                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";
                //check if user is loggedIn, then display Overtime
                if ($_SESSION["loggedin"] && $permission < 3) {

                    echo "<td class='text-end'>";
                    $hours = floor((float)($row['SALDO'] / 60 / 60));
                    $minutes = (int)($row['SALDO'] / 60) % 60;
                    $minutes = abs($minutes);
                    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT); // Pad the minutes with leading zeros
                    $result = $hours . ":" . $minutes . " ";
                    if ($row['SALDO'] > 0) {
                        echo "<p style='color: green'>" . $result;
                    } else {
                        echo "<p style='color: red'>" . $result;
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            }
            echo "
</div>
</div>
</div>";
        } else $e = oci_error($stmt);
        //close Oracle DB connection
        oci_close($oracleConn);
        //close MySQL DB connection
        $mySqlConn->close();


        ?>
    </div>
</div>
</body>
</html>



