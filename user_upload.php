<?php

  //Handle the directives from the Bash input
  foreach ($argv as $value)
  {
    //Simple check if any directives were received.  If not, display error & exit.
    if (sizeof($argv) <= 1) {
      echo ("Error Command.  Missing Directive input");
      exit(helpInstructions());
    }

    //Handle directive arguments
    switch ($argv[1])
    {
      case "--file":

      case "--create-table":

      case "-u":

      case "-p":

      case "-h":

      case "--help":
        exit(helpInstructions());

      default:
        exit ("Unkown command $argv[1].  Try the --help directive for assistance\n");
      }
    }

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
