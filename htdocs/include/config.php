<?php
/*
	This configuration file contains application internal settings and should
	not be adjusted.

	Your real configuration file is in config-settings.php
*/


/*
	Define Application Name & Versions
*/

// define the application details
$GLOBALS["config"]["app_name"]			= "MailGuidance";
$GLOBALS["config"]["app_version"]		= "1.0";

// define the schema version required
$GLOBALS["config"]["schema_version"]		= "20120506";



/*
	Inherit User Configuration & Database Connectivity
*/
include("config-settings.php");
include("database.php");

?>
