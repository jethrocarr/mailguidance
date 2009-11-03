#!/usr/bin/php
<?php
/*
	mailguidance-cron-user.php

	This PHP file should be installed into the user home directory
	and be configured with the DB settings and other option.

	The actual logic will then be imported from the install directory
	of MailGuidance.
*/



/*
	SETTINGS
*/

$GLOBALS["config"];

// Install path of MailGuidance application
$config["app_path"] = "/home/jethro/webapp_development/amberdms/mailguidance/cvs/mailguidance/";

// Database Configuration
$config["db_host"] = "localhost";			// hostname of the MySQL server
$config["db_name"] = "mailguidance_devel";		// database name
$config["db_user"] = "root";				// MySQL user
$config["db_pass"] = "sdr05ynw4tuj";			// MySQL password (if any)



/*
	Fixed options

	Do not touch anything below this line
*/

// trick to make logging and error system work correctly for scripts.
$GLOBALS["_SESSION"]	= array();
$_SESSION["mode"]	= "cli";


// force debugging on for all users + scripts
// (note: debugging can be enabled on a per-user basis by an admin via the web interface)
$_SESSION["user"]["debug"] = "on";



/*
	Import Logic
*/

require($config["app_path"] ."/scripts/mailguidance-cron.php");



