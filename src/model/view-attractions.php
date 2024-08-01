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

        <h2>Welcome to Wabi-Tabi! Here are our current attractions!<h2>
        <nav>
            <a href="home.php">Home</a>
            <a href="view-users.php">Users</a>
            <a href="view-destination.php">Destinations</a>
            <a href="view-attractions.php">Attractions</a>
            <a href="view-itineraries.php">Itineraries</a>
            <a href="admin.php">Admin</a>
        </nav>


        <hr/>

        <h3>Our Current Attractions!</h3>

        <table>
            <?php
                if (isset($_GET['displayQueryRequest']) && ($_GET['displayTableInput'] != null)){
                    handleSearchTable();
                } else {
                    handleDisplayTable();
                }
            ?>
        </table>

        <hr />

        <h2>Filter Attractions by Destination ID or Name</h2>

        <form method='GET' action='view-attractions.php'>
            <input type='hidden' id='displayQueryRequest' name='displayQueryRequest'>
            Destination ID or City: <input type='text' name='displayTableInput' pattern="[a-zA-Z0-9]+"> <br /><br />
            <select id='dropdown' name='SearchType'>
            <option value='DestinationID'>Search by Destination ID</option>
            <option value='DestinationName'>Search by Destination City</option>
            <input type='submit' value='Search' name='removeSubmit'></p>
        </form>

        <hr />

        <h2>Calculate Total Number of Reviews of Each Attraction</h2>
        <h4>Note: Attractions without reviews will not be shown.</h4>
          
        <form method='GET' action='view-attractions.php'>
            <input type='hidden' id='aggregateWithGroupByQueryRequest' name='aggregateWithGroupByQueryRequest'>
            <input type='submit' value='Calculate' name='aggregateGroupBySubmit'></p>
        </form>

        <br/>

        <table>
            <?php
                if (isset($_GET['aggregateWithGroupByQueryRequest'])){
                    handleAggregationRequest();
                }
            ?>
        </table>

        <hr/>

        <h2>Calculate Average Rating of Attractions Whose Lowest Rating is At Least the Overall Average Rating of All Attractions</h2>

        <form method='GET' action='view-attractions.php'>
            <input type='hidden' id='nestedQueryRequest' name='nestedQueryRequest'>
            <input type='submit' value='Calculate' name='nestedAggregateSubmit'></p>
        </form>

        <br/>

        <table>
            <?php
                if (isset($_GET['nestedQueryRequest'])){
                    handleNestedAggregationRequest();
                }
            ?>
        </table>

        <hr/>

        <?php
            //this tells the system that it's no longer just parsing html; it's now parsing PHP

            $success = True; //keep track of errors so it redirects the page only if there are no errors
            $db_conn = NULL; // edit the login credentials in connectToDB()
            $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())


            function handleUpdateRequest() {
                global $db_conn;

                // $userID = $_POST['userID'];
                // $userID = $_POST['update_userID'];
                $email = $_POST['update_email'];
                $key = $_POST['update_key'];
                $value = $_POST['update_value'];
                echo $email . $key . $value;
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

            class AttractionHeader {
                public $attractionID;
                public $name;
                public $description;
                public $destinationID;
                public $destinationCountry;
                public $destinationCity;


                public function __construct($attractionID, $name, $description, $destinationID, $destinationCity, $destinationCountry) {
                    $this->attractionID = $attractionID;
                    $this->name = $name;
                    $this->description = $description;
                    $this->destinationID = $destinationID;
                    $this->destinationCountry = $destinationCountry;
                    $this->destinationCity = $destinationCity;
                }
            }

            class AggregatedAttractionNumberReview {
                public $attractionID;
                public $name;
                public $address;
                public $description;
                public $totalNumberOfReviews;

                public function __construct($attractionID, $name, $address, $description, $totalNumberOfReviews) {
                    $this->attractionID = $attractionID;
                    $this->name = $name;
                    $this->address = $address;
                    $this->description = $description;
                    $this->totalNumberOfReviews = $totalNumberOfReviews;
                }
            }

            class AggregatedAttractionReviewScore {
                public $destID;
                public $attractionID;
                public $name;
                public $address;
                public $description;
                public $averageRating;

                public function __construct($destID, $attractionID, $name, $address, $description, $averageRating) {
                    $this->destID = $destID;
                    $this->attractionID = $attractionID;
                    $this->name = $name;
                    $this->address = $address;
                    $this->description = $description;
                    $this->averageRating = $averageRating;
                }
            }

            function handleDisplayTable() {
                connectToDB();
                global $db_conn;

                $result = executePlainSQL("SELECT Attractions.*, Destinations.cityName, Destinations.countryName  FROM Attractions
                JOIN Destinations ON Attractions.destID = Destinations.destID");
                $data = array();

                while (($row = oci_fetch_row($result)) != false) {
                    // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                    // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
                    for ($i = 0; $i < count($row); $i++) {
                        // echo "hi". "<br>";
                        // echo "his" . $row[$i] . "<br>";
                    }
                    $data[] = new AttractionHeader($row[0], $row[1], $row[2], $row[4], $row[5], $row[6]);
                }
                displayTable($data);
                disconnectFromDB();
            }

            function handleSearchTable() {
                connectToDB();
                global $db_conn;
                $searchValue = $_GET['displayTableInput'];
                $searchType = $_GET['SearchType'];
                if (is_numeric($searchValue) && ($searchType == "DestinationName")) {
                    echo "Please enter a valid city name.";
                } else if (!is_numeric($searchValue) && ($searchType == "DestinationID")){
                    echo "Please enter a valid destination ID";
                } else {
                    $result = "Error in handleSearchTable()";

                    if ($searchType == "DestinationID") {
                        $result = executePlainSQL("SELECT Attractions.*, Destinations.cityName, Destinations.countryName  FROM Attractions
                        JOIN Destinations ON Attractions.destID = Destinations.destID
                        WHERE Destinations.destID = '" . $searchValue . "'"
                        );
                    } else if ($searchType == "DestinationName") {
                        $result = executePlainSQL("SELECT Attractions.*, Destinations.cityName, Destinations.countryName  FROM Attractions
                        JOIN Destinations ON Attractions.destID = Destinations.destID
                        WHERE Destinations.cityName = '" . $searchValue . "'"
                        );
                    }

                    $data = array();

                    while (($row = oci_fetch_row($result)) != false) {
                        // echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                        // echo "<br> The number of tuples in demoTable: " . $row . "<br>";
                        for ($i = 0; $i < count($row); $i++) {
                            // echo "hi". "<br>";
                            // echo "his" . $row[$i] . "<br>";
                        }
                        $data[] = new AttractionHeader($row[0], $row[1], $row[2], $row[4], $row[5], $row[6]);
                    }
                    if (count($data) > 0) {
                        displayTable($data);
                    } else {
                        echo "No Attractions found! To reset, click on the Search without entering anything.";
                    }

                }


                disconnectFromDB();
            }

            function displayTable($data) {
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
            }

            function handleLoadRequest() {
                global $db_conn;

            }

            function displayUsers() {
                global $db_conn;

                $result = executePlainSQL("SELECT Name FROM Users");
                if (($row = oci_fetch_row($result)) != false) {
                    echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
                    echo "<br> The number of tuples in demoTable: " . $row . "<br>";
                    for ($i = 0; $i < count($row); $i++) {
                        // echo "hi". "<br>";
                        echo "his" . $row[$i] . "<br>";
                    }
                }
            }

            function handleInitializeRequest() {
                global $db_conn;

                executePlainSQL("DROP Table Users");
            }

            function handleAggregationRequest() {
                connectToDB();
                global $db_conn;

                $result = executePlainSQL("SELECT A.attractionID, A.name, A.description, A.address, SUM(CR.numberOfReview) AS totalNumberOfReviews
                FROM (SELECT TR.attractionID, COUNT(*) AS numberOfReview FROM TextReviews TR GROUP BY TR.attractionID
                      UNION ALL
                      SELECT PR.attractionID, COUNT(*) AS numberOfReview FROM PhotoReviews PR GROUP BY PR.attractionID) CR, Attractions A
                WHERE A.attractionID = CR.attractionID
                GROUP BY A.attractionID, A.name, A.description, A.address");

                $data = array();

                while (($row = oci_fetch_row($result)) != false) {
                    $data[] = new AggregatedAttractionNumberReview($row[0], $row[1], $row[2], $row[3], $row[4]);
                }

                displayAggregatedGroupByTable($data);

                disconnectFromDB();
            }

            function displayAggregatedGroupByTable($data) {
                echo "<tr>";
                echo "<td> Attraction ID</td>";
                echo "<td> Name</td>";
                echo "<td> Address </td>";
                echo "<td> Description</td>";
                echo "<td> Total Number of Reviews</td>";
                echo "</tr>";
                foreach ($data as $curr) {
                    echo "<tr>";
                    echo "<td>" . $curr->attractionID . "</td>";
                    // echo "<td><a href='view-attraction.php?attractionID=" . $header->attractionID . "'>" . $header->name . "</a></td>";
                    echo "<td>" . $curr->name . "</td>";
                    echo "<td>" . $curr->address . "</td>";
                    echo "<td>" . $curr->description . "</td>";
                    echo "<td>" . $curr->totalNumberOfReviews . "</td>";
                    echo "</tr>";
                }
            }

            function handleNestedAggregationRequest() {
                connectToDB();
                global $db_conn;

                $result = executePlainSQL("SELECT A.destID, A.attractionID, A.name, A.address, A.description, AVG(CR.rating) AS averageRating
                FROM (SELECT TR.attractionID, TR.rating FROM TextReviews TR
                      UNION ALL
                      SELECT PR.attractionID, PR.rating FROM PhotoReviews PR) CR, Attractions A
                WHERE A.attractionID = CR.attractionID
                GROUP BY A.destID, A.attractionID, A.name, A.address, A.description
                HAVING MIN(CR.rating) >= (SELECT AVG(CR2.rating)
                                          FROM (SELECT TR2.attractionID, TR2.rating
                                               	FROM TextReviews TR2
                                               	UNION ALL
                                               	SELECT PR2.attractionID, PR2.rating
                                               	FROM PhotoReviews PR2) CR2)");

                $data = array();

                while (($row = oci_fetch_row($result)) != false) {
                    $data[] = new AggregatedAttractionReviewScore($row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
                }

                displayNestedAggregatedGroupByTable($data);

                disconnectFromDB();
            }

            function displayNestedAggregatedGroupByTable($data) {
                echo "<tr>";
                echo "<td> Destination ID</td>";
                echo "<td> Attraction ID</td>";
                echo "<td> Name</td>";
                echo "<td> Address </td>";
                echo "<td> Description</td>";
                echo "<td> Average Review Rating</td>";
                echo "</tr>";
                foreach ($data as $curr) {
                    echo "<tr>";
                    echo "<td>" . $curr->destID . "</td>";
                    echo "<td>" . $curr->attractionID . "</td>";
                    // echo "<td><a href='view-attraction.php?attractionID=" . $header->attractionID . "'>" . $header->name . "</a></td>";
                    echo "<td>" . $curr->name . "</td>";
                    echo "<td>" . $curr->address . "</td>";
                    echo "<td>" . $curr->description . "</td>";
                    echo "<td>" . round($curr->averageRating , 2) . "</td>";
                    echo "</tr>";
                }
            }



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

            // These are the given oracle-test sample provided by CPSC 304

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
                    } // else if (array_key_exists('aggregateGroupBySubmit', $_GET)) {
                    //    handleAggregationRequest();
                    // } else if (array_key_exists('nestedAggregateSubmit', $_GET)) {
                     //   handleNestedAggregationRequest();
                    // }

                    disconnectFromDB();
                }
            }

            if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])
            || isset($_POST['removeSubmit'])) {
                handlePOSTRequest();
            } else if (isset($_GET['countTupleRequest'])) {
            // || isset($_GET['aggregateWithGroupByQueryRequest'])
            //|| isset($_GET['nestedQueryRequest'])
                handleGETRequest();
            } else if (isset($_GET['RefreshTableRequest'])) {
                handleDisplayTable();
            }
        ?>
    </body>
</html>
