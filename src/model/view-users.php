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

<h2>Welcome to Wabi-Tabi! Here are our current users!<h2>
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

<h3>Currently registered Users</h3>

<table>
    <?php
    handleDisplayTable();
    ?>
</table>

<form method="GET" action="view-users.php"> <!--refresh page when submitted-->
    <input type="hidden" id="RefreshTableRequest" name="RefreshTableRequest">
    <br>
    <input type="submit" value = "Refresh Table" name="refreshTable"></p>
</form>

<h2>Register User</h2>
<form method="POST" action="view-users.php"> <!--refresh page when submitted-->
    <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
    User ID: <input type="text" name="insert_userID" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    Email: <input type="text" name="insert_email" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    First Name: <input type="text" name="insert_firstName" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    Last Name: <input type="text" name="insert_lastName" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    <input type="submit" value="Insert" name="insertSubmit"></p>
</form>

<hr />

<h2>Update User</h2>
<p>The values are case sensitive and if you enter in the wrong case, this will not do anything.</p>

<form method="POST" action="view-users.php"> <!--refresh page when submitted-->
    <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
    <!-- User ID: <input type="text" name="update_userID"> <br /><br /> -->
    Email: <input type="text" name="update_email" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    <select id="update_key_dropdown" name="update_key">
    <option value="email">Email</option>
    <option value="firstName">First Name</option>
    <option value="lastName">Last Name</option>
    <!-- Target Attribute: <input type="text" name="update_key"> <br /><br /> -->
    New Attribute value: <input type="text" name="update_value" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />

    <input type="submit" value="Update" name="updateSubmit"></p>
</form>

<hr />

<h2>Remove User</h2>
<p>The values are case sensitive and if you enter in the wrong case, this will not do anything.</p>

<form method="POST" action="view-users.php"> <!--refresh page when submitted-->
    <input type="hidden" id="removeQueryRequest" name="removeQueryRequest">
    User ID: <input type="text" name="remove_userID" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    Email: <input type="text" name="remove_email" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
    <!-- First Name: <input type="text" name="firstName"> <br /><br />
    Last Name: <input type="text" name="lastName"> <br /><br /> -->

    <input type="submit" value="Remove" name="removeSubmit"></p>
</form>

<hr />

<h2>★ Find MVP WabiTabi Users ★</h2>
<p>Find users who have written a text review of every single attraction on WabiTabi! We are very thankful for their contributions!</p>

<form method='GET' action='view-users.php'>
            <input type='hidden' id='findMVP' name='findMVP'>
            <input type="submit" value="Find and Display MVP Users" name="findMVPbutton"></p>
        </form>

<table>
            <?php
                if (isset($_GET['findMVP'])){
                    handleMVPRequest();
                }
            ?>
        </table>
<hr />

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
    $email = $_POST['update_email'];
    $key = $_POST['update_key'];
    $value = $_POST['update_value'];
    // echo $email . $key . $value;
    $number = 0;
    $number2 = 0;
    $check = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $email . "'");
    $check2 = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $value . "'");
    if (($row = oci_fetch_row($check)) != false) {
        $number = $row[0];
    }
    if (($row = oci_fetch_row($check2)) != false) {
        $number2 = $row[0];
    }
    if ($number < 1) {
        echo "Email does not exist";
    } else if ($number2 > 0 && $key == "email") {
        echo "Proposed email already exists. Please try again.";
    } else {
        executePlainSQL("UPDATE Users SET " . $key . "='" . $value . "' WHERE email='" . $email . "'");
        echo "Update successful. Please refresh table to see changes.";
    }

    OCICommit($db_conn);
}

