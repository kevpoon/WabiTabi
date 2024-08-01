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
    handleDisplayAttraction();
?>
<body>
<hr>




<table>
    <?php
    // handleDisplayTable();
    handleDisplayPhotos();
    ?>
</table>

<hr>

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

function printResult($result) { //prints results from a select statement
    echo "<br>Retrieved data from table demoTable:<br>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th></tr>";

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
    }

    echo "</table>";
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

function handleUpdateRequest() {
    global $db_conn;

    // $userID = $_POST['userID'];
    // $userID = $_POST['update_userID'];
    $email = $_POST['update_email'];
    $key = $_POST['update_key'];
    $value = $_POST['update_value'];
    // echo $email . $key . $value;
    executePlainSQL("UPDATE Users SET " . $key . "='" . $value . "' WHERE email='" . $email . "'");
    // executeBoundSQL("insert into Users values (:userID, :email, :firstName, :lastName)", $alltuples);

    // you need the wrap the old name and new name values with single quotations
    // executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
    OCICommit($db_conn);
}



class User {
    public $userID;
    public $email;
    public $firstName;
    public $lastName;

    public function __construct($userID, $email, $firstName, $lastName) {
        $this->userID = $userID;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}

class Attraction {
    //attractionID, name, description, address, destID
    public $attractionID;
    public $name;
    public $description;
    public $address;
    public $destinationID;
    public $destinationCountry;
    public $destinationCity;
    public $destinationDescription;


    public function __construct($attractionID, $name, $description, $address, $destinationID, $destinationCity, $destinationCountry, $destinationDescription) {
        $this->attractionID = $attractionID;
        $this->name = $name;
        $this->description = $description;
        $this->address = $address;
        $this->destinationID = $destinationID;
        $this->destinationCountry = $destinationCountry;
        $this->destinationCity = $destinationCity;
        $this->destinationDescription = $destinationDescription;
    }
}

function handleDisplayTable() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT Attractions.*, Destinations.* 
    FROM Attractions 
    JOIN Destinations ON Attractions.destID = Destinations.destID 
    WHERE Attractions.attractionID = " . $_GET['attractionID'] . "");
    $data = array();

    while (($row = oci_fetch_row($result)) != false) {
        // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
        // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
        for ($i = 0; $i < count($row); $i++) {
            // echo "hi". "<br>";
            // echo "his" . $row[$i] . "<br>";
        }
        $data[] = new Attraction($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
    }
    displayTable($data);
    disconnectFromDB();
}

function displayTable($data) {
    // Should only have one element.
    echo "<tr>";
    echo "<td> Attraction ID</td>";
    echo "<td> Name</td>";
    echo "<td> Description</td>";
    echo "<td> Destination ID</td>";
    echo "<td> Destination City</td>";
    echo "<td> Destination Country</td>";
    echo "</tr>";
    foreach ($data as $header) {
        echo "<tr>";
        // foreach ($user as $attribute) {
        //     echo "<td>" . $attribute . "</td>";
        // }
        echo "<td>" . $header->attractionID . "</td>";
        echo "<td><a href='view-attraction.php?attractionID=" . $header->attractionID . "'>" . $header->name . "</a></td>";
        echo "<td>" . $header->description . "</td>";
        echo "<td>" . $header->destinationID . "</td>";
        echo "<td><a href='view-destination.php?destinationID=" . $header->destinationID . "'>" . $header->destinationCity . "</a></td>";
        echo "<td>" . $header->destinationCountry . "</td>";
        echo "</tr>";
    }
    echo "<br>";
}

function handleDisplayAttraction() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT Attractions.*, Destinations.* 
    FROM Attractions 
    JOIN Destinations ON Attractions.destID = Destinations.destID 
    WHERE Attractions.attractionID = " . $_GET['attractionID'] . "");
    $data = array();

    if (($row = oci_fetch_row($result)) != false) {
        $data[] = new Attraction($row[0], $row[1], $row[2], $row[3], $row[4], $row[6], $row[7], $row[8]);
    }
    $header = $data[0];
    echo "(ID: " . $header->attractionID . ") Welcome to " . $header->name . "! <br>";
    echo "<hr>";
    echo "<table>";
    echo "<tr><td>" . $header->description . "</td></tr>";
    // echo "<tr><td> Find this attraction at <a href='view-destination.php?destinationID=" . $header->address . "'></td></tr>";
    echo "<tr><td> Find this attraction at <a href='view-destination.php?destinationID=" . $header->destinationID . "'>" . $header->address . "</a></td></tr>";

    
    echo "<tr><td> Located in " . $header->destinationCountry . ", " . $header->destinationCity . " (Destination ID:" . $header->destinationID . ")</td></tr>";
    echo "<tr><td> Destination Description: " . $header->destinationDescription . "</td></tr>";
    echo "</table>";
    disconnectFromDB();
}



class Photo {
    //photoID, caption, photoURL, attractionID, reviewID
    public $photoID;
    public $caption;
    public $photoURL;
    public $attractionID;
    public $reviewID;

    public function __construct($photoID, $caption, $photoURL, $attractionID, $reviewID) {
        $this->photoID = $photoID;
        $this->caption = $caption;
        $this->photoURL = $photoURL;
        $this->attractionID = $attractionID;
        $this->reviewID = $reviewID;
    }

}

function handleDisplayPhotos() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT Photos.* FROM Photos
    JOIN Attractions ON Attractions.attractionID = Photos.attractionID
    WHERE Attractions.attractionID = " . $_GET['attractionID'] . "");
    $data = array();

    while (($row = oci_fetch_row($result)) != false) {
        $data[] = new Photo($row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
    }

    // echo "<tr>";
    // echo "<td> Photo ID</td>";
    // echo "<td> Caption</td>";
    // echo "<td> Photo Image </td>";
    // echo "<td> Attraction ID </td>";
    // echo "<td> Review ID</td>";
    echo "<h4> Photos </h4>";
    foreach ($data as $photo) {
        // foreach ($user as $attribute) {
        //     echo "<td>" . $attribute . "</td>";
        // }
        //echo "" . $photo->caption . "<br>";
        echo "<img src='" . $photo->photoURL . "' alt='Image did not load. URL: " . $photo->photoURL . "' style='width: 500px;'>";
        //echo "<td>" . $photo->photoURL . "</td>";
        // echo "<td><a href='view-destination.php?destinationID=" . $photo->attractionID . "'>" . $photo->attractionID . "</a></td>";
        //echo "<td>" . $photo->reviewID . "</td>";
        // echo "</tr>";
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
