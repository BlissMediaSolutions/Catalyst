<?php
  //TODO: change echo statements to stdout.
  //    **CSV file contains white spaces in header.  Assuming this will exist when Catalyst run it, then either the header needs to be trimmed or change it to an indexed array.
  //    futher filtering on name/surname to remove non alpha characters.
  //    Database handling

  //Check if the XML file for settings exist, if not create it, then load the contents into a new object;
  if (checkFileExists("dbconnect.xml") == false)
  {
    createXMLFile();
  }
  $xml = new stdClass();
  $xml = simplexml_load_file("dbconnect.xml");

  //Handle the directives from the Bash input
  foreach ($argv as $value)
  {
    //Simple check if any directives were received.  If not, display error, help instructions & exit.
    if (sizeof($argv) <= 1) {
      echo ("Error Command.  Missing Directive input");
      exit(helpInstructions());
    }

    //Handle Bash directive arguments
    switch ($argv[1])
    {
      case "--file":
        //check to see if a filename was added as a directive, and that the file exists
        if ((empty($argv[2])) OR (checkFileExists($argv[2]) == false))
        {
          exit ("Unknown command $argv[1].  File name missing or File Not Found\n");
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
            echo "Table Create Successfully\n";
          }
        } catch (Exception $e) {
          echo "ERROR: ".$e->getMessage()." \n";
        }
        pg_close($pg);
        break;

      case "--check-db":
        set_error_handler("exception_db_handler");
        try {
          $pgsql = @pg_connect("host=".$xml->host." port=".$xml->port." dbname=".$xml->database." user=".$xml->username." password=".$xml->password);

          if ($pgsql) {
            echo "Successfully connected to database: " . pg_dbname($pgsql)."\n";
          }
        } catch (Exception $e) {
          echo "ERROR: ".$e->getMessage()." \n";
        }
        pg_close($pgsql);
        break;

      //Specify the Postgre Directives
      case "-u":
      case "-p":
      case "-h":
      case "-c":
      case "-d":
        if (empty($argv[2])) {
          echo readXMLFile($xml, $argv[1])."\n";
        } else {
          echo modifyXMLFile($xml, $argv[1], $argv[2])."\n";
        }
        break;

      case "--help":
        echo helpInstructions()."\n";
        break;

      default:
        exit ("Unknown command $argv[1].  Try the --help directive for assistance\n");
      }
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

    //function used to the read an attribute from the XML file which stores the connection elements for Postgre
    function readXMLFile($xml, $argu1)
    {
      switch ($argu1)
      {
        case "-u":
          $xmlString = "Postgre Username: ".$xml->username;
          break;
        case "-p":
          $xmlString = "Postgre Password: ".$xml->password;
          break;
        case "-h":
          $xmlString = "Postgre Host: ".$xml->host;
          break;
        case "-c":
          $xmlString = "Postgre Port No: ".$xml->port;
          break;
        case "-d":
          $xmlString = "Postgre Database: ".$xml->database;
          break;
        }
        return $xmlString;
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
          $string = "Postgre Password: ".$xml->password;
          break;
        case "-h":
          $xml->host = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Host: ".$xml->host;
          break;
        case "-c":
          $xml->port = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Port No: ".$xml->port;
          break;
        case "-d":
          $sml->database = $argu2;
          $xml->asXML("dbconnect.xml");
          $string = "Postgre Database: ".$xml->database;
          break;
      }
      //$xml->close();
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
      print_r($rows);             //For testing purposes
      $head = array_shift($rows);

      $csvfile = array();
      $x = 0;
      foreach ($rows as $row) {
        $csvfile[] = array_combine($head, $row);
        $csvfile[$x]['name'] = ucfirst(strtolower($csvfile[$x]['name']));
        $csvfile[$x]['surname'] = ucfirst(strtolower($csvfile[$x]['surname']));
        $x++;
      }
      echo ("USER: ".ucfirst(strtolower($csvfile[6]['name']))."\n");     //For testing purposes
      echo ("USER: ".ucfirst(strtolower($csvfile[6]['surname']))."\n");  //For testing purposes
      //echo ("USER: ".ucfirst(strtolower($csvfile[6]['email']))."\n");
    }

    //Simple function to display help instructions
    function helpInstructions()
    {
      echo "\n     |>>** USER_UPLOAD DIRECTIVES **<<|     \n";
      echo "--file [csv filename] = this is the name of the CSV file to be parsed into the database\n";
      echo "--file [csv filename] --dry_run = Peforms a dry run on the csv file, but doesn't actually write any data to the Db\n";
      echo "--check-db = used to test the current Postgres database connection parameters.\n";
      echo "--create_table = this will cause the PostgreSQL users table to be built\n";
      echo "-u = Display the PostgreSQL username\n";
      echo "-u [username] = Sets the PostgreSQL username\n";
      echo "-p = Display the PostgreSQL password\n";
      echo "-p [password] = Sets the PostgreSQL password\n";
      echo "-h = Display the PostgreSQL host\n";
      echo "-h [host address] = Sets the PostgreSQL host\n";
      echo "-c = specify the PostgreSQL port\n";
      echo "-c [port] = Sets the PostgreSQL port number\n";
      echo "-d = Display the PostgreSQL database\n";
      echo "-d [database] = Sets the PostgreSQL database\n";
      echo "--help = Display instructions for directive usage\n\n";
    }

    //Custom Exception Error handler for pg_connect, as it wont throw an exception
    function exception_db_handler($errno, $errstr, $errfile, $errline )
    {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

  ?>
