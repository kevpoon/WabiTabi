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

<hr>
<h2> We are currently on the Homepage! </h2>
<div class="tenor-gif-embed" data-postid="26512259" data-share-method="host" data-aspect-ratio="1.39738" data-width="20%"><a href="https://tenor.com/view/genshin-impact-lumine-run-lumine-lumine-chibi-genshin-gif-26512259">Genshin Impact Lumine Run GIF</a>from <a href="https://tenor.com/search/genshin+impact-gifs">Genshin Impact GIFs</a></div> <script type="text/javascript" async src="https://tenor.com/embed.js"></script>
  <p >Wabi-Tabi is a travel and tourism management application, aiming to serve as a cohesive platform that enriches travel planning for its users. Specifically, our application seeks to empower users with information and tools for crafting, sharing, and exploring travel itineraries, attractions, and user-generated content, including reviews and photos. Our database primarily models crucial elements for efficient travel planning and community engagement, such as Users, Itineraries, Reviews, PhotoReviews, Destinations, Attractions, Photos, Languages, and Dialects.</p>
  <hr>
  <h2>View Table Attributes (Demo)</h2>
  
  <div>
  <form method='GET'>
  <?php
  displayTablesDropdown();
  ?>
  <input type='submit' value='Choose table' name='submitTable'>
  </form>
  </div>
  <hr>
  <div>
  <?php
  if (isset($_GET['table_name'])) {
    displayAttributesDropdown($_GET['table_name']);
  }
  
  if (isset($_GET['column_name']) && isset($_GET['table_name'])) {
    displayProjectionTable($_GET['table_name'], $_GET['column_name']);
  }
  
  function displayTablesDropdown() {
    $db_conn = connectToDB();
    
    $result = executePlainSQL("SELECT table_name FROM user_tables", $db_conn);
    echo "Select a table to view:  ";
    echo '<select name="table_name">';
    while (($row = oci_fetch_row($result)) != false) {
      echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
    }
    echo '</select>';
  }
  
  function displayAttributesDropdown($table) {
    $db_conn = connectToDB();
    
    echo "<h3>You have selected table: " . $table . "</h3>";
    echo "Select an attribute to view:  ";
    $result = executePlainSQL("SELECT column_name FROM user_tab_columns WHERE table_name = '" . $table . "'", $db_conn);
    echo '<form method="GET">';
    echo '<input type="hidden" name="table_name" value="' . $table . '">';
    echo '<select name="column_name">';
    while (($row = oci_fetch_row($result)) != false) {
      echo '<option value="' . $row[0] . '">' . $row[0] . '</option>';
    }
    echo '</select>';
    echo ' ';
    echo "<input type='submit' value='View' name='submitColumn'>";
    echo '</form>';
  }
  
  function displayProjectionTable($table, $column) {
    $db_conn = connectToDB();
    
    echo "<hr>";
    echo "<h3>You have selected attribute: " . $column . "</h3>";
    $result = executePlainSQL("SELECT " . $column . " FROM " . $table . "");
    echo "<table>";
    while (($row = oci_fetch_row($result)) != false) {
      echo "<tr><td>" . $row[0] . "</td></tr>";
    }
    echo "</table>";
  }
  
  ?>
  </div>
  
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
  
  </html>
  
  
