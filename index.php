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


<!--    Table 1     -->
        <?php

    $sql = "SELECT MA.NACHNAME, MA.VORNAME, MA.KUERZEL, D.TITEL,  D.PA_CODE, PL.KNOTEN_ID, FnGLAZSaldo(MA.ID, TO_DATE(:datum, 'YYYY-MM-DD')) AS SALDO
        FROM ECBERN.DIENST D, ECBERN.PLANUNG PL, ECBERN.MITARBEITER MA
        WHERE (MA.ID = PL.MITARBEITER_ID) AND D.PA_CODE = PL.PA_CODE AND PL.KNOTEN_ID in (1527,28303) AND (PL.DATUM = TO_DATE(:datum, 'YYYY-MM-DD')) AND D.PA_CODE NOT IN (568,569,10,1,568,709,702,31,40, 857,70,71,577,525,53,54,5,20,706, 25, 516,5505,507,5563,508,5502,279,514,5496,570,3302,855,1028,513,5492,527,5561,571,575,3301,192,281, 97,856,567,505,283,5491,992,506, 7048,520,5503,509,5506, 1057,5504,2302,5562,5541,5537,5538,5540,5543,5544,5560,5548,5547 ) AND  D.PA_CODE IN (518,5779,551,560,504,5519,501,5518,1029,5490,515,5501,561,1030,5566,566,1032,5568,5779 ,565,1031,5567,528,5564,562,540,5520,5520,576,2401,5507, 2416,5539,5531,5532,5536,5535,5546,5533,5545,5537,2435 )
        ORDER BY decode (D.PA_CODE, 551,1 ,5539,1.5, 560,5 ,5531,5.5, 504,6,5519,6.5, 501,7,5518,7.1, 2401,7.5, 5507, 7.8, 1029,8,5490,8.5, 515,9, 5501, 9.5, 518,10, 561, 11, 5532,11.5, 1030,12,5566,12.1,565, 14,5579, 5535,14.5, 1031,15,5567,15.1, 566, 20, 5536,20.5, 1032, 21,5568,21.1, 2416,21.5, 5546,40, 528, 40.5, 5564,40.6,562, 41, 5533,41.5, 540,43,5520,43.1, 576, 105 ,5545,105.5, 200, 2435),PL.KNOTEN_ID";

        if (isset($conn)) {
            $stmt = oci_parse($conn, $sql);
        }
        oci_bind_by_name($stmt, ':datum', $datum);
        oci_execute($stmt);

        $resultString = "";

        while (($row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
            $rowString = implode(", ", $row);
            $resultString .= $rowString . "\n";
        }

        echo $resultString;

        //TODO: Convert String into table
    ?>

<!--    TODO: Add table 'Dienste' here-->

<!--    TODO: Add table 'Weisse Zone' here-->



<?php
    //close Oracle DB connection
    oci_close($conn);
    //close MySQL DB connection
    $mySqlConn->close();
    ?>

</div>
</body>
</html>