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
        <h2>Welcome to Wabi-Tabi! Here are all the available destinations for you to look at!</h2>
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
        <hr/>

        <h3>Destinations</h3>

        <table>
            <?php
                handleDisplayDestinations();
            ?>
        </table>


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

                $old_name = $_POST['oldName'];
                $new_name = $_POST['newName'];

                // you need the wrap the old name and new name values with single quotations
                executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
                OCICommit($db_conn);
            }

            function handleResetRequest() {
                global $db_conn;
                // Drop old table
                executePlainSQL("DROP TABLE demoTable");

                // Create new table
                echo "<br> creating new table <br>";
                executePlainSQL("CREATE TABLE demoTable (id int PRIMARY KEY, name char(30))");
                OCICommit($db_conn);
            }

            function handleInsertRequest() {
                global $db_conn;

                //Getting the values from user and insert data into the table
                $tuple = array (
                    ":bind1" => $_POST['insNo'],
                    ":bind2" => $_POST['insName']
                );

                $alltuples = array (
                    $tuple
                );

                executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);
                OCICommit($db_conn);
            }

            function handleCountRequest() {
                global $db_conn;

                $result = executePlainSQL("SELECT Count(*) FROM demoTable");

                if (($row = oci_fetch_row($result)) != false) {
                    echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                }
            }

            class Destination {
                public $destID;
                public $cityName;
                public $countryName;
                public $continent;
                public $climate;
                public $currency;
                public $codeISO;
                public $description;

                public function __construct($destID, $cityName, $countryName, $continent, $climate, $currency, $codeISO, $description) {
                    $this->destID = $destID;
                    $this->cityName = $cityName;
                    $this->countryName = $countryName;
                    $this->continent = $continent;
                    $this->climate = $climate;
                    $this->currency = $currency;
                    $this->codeISO = $codeISO;
                    $this->description = $description;
                }
            }

            function handleDisplayDestinations() {
                connectToDB();
                global $db_conn;

                $result = executePlainSQL("SELECT D.destID, D.cityName, D.countryName, L.continent, E.climate, L.currency, Lan.codeISO, D.description
                FROM Destinations D, Environment E, Locale L, Languages Lan
                WHERE D.countryName = L.countryName AND D.countryName = E.countryName AND D.cityName = E.cityName AND Lan.codeISO = E.codeISO");
                $data = array();

                while (($row = oci_fetch_row($result)) != false) {
                    for ($i = 0; $i < count($row); $i++) {
                        // echo "hi". "<br>";
                        // echo "his" . $row[$i] . "<br>";
                    }
                    $data[] = new Destination($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
                }
                displayDestination($data);
                disconnectFromDB();
            }

            function displayDestination($data) {
                echo "<tr>";
                echo "<td> Destination ID</td>";
                echo "<td> City Name</td>";
                echo "<td> Country Name</td>";
                echo "<td> Continent</td>";
                echo "<td> Climate</td>";
                echo "<td> Currency</td>";
                echo "<td> Language Code ISO</td>";
                echo "<td> Description</td>";
                echo "</tr>";
                foreach ($data as $dest) {
                    echo "<tr>";
                    // foreach ($user as $attribute) {
                    //     echo "<td>" . $attribute . "</td>";
                    // }
                    // echo "<td><a href='view-destination.php?destID=" . $dest->destID . "'>" . $dest->destID . "</a></td>";
                    echo "<td>" . $dest->destID . "</td>";
                    echo "<td>" . $dest->cityName . "</td>";
                    echo "<td>" . $dest->countryName . "</td>";
                    echo "<td>" . $dest->continent . "</td>";
                    echo "<td>" . $dest->climate . "</td>";
                    echo "<td>" . $dest->currency . "</td>";
                    echo "<td>" . $dest->codeISO . "</td>";
                    echo "<td>" . $dest->description . "</td>";
                    echo "</tr>";
                }
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

            if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
                handlePOSTRequest();
            } else if (isset($_GET['countTupleRequest'])) {
                handleGETRequest();
            }
            ?>

    </body>

</html>