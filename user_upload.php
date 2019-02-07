<?php
  //TODO: change echo statements to stdout.
  //    **CSV file contains white spaces in header.  Assuming this will exist when Catalyst run it, then either the header needs to be trimmed or change it to an indexed array.
  //    futher filtering on name/surname to remove non alpha characters.
  //    Database handling

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
          break;
        }

      case "--create-table":
        //check if a db connection file exists - if it doesnt
        if (checkFileExists("dbconnect.xml") == false)
        {
          $db = pg_connect( "$host $port $dbname $credentials" );
          if (!$db) {
             echo "Error : Unable to open database\n";
           } else {
              echo "Opened database successfully\n";
           }
        }
        break;

      //Specify the Postgre Directives
      case "-u":
      case "-p":
      case "-h":
      case "-c":
      case "-d":
        if (empty($argv[2])) {
          readXMLFile($argv[1]);
        } else {
          modifyXMLFile($argv[1], $argv[2]);
        }
        break;

      case "--help":
        exit(helpInstructions());

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
    function readXMLFile($argu1)
    {
      if (checkFileExists("dbconnect.xml") == false)
      {
        createXMLFile();
      }
      $xml = simplexml_load_file("dbconnect.xml");
      switch ($argu1)
      {
        case "-u":
          echo "Postgre Username: ".$xml->username."\n";
          break;
        case "-p":
          echo "Postgre Password: ".$xml->password."\n";
          break;
        case "-h":
          echo "Postgre Host: ".$xml->host."\n";
          break;
        case "-c":
          echo "Postgre Port No: ".$xml->port."\n";
          break;
        case "-d":
          echo "Postgre Database: ".$xml->database."\n";
          break;
        }
    }

    //function used to modify a given key value in the XML file for the Postgre connection
    function modifyXMLFile($argu1, $argu2)
    {
      if (checkFileExists("dbconnect.xml") == false)
      {
        createXMLFile();
      }
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
      echo $string."\n";
    }

    //function used to create an empty XML file of the connection elements for Postgre (includes default values)
    function createXMLFile()
    {
      $xmlstr = "<?xml version='1.0' encoding='UTF-8'?><dbconnect></dbconnect>";
      $xmlconn = new SimpleXMLElement($xmlstr);
      $xmlconn->addChild('username','root');
      $xmlconn->addChild('password','root');
      $xmlconn->addChild('host','127.0.0.1');
      $xmlconn->addChild('port','5432');
      $xmlconn->addChild('database','tmpCatalyst');
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
      echo "--file [csv filename] = this is the name of the CSV file to be parsed\n";
      echo "--file [csv filename] --dry_run = Peforms a dry run on the csv file, but doesn't actually write any data to the Db\n";
      echo "--create_table = this will cause the PostgreSQL users table to be built\n";
      echo "-u = specify the PostgreSQL username\n";
      echo "-p = specify the PostgreSQL password\n";
      echo "-h = specify the PostgreSQL host\n";
      echo "-c = specify the PostgreSQL port\n";
      echo "-d = specify the PostgreSQL database\n";
      echo "--help = Display instructions for directive usage\n\n";
      echo "     |>>** USAGE EXAMPLES **<<|     \n";
      echo "i) php user_upload.php --file users.csv = the csv file 'users' will be parsed and written to the Database.\n";
      echo "ii) php user_upload.php --file users.csv --dry_run = the csv file 'users' will be parsed, but no data will be written to database.\n\n";
      //add more usage examples
    }

  ?>
