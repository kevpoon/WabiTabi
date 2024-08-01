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
    <h2>Welcome to Wabi-Tabi! You can create your new itinerary here!</h2>

    <h2>
        <nav>
            <a href="home.php">Home</a>
            <a href="view-users.php">Users</a>
            <a href="view-destination.php">Destinations</a>
            <a href="view-attractions.php">Attractions</a>
            <a href="view-itineraries.php">Itineraries</a>
            <a href="admin.php">Admin</a>
        </nav>
    </h2>
    <hr />

    <h2>All Current Itineraries</h2>

    <table>
        <?php
        handleDisplayItineraries();
        ?>
    </table>

    <hr />

    <h2>Create New Itinerary Step 1: Decide your Destination and Duration!</h2>
    <form method="POST" action="view-itineraries.php"> <!--refresh page when submitted-->
        <input type="hidden" id="insertQueryStep1Request" name="insertQueryStep1Request">
        User ID: <input type="text" name="insert_userID" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
        Email: <input type="text" name="insert_emailCheck" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
        Destination ID: <input type="text" name="insert_destID" pattern="[0-9]+" required="required"> <br /><br />
        Duration (Days): <input type="text" name="insert_duration" pattern="[0-9]+" required="required"> <br /> <br />
        <input type="submit" value="Create Itinerary" name="insertSubmit"></p>
    </form>

    <hr />

    <h2>Create New Itinerary Step 2: Add Attractions to Your Itinerary One At A Time!</h2>
    <form method="POST" action="view-itineraries.php"> <!--refresh page when submitted-->
        <input type="hidden" id="insertQueryStep2Request" name="insertQueryStep2Request">
        User ID: <input type="text" name="insert2_userID" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
        Email: <input type="text" name="insert2_emailCheck" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
        Plan ID: <input type="text" name="insert2_planID" pattern="[0-9]+" required="required"> <br /><br />
        Attraction ID: <input type="text" name="insert2_attractionID" pattern="[0-9]+" required="required"> <br /><br />
        Visit Date (YYYY-MM-DD): <input type="text" name="insert2_visitDate" pattern="[0-9]{4}-[0-1][0-9]-[0-3][0-9]" required="required"> <br /> <br />
        <input type="submit" value="Add Attraction to Itinerary" name="insertSubmit"></p>
    </form>

    <hr />

    <h2>Update Your Itinerary: Change Destination and Duration</h2>
    <form method="POST" action="view-itineraries.php"> <!--refresh page when submitted-->
        <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
        Plan ID: <input type="text" name="update_planID" pattern="[0-9]+" required="required"> <br /><br />
        New Destination ID: <input type="text" name="update_destID" pattern="[0-9]+" required="required"> <br /><br />
        New Duration (Days): <input type="text" name="update_duration" pattern="[0-9]+" required="required"> <br /><br />
        Email: <input type="text" name="update_emailCheck" pattern="[a-zA-Z0-9@._]+" required="required"> <br /><br />
        <input type="submit" value="Update Itinerary" name="updateSubmit"></p>
    </form>

    <hr />

    <h2>Find Average Duration Per Destination</h2>
    <form method="GET" action="view-itineraries.php">
        <input type="hidden" id="aggregateHavingQueryRequest" name="aggregateHavingQueryRequest">
        Minimum Number of Itineraries In Destination: <input type="text" name="aggregate_minCount" pattern="[0-9]+" required="required"> <br /><br />
        Maximum Duration: <input type="text" name="aggregate_maxDuration" pattern="[0-9]+" required="required"> <br /><br />
        <input type="submit" value="Calculate Average Duration Per Destination" name="aggregateHavingSubmit">
    </form>

    <br />

    <table>
        <?php
        if (isset($_GET['aggregateHavingQueryRequest'])) {
            handleAggregateHavingRequest();
        }
        ?>
    </table>

    <hr />

    <h2>Filter Itineraries</h2>
    <form method="GET" action="view-itineraries.php">
        <input type="hidden" id="filterQueryRequest" name="filterQueryRequest">

        Plan ID: <input type="text" name="get_planID" pattern="[0-9]+" required="required"> <br /><br />

        <label for='dropdown1'></label>
        <select id='dropdown1' name='dropdown1'>
            <option value='AND'>AND</option>
            <option value='OR'>OR</option>
        </select>
        <br /><br />

        Duration (Days): <input type="text" name="get_duration" pattern="[0-9]+" required="required"> <br /> <br />

        <label for='dropdown2'></label>
        <select id='dropdown2' name='dropdown2'>
            <option value='AND'>AND</option>
            <option value='OR'>OR</option>
        </select>
        <br /><br />

        Destination ID: <input type="text" name="get_destID" pattern="[0-9]+" required="required"> <br /><br />

        <label for='dropdown3'></label>
        <select id='dropdown3' name='dropdown3'>
            <option value='AND'>AND</option>
            <option value='OR'>OR</option>
        </select>
        <br /><br />

        User ID: <input type="text" name="get_userID" pattern="[a-zA-Z0-9@-_]+" required="required"> <br /><br />

        <label for='dropdown4'></label>
        <select id='dropdown4' name='dropdown4'>
            <option value='AND'>AND</option>
            <option value='OR'>OR</option>
        </select>
        <br /><br />

        Date Created: <input type="text" name="get_dateCreated" pattern="[a-zA-Z0-9-]+" required="required"> <br /><br />

        <input type='submit' value='Select' name='filterItinerary'></p>
    </form>

    <table>
        <?php
        if (isset($_GET['filterQueryRequest'])) {
            handleFilterQueryRequestt();
        }
        ?>
    </table>

    <hr />

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP

    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = NULL; // edit the login credentials in connectToDB()
    $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

    function debugAlertMessage($message)
    {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr)
    { //takes a plain (no bound variables) SQL command and executes it
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

        return $statement;
    }

    function executeBoundSQL($cmdstr, $list)
    {
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
                unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
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

    function printResult($result)
    { //prints results from a select statement
        echo "<br>Retrieved data from table demoTable:<br>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
    }

    function connectToDB()
    {
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

    function disconnectFromDB()
    {
        global $db_conn;

        debugAlertMessage("Disconnect from Database");
        OCILogoff($db_conn);
    }

    class Itinerary
    {
        public $planID;
        public $duration;
        public $dateCreated;
        public $destID;
        public $userID;

        public function __construct($planID, $duration, $dateCreated, $destID, $userID)
        {
            $this->planID = $planID;
            $this->duration = $duration;
            $this->dateCreated = $dateCreated;
            $this->destID = $destID;
            $this->userID = $userID;
        }
    }

    class IncludeTuple
    {
        public $attractionID;
        public $planID;
        public $visitDate;

        public function __construct($attractionID, $planID, $visitDate)
        {
            $this->attractionID = $attractionID;
            $this->planID = $planID;
            $this->visitDate = $visitDate;
        }
    }

    class AggregatedItinerary
    {
        public $destID;
        public $averageDuration;

        public function __construct($destID, $avg_duration)
        {
            $this->destID = $destID;
            $this->averageDuration = $avg_duration;
        }
    }

    function generatePlanID()
    {
        connectToDB();
        global $db_conn;

        $result = executePlainSQL("SELECT MAX(planID) From Itineraries");
        $maxPlanIDSF = 0;
        while (($row = oci_fetch_row($result)) != false) {
            $maxPlanIDSF = $row[0];
        }

        if ($maxPlanIDSF == 0) {
            $maxPlanIDSF = 1;
        } else {
            $maxPlanIDSF = $maxPlanIDSF + 1;
        }

        disconnectFromDB();
        return $maxPlanIDSF;
    }

    function generateCurrentDate()
    {
        return date("Y-m-d");
    }

    function handleUpdateRequest()
    {
        global $db_conn;

        // Getting user inputs
        $email = $_POST['update_emailCheck'];
        $planID = $_POST['update_planID'];
        $input_newDestID = $_POST['update_destID'];
        $input_newDuration = $_POST['update_duration'];

        // Checks for valid plan ID
        $planIDCheckResult = 0;
        $planIDCheck = executePlainSQL("SELECT COUNT(*) FROM Itineraries WHERE planID='" . $planID . "'");

        if (($row = oci_fetch_row($planIDCheck)) != false) {
            $planIDCheckResult = $row[0];
        }

        // Getting the user ID for the selected itinerary
        $userID = "";
        $userIDQueryResult = executePlainSQL("SELECT userID FROM Itineraries WHERE planID='" . $planID . "'");

        if (($row = oci_fetch_row($userIDQueryResult)) != false) {
            $userID = $row[0];
        }

        // Checks for valid user ID and email combination for the selected itinerary
        $userIDEmailCheckResult = 0;
        $userIDEmailCheck = executePlainSQL("SELECT COUNT(*) FROM Users WHERE userID='" . $userID . "' AND email='" . $email . "'");

        if (($row = oci_fetch_row($userIDEmailCheck)) != false) {
            $userIDEmailCheckResult = $row[0];
        }

        // remove all attractions associated with the itinerary if $input_newDestID is not empty

        if ($input_newDestID != "") {
            executePlainSQL("DELETE FROM Include WHERE planID='" . $planID . "'");
        }



        // Prints error messages if any of the checks failed, or successfully updates the itinerary
        if ($planIDCheckResult < 1) {
            echo "The plan ID does not exist. Please try again.";
        } else if ($userIDEmailCheckResult < 1) {
            echo "The email does not match with the user ID of the itinerary with the entered plan ID. Please try again.";
        } else {
            executePlainSQL("UPDATE Itineraries SET destID='" . $input_newDestID . "', duration='" . $input_newDuration . "' WHERE planID='" . $planID . "'");
            echo "Update successful. Please refresh table to see changes.";
        }

        OCICommit($db_conn);
    }

    //             function handleResetRequest() {
    //                 global $db_conn;
    //                 // Drop old table
    //                 executePlainSQL("DROP TABLE demoTable");
    //
    //                 // Create new table
    //                 echo "<br> creating new table <br>";
    //                 executePlainSQL("CREATE TABLE demoTable (id int PRIMARY KEY, name char(30))");
    //                 OCICommit($db_conn);
    //             }

    function handleInsertStep1Request()
    {
        global $db_conn;

        // Makes the planID and dateCreated
        $newPlanID = generatePlanID();
        $newCreatedDate = generateCurrentDate();
        // Getting the values from user and insert data into the table
        $tuple = array(
            ":planID" => $newPlanID,
            ":duration" => $_POST['insert_duration'],
            ":dateCreated" => $newCreatedDate,
            ":destID" => $_POST['insert_destID'],
            ":userID" => $_POST['insert_userID']
        );

        $alltuples = array(
            $tuple
        );

        // Getting user input values for additional checks
        $input_userID = $_POST['insert_userID'];
        $input_email = $_POST['insert_emailCheck'];
        $input_destID = $_POST['insert_destID'];

        // Checking for valid user ID
        $userIDCheck = executePlainSQL("SELECT COUNT(*) FROM Users WHERE userID='" . $input_userID . "'");
        $userIDCheckResult = 0;

        if (($row = oci_fetch_row($userIDCheck)) != false) {
            $userIDCheckResult = $row[0];
        }

        // Checking for valid user ID and email combination
        $userIDEmailCheck = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $input_email . "' AND userID='" . $input_userID . "'");
        $userIDEmailCheckResult = 0;

        if (($row = oci_fetch_row($userIDEmailCheck)) != false) {
            $userIDEmailCheckResult = $row[0];
        }

        // Checking for valid destination ID
        $destIDCheck = executePlainSQL("SELECT COUNT(*) FROM Destinations WHERE destID='" . $input_destID . "'");
        $destIDCheckResult = 0;

        if (($row = oci_fetch_row($destIDCheck)) != false) {
            $destIDCheckResult = $row[0];
        }

        // Prints error messages if any of the checks failed, or successfully inserts the tuple
        if ($userIDCheckResult < 1) {
            echo "This user ID does not exist. Please try again.";
        } else if ($userIDEmailCheckResult < 1) {
            echo "The email does not match with the entered user ID. Please try again.";
        } else if ($destIDCheckResult < 1) {
            echo "This destination ID does not exist. Please try again.";
        } else {
            executeBoundSQL("INSERT INTO Itineraries VALUES (:planID, :duration, :dateCreated, :destID, :userID)", $alltuples);
            echo ("Created new itinerary for user" . $_POST['insert_userID'] . " with plan ID " . $newPlanID . " on " . $newCreatedDate . ". Destination ID is " . $_POST['insert_destID'] . " with duration of " . $_POST['insert_duration'] . " days. Please refresh the page.");
        }

        OCICommit($db_conn);
    }


    function handleInsertStep2Request()
    {
        global $db_conn;

        // Getting the values from user and insert data into the table
        $tuple = array(
            ":attractionID" => $_POST['insert2_attractionID'],
            ":planID" => $_POST['insert2_planID'],
            ":visitDate" => $_POST['insert2_visitDate']
        );

        $alltuples = array(
            $tuple
        );

        // Getting user input values for additional checks
        $input_planID = $_POST['insert2_planID'];
        $input_attractionID = $_POST['insert2_attractionID'];
        $input_userID = $_POST['insert2_userID'];
        $input_email = $_POST['insert2_emailCheck'];

        // Checking for valid plan ID
        $planIDCheck = executePlainSQL("SELECT COUNT(*) FROM Itineraries WHERE planID='" . $input_planID . "'");
        $planIDCheckResult = 0;
        if (($row = oci_fetch_row($planIDCheck)) != false) {
            $planIDCheckResult = $row[0];
        }

        // Checking for valid attraction ID
        $attractionIDCheck = executePlainSQL("SELECT COUNT(*) FROM Attractions WHERE attractionID='" . $input_attractionID . "'");
        $attractionIDCheckResult = 0;
        if (($row = oci_fetch_row($attractionIDCheck)) != false) {
            $attractionIDCheckResult = $row[0];
        }

        // Obtaining the destination ID based on the plan ID
        $destIDCheck = executePlainSQL("SELECT destID FROM Itineraries WHERE planID='" . $input_planID . "'");
        $destIDCheckResult = "";
        if (($row = oci_fetch_row($destIDCheck)) != false) {
            $destIDCheckResult = $row[0];
        }

        // Checking if the destination ID and attraction ID combination is valid
        $destIDAttractionIDCheck = executePlainSQL("SELECT COUNT(*) FROM Attractions WHERE attractionID='" . $input_attractionID . "' AND destID='" . $destIDCheckResult . "'");
        $destIDAttractionIDCheckResult = 0;
        if (($row = oci_fetch_row($destIDAttractionIDCheck)) != false) {
            $destIDAttractionIDCheckResult = $row[0];
        }

        // Checking for valid user ID
        $userIDCheck = executePlainSQL("SELECT COUNT(*) FROM Users WHERE userID='" . $input_userID . "'");
        $userIDCheckResult = 0;
        if (($row = oci_fetch_row($userIDCheck)) != false) {
            $userIDCheckResult = $row[0];
        }

        // Checking for valid user ID and email combination
        $userIDEmailCheck = executePlainSQL("SELECT COUNT(*) FROM Users WHERE email='" . $input_email . "' AND userID='" . $input_userID . "'");
        $userIDEmailCheckResult = 0;
        if (($row = oci_fetch_row($userIDEmailCheck)) != false) {
            $userIDEmailCheckResult = $row[0];
        }

        // Prints error messages if any of the checks failed, or successfully inserts the tuple
        if ($userIDCheckResult < 1) {
            echo "This user ID does not exist. Please try again.";
        } else if ($userIDEmailCheckResult < 1) {
            echo "The email does not match with the entered user ID. Please try again.";
        } else if ($planIDCheckResult < 1) {
            echo "This plan ID does not exist. Please try again.";
        } else if ($destIDCheckResult == "") {
            echo "Error encountered. Destination ID is invalid or cannot be obtained. Please try again.";
        } else if ($attractionIDCheckResult < 1) {
            echo "This attraction ID does not exist. Please try again.";
        } else if ($destIDAttractionIDCheckResult < 1) {
            echo "The attraction with the entered attraction ID does not exist at the destination associated with your plan ID. Please try again.";
        } else {
            executeBoundSQL("INSERT INTO Include VALUES (:attractionID, :planID, :visitDate)", $alltuples);
            echo ("Added attraction with attraction ID " . $_POST['insert2_attractionID'] . " to itinerary with plan ID " . $_POST['insert2_planID'] . " and destination ID " . $destIDCheckResult . " for user " . $_POST['insert2_userID'] . ". Please refresh the page.");
        }

        OCICommit($db_conn);
    }

    //             function handleCountRequest() {
    //                 global $db_conn;
    //
    //                 $result = executePlainSQL("SELECT Count(*) FROM demoTable");
    //
    //                 if (($row = oci_fetch_row($result)) != false) {
    //                     echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
    //                 }
    //             }

    function handleFilterQueryRequestt()
    {
        connectToDB();
        global $db_conn;

        // Getting user inputs
        $planID = $_GET['get_planID'];
        $duration = $_GET['get_duration'];
        $destID = $_GET['get_destID'];
        $userID = $_GET['get_userID'];
        $dateCreated = $_GET['get_dateCreated'];

        // get the dropdown values
        $dropdown1 = $_GET['dropdown1'];
        $dropdown2 = $_GET['dropdown2'];
        $dropdown3 = $_GET['dropdown3'];
        $dropdown4 = $_GET['dropdown4'];

        $result = executePlainSQL("SELECT * FROM Itineraries WHERE planID='" . $planID . "' " . $dropdown1 . " duration='" . $duration . "' " . $dropdown2 . " destID='" . $destID . "' " . $dropdown3 . " userID='" . $userID . "' " . $dropdown4 . " dateCreated='" . $dateCreated . "'");
        $data = array();

        while (($row = oci_fetch_row($result)) != false) {
            $data[] = new Itinerary($row[0], $row[1], $row[2], $row[3], $row[4]);
        }

        if (count($data) > 0) {
            displayItinerary($data);
        } else {
            echo "None of the itinerary meet your requirements! To reset, refresh the page.";
        }
    }

    function handleAggregateHavingRequest()
    {
        connectToDB();
        global $db_conn;

        // Getting user inputs
        $minCount = $_GET['aggregate_minCount'];
        $maxDuration = $_GET['aggregate_maxDuration'];

        $result = executePlainSQL("SELECT destID, AVG(duration) FROM Itineraries WHERE duration <= '" . $maxDuration . "' GROUP BY destID HAVING COUNT(*) >= '" . $minCount . "'");

        $data = array();

        while (($row = oci_fetch_row($result)) != false) {
            for ($i = 0; $i < count($row); $i++) {
                // echo "hi". "<br>";
                // echo "his" . $row[$i] . "<br>";
            }
            $data[] = new AggregatedItinerary($row[0], $row[1]);
        }

        if (count($data) > 0) {
            displayAggregatedItineraries($data);
        } else {
            echo "None of the itinerary meet your requirements! To reset, refresh the page.";
        }

        disconnectFromDB();
    }


    function handleDisplayItineraries()
    {
        connectToDB();
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM Itineraries I");
        $data = array();

        while (($row = oci_fetch_row($result)) != false) {
            for ($i = 0; $i < count($row); $i++) {
                // echo "hi". "<br>";
                // echo "his" . $row[$i] . "<br>";
            }
            $data[] = new Itinerary($row[0], $row[1], $row[2], $row[3], $row[4]);
        }
        displayItinerary($data);
        disconnectFromDB();
    }

    function displayItinerary($data)
    {
        echo "<tr>";
        echo "<td> Plan ID</td>";
        echo "<td> Duration (Days)</td>";
        echo "<td> Destination ID</td>";
        echo "<td> User ID</td>";
        echo "<td> Date Created</td>";
        echo "</tr>";
        foreach ($data as $itin) {
            echo "<tr>";
            // foreach ($user as $attribute) {
            //     echo "<td>" . $attribute . "</td>";
            // }
            echo "<td><a href='view-itinerary.php?planID=" . $itin->planID . "'>" . $itin->planID . "</a></td>";
            // echo "<td>" . $itin->planID . "</td>";
            echo "<td>" . $itin->duration . "</td>";
            echo "<td>" . $itin->destID . "</td>";
            echo "<td>" . $itin->userID . "</td>";
            echo "<td>" . $itin->dateCreated . "</td>";
            echo "</tr>";
        }
    }

    function displayAggregatedItineraries($data)
    {
        echo "<tr>";
        echo "<td> Destination ID</td>";
        echo "<td> Average Duration (Days)</td>";
        echo "</tr>";
        foreach ($data as $itinGroup) {
            echo "<tr>";
            echo "<td>" . $itinGroup->destID . "</td>";
            echo "<td>" . round($itinGroup->averageDuration, 2) . "</td>";
            echo "</tr>";
        }
    }

    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('resetTablesRequest', $_POST)) {
                handleResetRequest();
            } else if (array_key_exists('updateQueryRequest', $_POST)) {
                handleUpdateRequest();
            } else if (array_key_exists('insertQueryStep1Request', $_POST)) {
                handleInsertStep1Request();
            } else if (array_key_exists('insertQueryStep2Request', $_POST)) {
                handleInsertStep2Request();
            }

            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    //             function handleGETRequest() {
    //                 if (connectToDB()) {
    //                     if (array_key_exists('aggregateHavingQueryRequest', $_GET)) {
    //                         handleAggregateHavingRequest();
    //                     }
    //                     disconnectFromDB();
    //                 }
    //             }

    if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
        handlePOSTRequest();
    } // else if (isset($_GET['aggregateHavingSubmit'])) {
    //  handleGETRequest();
    //}
    ?>

</body>

</html>