function handleInsertRequest() {
    global $db_conn;

    //Getting the values from user and insert data into the table
    
    $tuple = array (
        ":userID" => $_POST['insert_userID'],
        ":email" => $_POST['insert_email'],
        ":firstName" => $_POST['insert_firstName'],
        ":lastName" => $_POST['insert_lastName']
    );
    $alltuples = array (
        $tuple
    );
    $userID = $_POST['insert_userID'];
    $email = $_POST['insert_email'];
    $number = 0;
    $number2 = 0;
    $count = executePlainSQL("SELECT COUNT(*) FROM Users WHERE userID='" . $userID . "'");
    $count2 = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $email . "'");
    if (($row = oci_fetch_row($count)) != false) {
        $number = $row[0];
        // echo "The number is " . $row[0];
    }
    if (($row = oci_fetch_row($count2)) != false) {
        $number2 = $row[0];
        // echo "The number is " . $row[0];
    }
    if ($userID == null || $email == null) {
        echo "Please enter a userID and email";

    } else if ($number == 0 && $number2 == 0) {
        executeBoundSQL("insert into Users values (:userID, :email, :firstName, :lastName)", $alltuples);
        // executePlainSQL()
        // executePlainSQL("insert into Users (userID, email, firstName, lastName) VALUES (" . $userID . " ," .  $email . " ," .  $firstName . " ," . $lastName . ")");
        // executePlainSQL("insert into Users (userID, email, firstName, lastName) VALUES (hi,hi,h,hi)");
        echo ("Created User " . $_POST['insert_userID'] . " with name " . $_POST['insert_firstName'] . " " . $_POST['insert_lastName'] . " with email " . $_POST['insert_email']);
        OCICommit($db_conn);
    } else if ($number > 0) {
        echo "This userID already exists. Please choose another one.";
    } else if ($number2 > 0) {
        echo "This email already exists. Please choose another one.";
    }
    // executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);

    // handleDisplayTable();
}

function handleRemoveRequest() {
    global $db_conn;

    // $userID = $_POST['userID'];

    $userID = $_POST['remove_userID'];
    $email = $_POST['remove_email'];
    $number = 0;
    // $number2 = 0;
    $count = executePlainSQL("SELECT COUNT(*) FROM Users WHERE userID='" . $userID . "' AND email='" . $email . "'");
    // $count2 = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $email . "'");
    if (($row = oci_fetch_row($count)) != false) {
        $number = $row[0];
        // echo "The number is " . $row[0];
    }
    // if (($row = oci_fetch_row($count2)) != false) {
    //     $number2 = $row[0];
    //     // echo "The number is " . $row[0];
    // }
    if ($number > 0) {
        $userID = $_POST['remove_userID'];
        $email = $_POST['remove_email'];
        // echo $email . $key . $value;
        executePlainSQL("DELETE FROM Users WHERE email='" . $email . "' AND  userID='" . $userID . "'");
    
        // executeBoundSQL("insert into Users values (:userID, :email, :firstName, :lastName)", $alltuples);
    
        // you need the wrap the old name and new name values with single quotations
        // executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
        OCICommit($db_conn);
        echo "Remove request submitted. Please refresh table to see changes.";
    } else {
        echo "Remove request rejected. Email does not match the provided userID";
    }
    
    
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

function handleDisplayTable() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT * FROM Users");
    $data = array();

    while (($row = oci_fetch_row($result)) != false) {
        $data[] = new User($row[0], $row[1], $row[2], $row[3]);
    }
    displayTable($data);
    disconnectFromDB();
}

function displayTable($data) {
    echo "<tr>";
    echo "<td> User ID</td>";
    echo "<td> First Name</td>";
    echo "<td> Last Name</td>";
    echo "</tr>";
    foreach ($data as $user) {
        echo "<tr>";
        // foreach ($user as $attribute) {
        //     echo "<td>" . $attribute . "</td>";
        // }
        echo "<td><a href='view-user.php?userID=" . $user->userID . "'>" . $user->userID . "</a></td>";
        echo "<td>" . $user->firstName . "</td>";
        echo "<td>" . $user->lastName . "</td>";
        echo "</tr>";
    }
}

function handleMVPRequest() {
    connectToDB();
    global $db_conn;

    $result = executePlainSQL("SELECT DISTINCT U.userID, U.email, U.firstName, U.lastName
    FROM Users U
    WHERE NOT EXISTS (
        SELECT A.attractionID
        FROM Attractions A
        WHERE NOT EXISTS (
            SELECT *
            FROM TextReviews TR
            WHERE TR.attractionID = A.attractionID
            AND TR.userID = U.userID)
    )");
    $data = array();

    while (($row = oci_fetch_row($result)) != false) {
        $data[] = new User($row[0], $row[1], $row[2], $row[3]);
    }
    displayTable($data);
    disconnectFromDB();
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
// } else if (isset($_GET['RefreshTableRequest'])) {
//     handleDisplayTable();
}
?>
</body>
</html>
