<?php
global $mySqlConn;

// Include the database configuration file
require_once "config/dbConfig.php";


// Store the selected date in a session variable if submitted
if (isset($_POST['date'])) {
// Store the selected date in a session variable
    $_SESSION['selected_date'] = $_POST['date'];
}

//get date from form or use current date
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
<body>

<header class="container-fluid">
    <nav class="navbar">
        <ul class="nav nav-pills navbar-expand-lg ">
            <li class="nav-item">
                <a class="navbar-brand" aria-current="page" href="index.php">
                    <img src="assets/USB_Logo.png" id="logo" alt="USB Logo">
                </a>
            </li>
            <li>
                <a class="nav-link" href="">Konfiguration</a>
            </li>
            <li>
                <a class="nav-link" href="">Benutzerverwaltung</a>
            </li>
            <li>
                <a class="nav-link" href="">Login</a>
            </li>

        </ul>
    </nav>
</header>

<!--   Main Content   -->

<div class="container-fluid">
    <div class="container card mb-3">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="Tagesinformation" class="form-label">Tagesinformation </label>
            <textarea disabled  name="Tagesinformation" id="Tagesinformation" rows="5" cols="50" class="form-control">
    <?php

    //return entry from mysql Database
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
        </form>
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
        ORDER BY decode (D.PA_CODE, 551,1 ,5539,1.5, 560,5 ,5531,5.5, 504,6,5519,6.5, 501,7,5518,7.1, 2401,7.5, 5507, 7.8, 1029,8,5490,8.5, 515,9, 5501, 9.5, 518,10, 561, 11, 5532,11.5, 1030,12,5566,12.1,565, 14,5579, 5535,14.5, 1031,15,5567,15.1, 566, 20, 5536,20.5, 1032, 21,5568,21.1, 2416,21.5, 5546,40, 528, 40.5, 5564,40.6,562, 41, 5533,41.5, 540,43,5520,43.1, 576, 105 ,5545,105.5, 200, 2435),PL.KNOTEN_ID";

        if (isset($conn)) {
            $stmt = oci_parse($conn, $sql);
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



// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {

// Convert PEP-title to predefined names
                $contactInfo = getStr($row);
                switch ($row['TITEL']) {
                    case "Tagdienst_Koord":
                        echo "<h5>CHEF OST</h5>";
                        break;
                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";


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
		ORDER BY decode (D.PA_CODE, 570,1, 5538,1.5, 3058,1.6, 5489, 1.8, 855,2, 5540,2.5, 520,3,5503,3.2, 3059,3.5,5508,3.6, 1028,4, 5543,4.5, 527,5,5561,5.1, 3301,6, 858,7, 7047,7.5, 571,8, 5541,8.5, 507,9,5563,9.1, 7048,10, 516,11, 5505,11.5, 1057,12, 5504,12.5, 508,13, 5502,13.5, 514,14,5496,14.5, 513,15, 5492,15.1), PL.KNOTEN_ID";

        if (isset($conn)) {
            $stmt = oci_parse($conn, $sql);
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
            "
</tr>";

// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
// emphasize OA
                if (strpos($row["TITEL"], "OA") !== false){
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
                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";

                echo "</tr>";
            }
            echo "</table>
</div>
</div>
</div>";
        } else {
            $e = oci_error($stmt);
        }

        $sql = "SELECT MA.NACHNAME, MA.VORNAME, MA.KUERZEL, D.TITEL, MA.TEL_INTERN, MA.TEL_PRIVAT, MA.TEL_SONST, MA.PAGER, PL.DIENSTPOSITION, D.PA_CODE, PL.KNOTEN_ID, FnGLAZSaldo(MA.ID, TO_DATE(:datum, 'YYYY-MM-DD')) AS SALDO
        FROM ECBERN.DIENST D, ECBERN.PLANUNG PL, ECBERN.MITARBEITER MA
        WHERE (MA.ID = PL.MITARBEITER_ID) AND D.PA_CODE = PL.PA_CODE AND PL.KNOTEN_ID in (1527,28303) AND (PL.DATUM = TO_DATE(:datum, 'YYYY-MM-DD')) AND  D.PA_CODE IN (856,567,505,283,5491,992,2608,5560,5548,5547)
        ORDER BY decode (D.PA_CODE, 992,1, 5547,1.5, 2428,2, 283,3, 5491, 3.1, 5548,3.2),PL.KNOTEN_ID";

        if (isset($conn)) {
            $stmt = oci_parse($conn, $sql);
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


// Fetch the results
            while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {

                $contactInfo = getStr($row);

                switch ($row['TITEL']) {
                    default:
                        echo $row['TITEL'];
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>
</div>
</div>
</div>";
        } else $e = oci_error($stmt);
        //close Oracle DB connection
        oci_close($conn);
        //close MySQL DB connection
        $mySqlConn->close();


        ?>
    </div>
</div>
</body>
</html>



