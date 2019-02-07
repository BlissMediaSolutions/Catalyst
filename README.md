# Catalyst IT PHP Coding Challenge

#### Author:
* Danielle Walker

#### Designed / Tested on:
* VM Workstation 12.5   
* Ubuntu Server 16.04.5 LTS (Xenial Xerus)   
* PHP 7.2   
* PostgreSQL 9.5
* OpenSSH

#### Assumptions:
* The PHP simplexml module has been installed.   
    `sudo apt-get install php7.2-simplexml`  (if using PHP version 7.2)   

* CSV file will be located in the same folder as the PHP Script (checks exist as to if the CSV file exists, but it does not check if a folder structure exists)
* CSV file will contain data in the structured order of: Name, Surname, Email
* CSV file may contain white spaces in the header.  Either trim the header before creating the key for an Associative Array or use an Indexed.
* CSV file may contain non Alpha characters in Name & Surname.  If this is the case, a blank value will be written to the database & reported with errors.
* When the 1st connection element is specified, the app will create the file "dbconnect.xml" and then use this for connection properties.
* If any of the Postgre connection elements are not specified or found in the connection file, it will try using the following defaults:
  Username = root, Password = root, Host = 127.0.0.1, Database = testCatalyst
* If no Database has been specified, and the default doesn't exist, then the system will create/use the database "tmpCatalyst"

#### Instructions:
