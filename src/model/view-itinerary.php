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

        <hr/>

        <table>
            <?php
                handleDisplayItineraryWithAttractions();
            ?>
        </table>
        <hr/>

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

            class ItineraryWithAttraction {
                public $planID;
                public $destinationID;
                public $destinationCity;
                public $destinationCountry;
                public $attractionID;
                public $attractionName;
                public $attractionDescription;
                public $attractionAddress;


                public function __construct($planID, $destinationID, $destinationCity, $destinationCountry, $attractionID, $attractionName, $attractionDescription, $attractionAddress) {
                    $this->planID = $planID;
                    $this->destinationID = $destinationID;
                    $this->destinationCity = $destinationCity;
                    $this->destinationCountry = $destinationCountry;
                    $this->attractionID = $attractionID;
                    $this->attractionName = $attractionName;
                    $this->attractionDescription = $attractionDescription;
                    $this->attractionAddress = $attractionAddress;
                }
            }

            function handleDisplayItineraryWithAttractions() {
                connectToDB();
                global $db_conn;

                $result = executePlainSQL("SELECT It.planID, A.destID, D.cityName, D.countryName, I.attractionID, A.name, A.description, A.address
                FROM Attractions A, Itineraries It, Destinations D, Include I
                WHERE I.planID = " . $_GET['planID'] . " AND I.planID = It.planID AND I.attractionID = A.attractionID AND It.destID = D.destID");

                $data = array();

                while (($row = oci_fetch_row($result)) != false) {
                    // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                    // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
                    for ($i = 0; $i < count($row); $i++) {
                        // echo "hi". "<br>";
                        // echo "his" . $row[$i] . "<br>";
                    }
                    $data[] = new ItineraryWithAttraction($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
                }
                displayItineraryWithAttractions($data);
                disconnectFromDB();
            }

            function displayItineraryWithAttractions($data) {
                // Should only have one element.
                echo "<tr>";
                echo "<td> Plan ID </td>";
                echo "<td> Destination ID </td>";
                echo "<td> City </td>";
                echo "<td> Country </td>";
                echo "<td> Attraction ID </td>";
                echo "<td> Attraction Name </td>";
                echo "<td> Attraction Description </td>";
                echo "<td> Attraction Address </td>";
                echo "</tr>";
                foreach ($data as $itinAtt) {
                    echo "<tr>";
                    // foreach ($user as $attribute) {
                    //     echo "<td>" . $attribute . "</td>";
                    // }
                    echo "<td>" . $itinAtt->planID . "</td>";
                    echo "<td>" . $itinAtt->destinationID . "</td>";
                    echo "<td>" . $itinAtt->destinationCity . "</td>";
                    echo "<td>" . $itinAtt->destinationCountry . "</td>";
                    echo "<td>" . $itinAtt->attractionID . "</td>";
                    echo "<td>" . $itinAtt->attractionName . "</td>";
                    echo "<td>" . $itinAtt->attractionDescription . "</td>";
                    echo "<td>" . $itinAtt->attractionAddress . "</td>";
                    echo "</tr>";
                }
                echo "<br>";
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
