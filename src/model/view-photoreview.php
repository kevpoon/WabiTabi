<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password

  ======================================================================================
  Our 304 project was based on the Official CPSC 304 Sample file (oracle-test.php)
  provided as a tutorial for groups using php. Sample helpers were used in our functions.
  =======================================================================================
  -->

<html>
<head>
    <title>CPSC 304 PHP/Oracle Demonstration</title>
</head>

<body>

<h2>Welcome to Wabi-Tabi!<h2>
<nav>
    <a href="home.php">Home</a>
    <a href="view-users.php">Users</a>
    <a href="view-destination.php">Destinations</a>
    <a href="view-attractions.php">Attractions</a>
    <a href="view-itineraries.php">Itineraries</a>
    <a href="admin.php">Admin</a>
</nav>

<body>

<hr>

<body>
<?php
    handleDisplayPhotoReview();
?>
<body>


<?php
//this tells the system that it's no longer just parsing html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = NULL; // edit the login credentials in connectToDB()
$show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

function debugAlertMessage($message) {
    global $show_debug_alert_messages;

    if ($show_debug_alert_messages) {
        echo "<script type='text/javascript'>alert('" . $message . "');</script>";
    }
}

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;

    $statement = OCIParse($db_conn, $cmdstr);
    //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
        echo htmlentities($e['message']);
        $success = False;
    }
    // echo "Success!";
    return $statement;
}

function executeBoundSQL($cmdstr, $list) {
    /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
In this case you don't need to create the statement several times. Bound variables cause a statement to only be
parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
See the sample code below for how this function is used */

    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            //echo $val;
            //echo "<br>".$bind."<br>";
            OCIBindByName($statement, $bind, $val);
            unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }
}

function connectToDB() {
    global $db_conn;

    // Your username is ora_(CWL_ID) and the password is a(student number). For example,
    // ora_platypus is the username and a12345678 is the password.
    $db_conn = OCILogon("ora_kp1998", "a42821165", "dbhost.students.cs.ubc.ca:1522/stu");

    if ($db_conn) {
        debugAlertMessage("Database is Connected");
        return true;
    } else {
        debugAlertMessage("Cannot connect to Database");
        $e = OCI_Error(); // For OCILogon errors pass no handle
        echo htmlentities($e['message']);
        return false;
    }
}

function disconnectFromDB() {
    global $db_conn;

    debugAlertMessage("Disconnect from Database");
    OCILogoff($db_conn);
}

function handleDisplayPhotoReview() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT * 
    FROM PhotoReviews TR, Attractions A, Photos P
    WHERE TR.attractionID = A.attractionID AND TR.reviewID = " . $_GET['reviewID'] . " AND TR.reviewID = P.reviewID");

    if (($row = oci_fetch_row($result)) != false) {
        if ($row[5] == 1) {
        echo "<h2>" . $row[2] . " (ID: " . $row[0] . ")</h2>";
        echo "<h3 style='color: #333;'>Attraction: <a href='view-attraction.php?attractionID="  . $row[8] . "'>"  . $row[9] . "</a> | Rating: " . $row[1] . "/10 | Number of Photos: " . $row[4] . "</h3>";
        echo "<table style='width: 100%;'>";
        echo "<tr><td><p style='font-size: 20px; color: #009;'>" . $row[14] . "</p></td></tr>";
        echo "<tr><td><img src='" . $row[15] . "' alt='" . $row[15] . "' style='max-width: 100%; height: auto;'></td></tr>";
        echo "<tr><td><h4 style='color: #333;'>Posted on " . $row[3] . " by <a href='view-user.php?userID="  . $row[6] . "'>"  . $row[6] . "</a></h4></td></tr>";
        echo "</table>";
        echo "</div>";
        } else{
            echo "<h2>" . $row[2] . " (ID: " . $row[0] . ")</h2>";
            echo "<h3 style='color: #333;'>Attraction: <a href='view-attraction.php?attractionID="  . $row[8] . "'>"  . $row[9] . "</a> | Rating: " . $row[1] . "/10 | Number of Photos: " . $row[4] . "</h3>";
            echo "<table style='width: 100%;'>";
            echo "<tr><td><p style='font-size: 16px; color: #009;'> Post visibility hidden. </p></td></tr>";
            echo "<tr><td><h4 style='color: #333;'>Posted on " . $row[3] . " by <a href='view-user.php?userID="  . $row[6] . "'>"  . $row[6] . "</a></h4></td></tr>";
            echo "</table>";
            echo "</div>";
        }
    }
    disconnectFromDB();
}

function handleLoadRequest() {
    global $db_conn;

}

// HANDLE ALL POST ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handlePOSTRequest() {
    if (connectToDB()) {
        if (array_key_exists('resetTablesRequest', $_POST)) {
            handleResetRequest();
        } else if (array_key_exists('updateQueryRequest', $_POST)) {
            handleUpdateRequest();
        } else if (array_key_exists('insertQueryRequest', $_POST)) {
            handleInsertRequest();
        } else if (array_key_exists('removeQueryRequest', $_POST)) {
            handleRemoveRequest();
        }

        disconnectFromDB();
    }
}

// HANDLE ALL GET ROUTES
// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
function handleGETRequest() {
    if (connectToDB()) {
        if (array_key_exists('countTuples', $_GET)) {
            handleCountRequest();
        }

        disconnectFromDB();
    }
}

if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) 
|| isset($_POST['removeSubmit'])) {
    handlePOSTRequest();
} else if (isset($_GET['countTupleRequest'])) {
    handleGETRequest();
} else if (isset($_GET['RefreshTableRequest']) || isset($_GET['userID'])) {
    handleDisplayTable();
}
?>
</body>
</html>
