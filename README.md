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
* A Database connection to PostgreSQL has the following default settings: (these are easily changed by using the app's bash directives)   
    `Database = Postgres`   
    `host = 127.0.0.1`  
    `Port = 5432`   
    `Username = Postgres`
    `Password = root`   
* The database (if not using Postgres), username/password already exist within PostgreSQL.  This app will not create a database or user accounts in PostgreSQL.
* CSV file will be located in the same folder as the PHP Script (checks exist as to if the CSV file exists, but it does not check if a folder structure exists)
* CSV file will contain data in the structured order of: Name, Surname, Email
* CSV file may contain non Alpha characters in Name & Surname.  If this is the case, a blank value will be written to the database & reported with errors.

#### Instructions:
