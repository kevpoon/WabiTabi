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
    <title>CPSC 304 PHP/Oracle View-User </title>
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

<?php
if (isset($_GET['userID'])) {
    echo "<h2> User " . $_GET['userID'] . "'s Reviews! (>._.)> <h2>";
} else if (isset($_POST['userID'])) {
    echo "<h2> User " . $_POST['userID'] . "'s Reviews! (>._.)> <h2>";
}  
?>

<table>
    <?php
    handleDisplayTable();
    ?>
</table>



<hr>

<hr />

<h2>Remove Review</h2>
<p>The email is case sensitive and if you enter in the wrong case, the remove statement will not do anything.</p>

<?php
    $userID = "Error: Please contact Admin.";
if (isset($_GET['userID'])) {
    $userID = $_GET['userID'];
} else if (isset($_POST['userID'])) {
    $userID = $_POST['userID'];
}
echo 
"<form method='POST' action='view-user.php'> 
    <input type='hidden' id='removeQueryRequest' name='removeQueryRequest'>
    Email: <input type='text' name='removeReview_email' pattern='[a-zA-Z0-9@._]+' required='required'><br /><br />
    Review ID: <input type='text' name='removeReview_reviewID' pattern='[a-zA-Z0-9@._]+' required='required'><br /><br />
    <input type='hidden' id='userID' value = '" . $userID . "' name='userID'>
    <select id='removeReview_dropdown' name='ReviewType'>
    <option value='TextReview'>TextReview</option>
    <option value='PhotoReview'>PhotoReview</option>
    <input type='submit' value='Remove' name='removeSubmit'></p>
</form>
";
?>

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


function handleRemoveRequest() {
    global $db_conn;

    // $userID = $_POST['userID'];
    $name = "Error: Please contact admin!";
    if (isset($_GET['userID'])) {
        $name = $_GET['userID'];
    } else if (isset($_POST['userID'])) {
        $name = $_POST['userID'];
    }
    $email = $_POST['removeReview_email'];
    $type = $_POST['ReviewType'];
    $reviewID = $_POST['removeReview_reviewID'];
    // echo $email . $key . $value;
    // echo $name;
    $result = executePlainSQL("SELECT * FROM Users WHERE Users.userID = '" . $name . "'");
    $correctEmail = "hi";
    while(($row = oci_fetch_row($result)) != false) {

        // echo "Thing" . $row[1];
        $correctEmail = $row[1];
    }

    // echo "Correct email [" . $correctEmail . "]";
    // echo "email [" . $email . "]";
    if ($email == $correctEmail) {
        if ($type == "TextReview") {
            $result = executePlainSQL("SELECT Count(*) FROM TextReviews WHERE userID='" . $name . "' AND reviewID='" . $reviewID . "'");
            if (($row = oci_fetch_row($result)) != false) {
                if ($row[0] > 0) {
                    executePlainSQL("DELETE FROM TextReviews WHERE userID='" . $name . "' AND reviewID='" . $reviewID . "'");
                    echo "Text Review ID: " . $reviewID . " removed! Please refresh your page to see changes.";
                } 
            } else {
                echo "TextReview ID: " . $reviewID . " not found. Please try again.";
            }
        } else if ($type == "PhotoReview") {
            $result = executePlainSQL("SELECT Count(*) FROM PhotoReviews WHERE userID='" . $name . "' AND reviewID='" . $reviewID . "'");
            if (($row = oci_fetch_row($result)) != false) {
                if ($row[0] > 0) {
                    executePlainSQL("DELETE FROM PhotoReviews WHERE userID='" . $name . "' AND reviewID='" . $reviewID . "'");
                    echo "PhotoReview ID: " . $reviewID . " removed! Please refresh your page to see changes.";
                }
                
            } else {
                echo "PhotoReview ID: " . $reviewID . " not found. Please try again.";
            }
        }
    } else {
        echo "Incorrect email. Review removal unsuccessful.";
    }

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

class ReviewHeader {
    //reviewID, rating, title, publishDate,
    public $type;
    public $reviewID;
    public $rating;
    public $title;
    public $publishDate;

    public function __construct($type, $reviewID, $rating, $title, $publishDate) {
        $this->type = $type;
        $this->reviewID = $reviewID;
        $this->rating = $rating;
        $this->title = $title;
        $this->publishDate = $publishDate;
    }
}

function handleDisplayTable() {
    connectToDB();
    global $db_conn;
    $name = "Error: Please contact admin!";
    if (isset($_GET['userID'])) {
        $name = $_GET['userID'];
    } else if (isset($_POST['userID'])) {
        $name = $_POST['userID'];
    }
    // echo $name;
    $data = array();

    $result = executePlainSQL("SELECT * FROM TextReviews WHERE userID='" . $name . "'");
    

    while (($row = oci_fetch_row($result)) != false) {
        // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
        // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
        for ($i = 0; $i < count($row); $i++) {
            // echo "hi". "<br>";
            // echo "his" . $row[$i] . "<br>";
        }
        $data[] = new ReviewHeader("TextReview", $row[0], $row[1], $row[2], $row[3]);
    }
    $result = executePlainSQL("SELECT * FROM PhotoReviews WHERE userID='" . $name . "'");
    

    while (($row = oci_fetch_row($result)) != false) {
        // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
        // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
        for ($i = 0; $i < count($row); $i++) {
            // echo "hi". "<br>";
            // echo "his" . $row[$i] . "<br>";
        }
        $data[] = new ReviewHeader("PhotoReview", $row[0], $row[1], $row[2], $row[3]);
    }
    // echo count($data);
    

    displayTable($data);
    

    disconnectFromDB();
}

function displayTable($data) {
    $headers = false;
    $empty = true;

        foreach ($data as $reviewHeader) {
            $empty = false;
            if ($headers == false) {
                echo "<tr>";
                echo "<td> Review Type </td>";
                echo "<td> ID </td>";
                echo "<td> Title </td>";
                echo "<td> Rating </td>";
                echo "<td> Date Published </td>";
                echo "</tr>";
                $headers = true;
            }
            echo "<tr>";
            // foreach ($user as $attribute) {
            //     echo "<td>" . $attribute . "</td>";
            // }
            echo "<td>" . $reviewHeader->type . "</td>";
            echo "<td>" . $reviewHeader->reviewID . "</td>";
            if ($reviewHeader->type == "TextReview") {
                echo "<td><a href='view-textreview.php?reviewID=". $reviewHeader->reviewID . "'>" . $reviewHeader->title . "</a></td>";
            } else if ($reviewHeader->type == "PhotoReview") {
                echo "<td><a href='view-photoreview.php?reviewID=". $reviewHeader->reviewID . "'>" . $reviewHeader->title . "</a></td>";
            }
            // echo "<td>" . $reviewHeader->title . "</td>";
            echo "<td>" . $reviewHeader->rating . "</td>";
            echo "<td>" . $reviewHeader->publishDate . "</td>";
            echo "</tr>";
        }
        if ($empty == true) {
 
            echo  "<br> <h5>No posts yet! ._. <h5><br> ";
        }
    // }
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
    // handleDisplayTable();
}
?>
</body>
</html>
