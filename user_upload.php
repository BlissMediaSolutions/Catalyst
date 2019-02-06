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

    //Handle directive arguments
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

      case "-u":

      case "-p":

      case "-h":

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
      echo "--help = Display instructions for directive usage\n\n";
      echo "     |>>** USAGE EXAMPLES **<<|     \n";
      echo "i) php user_upload.php --file users.csv = the csv file 'users' will be parsed and written to the Database.\n";
      echo "ii) php user_upload.php --file users.csv --dry_run = the csv file 'users' will be parsed, but no data will be written to database.\n\n";
      //add more usage examples
    }

  ?>
