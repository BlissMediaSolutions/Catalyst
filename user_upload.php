<?php

  //check if any directives were entered after the program name
  if (sizeof($argv) <= 1)
  {
    fwrite(STDOUT, "Error in Command.  Missing Directive instruction\n");
    exit(helpInstructions());
  }

  //Check if the XML file for settings exist, if not create it, then load the contents into a new object;
  if (checkFileExists("dbconnect.xml") == false)
  {
    createXMLFile();
  }
  $xml = new stdClass();
  $xml = simplexml_load_file("dbconnect.xml");

  //Handle Bash directive arguments
  switch ($argv[1])
  {
    case "--file":
      if ((empty($argv[2])) OR (checkFileExists($argv[2]) == false))
      {
        fwrite(STDOUT, "Unknown command ". $argv[1]." File name missing or File Not Found\n");
      } elseif (empty($argv[3])) {
        $data = readCSVFile($argv[2]);
        set_error_handler("exception_db_handler");
        try {
          $db = new Dbase();
          $pgsql = $db->dbConnect($xml->host, $xml->port, $xml->database, $xml->username, $xml->password);
          //if (!empty($db->createTable($pgsql)))
          //{
          //  fwrite(STDOUT, "Users Table Created Successfully\n");
            //pg_close($pgsql);
          //}
          $data = readCSVFile($argv[2]);                                            //Read the CSV file
          if ($db->insertData($pgsql, $data[0]))
          {
            pg_close($pgsql);
            createErrorIssue($data[1], $data[2]);                                   //create the Error & Issues CSV files.
            fwrite(STDOUT, "Successfully added records to the database\n");
            fwrite(STDOUT, "Check the files Error_log.csv and Issues_log.csv for any problems with the data.\n");
          }
        } catch (Exception $e) {
          pg_close($pgsql);
          fwrite(STDOUT,"Error with Database: ".$e->getMessage()." \n");
        }
      } else {
        $data = readCSVFile($argv[2]);
        createErrorIssue($data[1], $data[2]);
        fwrite(STDOUT, "Successfully performed Dry Run on CSV data.  Check Error_log.csv and Issues_log.csv for any problems with the data.\n");
      }
      break;

    case "--create_table":
      set_error_handler("exception_db_handler");
      try {
        $db = new Dbase();
        $pgsql = $db->dbConnect($xml->host, $xml->port, $xml->database, $xml->username, $xml->password);
        if ($db->checkUserTableExists($pgsql) == false)
        {
          if ($db->createTable($pgsql))
          {
            fwrite(STDOUT, "Users Table Created Successfully\n");
          }
        } else {
          fwrite(STDOUT, "Users Table already Exists\n");
        }
        pg_close($pgsql);
      } catch (Exception $e) {
          fwrite(STDOUT,"Error Creating Table: ".$e->getMessage()." \n");
      }
      break;

    case "--test_db":
      set_error_handler("exception_db_handler");
      try {
        $db = new Dbase();
        $pgsql = $db->dbConnect($xml->host, $xml->port, $xml->database, $xml->username, $xml->password);
        if ($pgsql)
        {
          fwrite(STDOUT, "Successfully tested connection to database: " . pg_dbname($pgsql)."\n");
          pg_close($pgsql);
        }
      } catch (Exception $e) {
         fwrite(STDOUT,"Error Communicating with Database: ".$e->getMessage()." \n");
      }
      break;

      //Specify the Postgre Directives
    case "-u":
      if (empty($argv[2])) {
        fwrite(STDOUT,"Current Postgre Username: ".$xml->username."\n");
      } else {
        fwrite(STDOUT,modifyXMLFile($xml, $argv[1], $argv[2])."\n");
      }
      break;
    case "-p":
      if (empty($argv[2])) {
        fwrite(STDOUT,"Current Postgre Password: ".$xml->password."\n");
      } else {
        fwrite(STDOUT,modifyXMLFile($xml, $argv[1], $argv[2])."\n");
      }
      break;
    case "-h":
      if (empty($argv[2])) {
        fwrite(STDOUT,"Current Postgre Host: ".$xml->host."\n");
      } else {
        fwrite(STDOUT,modifyXMLFile($xml, $argv[1], $argv[2])."\n");
      }
      break;
    case "-c":
      if (empty($argv[2])) {
        fwrite(STDOUT,"Current Postgre Port: ".$xml->port."\n");
      } else {
        fwrite(STDOUT,modifyXMLFile($xml, $argv[1], $argv[2])."\n");
      }
      break;
    case "-d":
      if (empty($argv[2])) {
        fwrite(STDOUT,"Current Postgre Database: ".$xml->database."\n");
      } else {
        fwrite(STDOUT,modifyXMLFile($xml, $argv[1], $argv[2])."\n");
      }
      break;

    case "--help":
      fwrite(STDOUT,helpInstructions()."\n");
      break;

    default:
      fwrite(STDOUT,"Unknown command:".$argv[1]."  Try the --help directive for assistance \n");
      break;
    }

    //Simple function to check if a specified file exists
    function checkFileExists($fileName)
    {
      if (!file_exists($fileName))
        {
          return false;
        } else {
          return true;
        }
    }

    //function used to modify a given key value in the XML file for the Postgre connection
    function modifyXMLFile($xml, $argu1, $argu2)
    {
      $string = "";
      $xml = simplexml_load_file("dbconnect.xml");
      switch ($argu1)
      {
        case "-u":
          $xml->username = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Username Updated: ".$xml->username;
          break;
        case "-p":
          $xml->password = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Password Updated: ".$xml->password;
          break;
        case "-h":
          $xml->host = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Host Updated: ".$xml->host;
          break;
        case "-c":
          $xml->port = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Port No Updated: ".$xml->port;
          break;
        case "-d":
          $sml->database = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Database Updated: ".$xml->database;
          break;
      }
      return $string;
    }

    //function used to create an empty XML file of the connection elements for Postgre (includes default values)
    function createXMLFile()
    {
      $xmlstr = "<?xml version='1.0' encoding='UTF-8'?><dbconnect></dbconnect>";
      $xmlconn = new SimpleXMLElement($xmlstr);
      $xmlconn->addChild('username','postgres');
      $xmlconn->addChild('password','root');
      $xmlconn->addChild('host','127.0.0.1');
      $xmlconn->addChild('port','5432');
      $xmlconn->addChild('database','postgres');
      $xmlconn->asXML("dbconnect.xml");
    }

    //function used to read data from a CSV file into an array.  The "checkFileExists" function should always be run prior to this, to check the file exists.
    function readCSVFile($fileName)
    {
      $rows = array_map('str_getcsv', file($fileName));
      $head = array_shift($rows);
      $trim_head = array_map('trim', $head);                                            //trim white space from header names

      $csvfile = array();                                                               //array of data to be written to database
      $csvNoDup = array();                                                              //a copy of csvfile array with duplicates removed
      $errlist = array();                                                               //array of data which couldn't be written to database due to invalid email or duplicate email
      $issuelist = array();                                                             //array of data which may need to be corrected in db, due to non-alpha charcters in name/surname
      $x = $y = 0;

      foreach ($rows as $row) {
        $csvfile[] = array_combine($trim_head, $row);
        $csvfile[$x]['name'] = ucfirst(strtolower(trim($csvfile[$x]['name'])));         //convert name to lowercase with first char Upper + trim whitespace
        $csvfile[$x]['surname'] = ucfirst(strtolower(trim($csvfile[$x]['surname'])));   //convert surname to lowercase with first char Upper + trim whitespace
        $csvfile[$x]['email'] = strtolower(trim($csvfile[$x]['email']));                //convert email to lowercase + trim whitespace

        if (!filter_var($csvfile[$x]['email'], FILTER_VALIDATE_EMAIL))
        {
          array_push($errlist, $csvfile[$x]);                                           //add invalid email record (including name/surname) to ErrList Array
          array_splice($csvfile[$x], $x, 1);                                            //remove invalid email record (including name/surname) & reindex CSVFile array
        }
        if (!ctype_alpha($csvfile[$x]['name']) || (!ctype_alpha($csvfile[$x]['surname'])))
        {
          array_push($issuelist, $csvfile[$x]);                                          //name or surname contains non-alpha characters, so add it to the Issues Array
        }
        $x++;
      }
      $data = removeDuplicateValues($csvfile, 'email');
      $csvfile = $data[0];                                                              //csvfile now contains non-duplicate data.

      foreach ($data[1] as $col) {
        array_push($errlist, $data[1][$y]);                                             //Add any duplicates found to the ErrList array which will appear in the error_log csv file.
        $y++;
      }
      return array($csvfile, $errlist, $issuelist);
    }

    //function which removes duplicates from csvfile & returns a duplicate free csv array + error array
    function removeDuplicateValues($array, $key)
    {
      $temparr = $keyarr = $errarr = $errkey = array();
      $i = 0;

      foreach($array as $val) {
        if (!in_array($val[$key], $keyarr)) {
          $keyarr[$i] = $val[$key];
          $temparr[$i] = $val;
        } else {
          $errkey[$i] = $val[$key];
          $errarr[$i] = $val;
        }
        $i++;
      }
      return array(array_values($temparr), array_values($errarr));                        //reindex both arrys & return them as 1 array
    }

    //function which creates CSV file for the Error_log and/or Issue_list
    function createCSV($name, $arr)
    {
      $csv = fopen($name.".csv", "w");
      foreach ($arr as $row)
      {
        fputcsv($csv, $row);
      }
      fclose($csv);
    }

    //function which checks if the error & issues array have values - and if they do, it creates the CSV file.
    function createErrorIssue($errlog, $issuelog)
    {
      if (!empty($errlog))
      {
        createCSV("Error_log", $errlog);
      }
      if (!empty($issuelog))
      {
        createCSV("Issue_log", $issuelog);
      }
    }

    //Simple function to display help instructions
    function helpInstructions()
    {
      $text = "\n     |>>** USER_UPLOAD DIRECTIVES **<<|     \n".
        "--file [csv filename] = this is the name of the CSV file to be parsed into the database\n".
        "--file [csv filename] --dry_run = Peforms a dry run on the csv file, but doesn't actually write any data to the Db\n".
        "--test_db = used to test the current Postgres database connection parameters.\n".
        "--create_table = this will cause the PostgreSQL users table to be built\n".
        "--help = Display theseinstructions for directive usage\n".
        "-u = Display the current PostgreSQL username\n".
        "-u [username] = Sets the PostgreSQL username\n".
        "-p = Display the current PostgreSQL password\n".
        "-p [password] = Sets the PostgreSQL password\n".
        "-h = Display the current PostgreSQL host\n".
        "-h [host address] = Sets the PostgreSQL host\n".
        "-c = Display the current PostgreSQL port\n".
        "-c [port] = Sets the PostgreSQL port number\n".
        "-d = Display the current PostgreSQL database\n".
        "-d [database] = Sets the PostgreSQL database\n";
      fwrite(STDOUT, $text);
    }

    //Custom Exception Error handler for pg_connect, as it wont throw an exception
    function exception_db_handler($errno, $errstr, $errfile, $errline )
    {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    class Dbase
    {
      //function which creates the initial connection to the Postgre Database
      function dbConnect($host, $port, $dbname, $user, $password)
      {
        $pgsql = @pg_connect("host=".$host." port=".$port." dbname=".$dbname." user=".$user." password=".$password);
        if ($pgsql)
        {
          return $pgsql;
        }
      }

      //function to create the 'users' table.
      function createTable($pg)
      {
        $query = "CREATE TABLE IF NOT EXISTS users (
                    email VARCHAR(50) PRIMARY KEY,
                    name VARCHAR(25) NOT NULL,
                    surname VARCHAR(25) NOT NULL)";
        $createtable = pg_query($pg, $query);
        if ($createtable)
        {
          return $createtable;
        }
      }

      //function to check the Users Table exists.  If it does return 'true' otherwise return 'false'
      function checkUserTableExists($pg)
      {
        $query = "SELECT 1 FROM users LIMIT 1";
        try {
          if (@pg_query($pg, $query) != null) {
            return true;
          }
        } catch (Exception $e) {
          return false;
        }
      }

      //function which inserts data to the 'users' table
      function insertData($pg, $csvfile)
      {
        $x = 0;
        foreach ($csvfile as $data)
        {
          $email = pg_escape_string($csvfile[$x]['email']);
          $name = pg_escape_string($csvfile[$x]['name']);
          $surname = pg_escape_string($csvfile[$x]['surname']);
          $query = "INSERT INTO users (email, name, surname) VALUES ('".$email."', '".$name."', '".$surname."')";
          $result = pg_query($pg, $query);
          $x++;
        }
        return $result;
      }
    }  //End Class

  ?>
