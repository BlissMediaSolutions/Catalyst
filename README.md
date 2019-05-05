# Catalyst IT PHP Coding Challenge

#### Author:
* Danielle Walker

#### Designed / Tested on:
* Ubuntu Server 16.04.5 LTS (Xenial Xerus)   
* PHP 7.2   
* PostgreSQL 9.5
* OpenSSH

#### Assumptions:
* The PHP simplexml module has been installed.  If need to install, use the following instructions (for PHP 7.2)  
    `sudo apt-get install php7.2-simplexml`    
* A Database connection to PostgreSQL has the following default settings: (these are easily changed by using the apps directives)   
    `Database = Postgres`   
    `host = 127.0.0.1`  
    `Port = 5432`   
    `Username = Postgres`
    `Password = root`   
* The database (if not using Postgres), username & password already exist within PostgreSQL DB.  This app will not create a database or user accounts in PostgreSQL.
* The `email` field in the database allows a max of 50 characters.  This field is also a Primary Key (Unique + Indexed)
* The `name` field in the database allows a max of 25 characters.
* The `surname` field in the database allows a max of 25 characters.
* Validation of an email address should comply with RFC 5322 (section 3.4.1).  Thus in the 'local' part of the address, non alpha-numeric characters like: `!#$%&'*+-/=?^_{|}~` are allowed.
  According to this basis, email addresses like: _`john!doe@example.com`_, _`john$doe@example.com`_ and _`john+doe@example.com`_ are all valid addresses.
* If an email address is not valid or it already exists in the db - then it wont be added to the db & the app will create a CSV 'Error' file which will list this data.
* If a name or surname contain non-alpha characters, While it will still be added to the database, the app will also create a CSV 'issues' file which will list these (so they can be addressed in the db)
* All CSV files will be located in the same folder as the PHP Script (checks exist as to if the CSV file exists, but it does not check if a folder structure exists)

#### Instructions:
This script is designed to be run from the Command Line\Bash shell - to run it simply;   
`php user_upload.php {directive}`   
Full directive instructions are provided within the script by simply running:   
`php uper_upload.php --help`
