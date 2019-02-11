<?php
  //TODO: futher filtering on name/surname to remove non alpha characters.
  //      Database handling

  //Check if the XML file for settings exist, if not create it, then load the contents into a new object;
  if (checkFileExists("dbconnect.xml") == false)
  {
    createXMLFile();
  }
  $xml = new stdClass();
  $xml = simplexml_load_file("dbconnect.xml");

  //check if any directives were entered after the program name
  if (sizeof($argv) <= 1)
  {
    fwrite(STDOUT, "Error Command.  Missing Directive input\n");
    exit(helpInstructions());
  }

  //Handle Bash directive arguments
  switch ($argv[1])
  {
    case "--file":
      //check to see if a filename was added as a directive, and that the file exists
      if ((empty($argv[2])) OR (checkFileExists($argv[2]) == false))
      {
        fwrite(STDOUT, "Unknown command $argv[1].  File name missing or File Not Found\n");
      } else {
        readCSVFile($argv[2]);
      }
      break;

    case "--create_table":
      $query = "CREATE TABLE IF NOT EXISTS users (
                  email VARCHAR(50) PRIMARY KEY,
                  name VARCHAR(25) NOT NULL,
                  surname VARCHAR(25) NOT NULL)";
      set_error_handler("exception_db_handler");
      try {
        $pg = @pg_connect("host=".$xml->host." port=".$xml->port." dbname=".$xml->database." user=".$xml->username." password=".$xml->password);
        $createtable = pg_query($pg, $query);
        if ($createtable) {
          fwrite(STDOUT, "Table Create Successfully\n");
        }
      } catch (Exception $e) {
          fwrite(STDOUT,"ERROR: ".$e->getMessage()." \n");
      }
      pg_close($pg);
      break;

    case "--check-db":
      set_error_handler("exception_db_handler");
      try {
        $pgsql = @pg_connect("host=".$xml->host." port=".$xml->port." dbname=".$xml->database." user=".$xml->username." password=".$xml->password);

        if ($pgsql) {
          fwrite(STDOUT, "Successfully connected to database: " . pg_dbname($pgsql)."\n");
        }
      } catch (Exception $e) {
        fwrite(STDOUT,"ERROR: ".$e->getMessage()." \n");
      }
      pg_close($pgsql);
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
      $trim_head = array_map('trim',$head);                                             //trim white space from header names

      $csvfile = array();
      $errlist = array();
      $x = 0;
      foreach ($rows as $row) {
        $csvfile[] = array_combine($trim_head, $row);
        $csvfile[$x]['name'] = ucfirst(strtolower(trim($csvfile[$x]['name'])));         //convert name to lowercase with first char Upper + trim whitespace
        $csvfile[$x]['surname'] = ucfirst(strtolower(trim($csvfile[$x]['surname'])));   //convert surname to lowercase with first char Upper + trim whitespace
        $csvfile[$x]['email'] = strtolower(trim($csvfile[$x]['email']));                //convert email to lowercase + trim whitespace

        if (!filter_var($csvfile[$x]['email'], FILTER_VALIDATE_EMAIL))
        {
          array_push($errlist, $csvfile[$x]);                                           //add invalid email record to ErrList
          array_splice($csvfile, $x, 1);                                                //remove invalid email record & reindex array
        }
        $x++;
      }
      print_r($errlist);    //testing only
      print_r($csvfile);    //testing only
    }

    //Simple function to display help instructions
    function helpInstructions()
    {
      $text = "\n     |>>** USER_UPLOAD DIRECTIVES **<<|     \n".
        "--file [csv filename] = this is the name of the CSV file to be parsed into the database\n".
        "--file [csv filename] --dry_run = Peforms a dry run on the csv file, but doesn't actually write any data to the Db\n".
        "--check-db = used to test the current Postgres database connection parameters.\n".
        "--create_table = this will cause the PostgreSQL users table to be built\n".
        "--help = Display theseinstructions for directive usage\n".
        "-u = Display the PostgreSQL username\n".
        "-u [username] = Sets the PostgreSQL username\n".
        "-p = Display the PostgreSQL password\n".
        "-p [password] = Sets the PostgreSQL password\n".
        "-h = Display the PostgreSQL host\n".
        "-h [host address] = Sets the PostgreSQL host\n".
        "-c = specify the PostgreSQL port\n".
        "-c [port] = Sets the PostgreSQL port number\n".
        "-d = Display the PostgreSQL database\n".
        "-d [database] = Sets the PostgreSQL database\n";
      fwrite(STDOUT, $text);
    }

    //Custom Exception Error handler for pg_connect, as it wont throw an exception
    function exception_db_handler($errno, $errstr, $errfile, $errline )
    {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

  ?>
