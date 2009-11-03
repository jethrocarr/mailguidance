<?php
/*
	mailguidance-cron.php

	This script provides the main logic for generating procmailrc files
	based on the configuration in MailGuidance.

	Do not execute this file directly, instead execute mailguidance-cron-user.php
	which will call this script with the correct configuration.
*/

require($config["app_path"] ."/htdocs/include/database.php");
require($config["app_path"] ."/htdocs/include/amberphplib/main.php");

log_debug("main", "Starting mailguidance-cron.php");


/*
	Fetch archive configuration
*/
$config["archive_filter_folders"]	= sql_get_singlevalue("SELECT value FROM config WHERE name='ARCHIVE_FILTER_FOLDERS' LIMIT 1");
$config["archive_inbox_allmail"]	= sql_get_singlevalue("SELECT value FROM config WHERE name='ARCHIVE_INBOX_ALLMAIL' LIMIT 1");


/*
	Define mapping arrays
*/

$map_users_email		= array();
$map_users_holiday 		= array();
$map_users_holiday_redirect	= array();
$map_filters_users		= array();

/*
	Fetch all users
*/

log_debug("main", "Fetching user information");

$obj_sql_users		= New sql_query;
$obj_sql_users->string	= "SELECT id, contact_email FROM users";
$obj_sql_users->execute();

if ($obj_sql_users->num_rows())
{
	$obj_sql_users->fetch_array();

	foreach ($obj_sql_users->data as $data_sql)
	{
		$map_users_email[ $data_sql["id"] ] = $data_sql["contact_email"];
	}
}



/*
	Fetch user holiday information
*/

log_debug("main", "Fetching user holiday information");

$obj_sql_users_holiday		= New sql_query;
$obj_sql_users_holiday->string	= "SELECT id_user, id_user_redirect FROM users_holidaymode";
$obj_sql_users_holiday->execute();

if ($obj_sql_users_holiday->num_rows())
{
	$obj_sql_users_holiday->fetch_array();

	foreach ($obj_sql_users_holiday->data as $data_sql)
	{
		$map_users_holiday[]					= $data_sql["id_user"];
		$map_users_holiday_redirect[ $data_sql["id_user"] ]	= $data_sql["id_user_redirect"];
	}
}

// TODO: solve recursiveness




/*
	Fetch user <-> filter mapping
*/

log_debug("main", "Fetching user <-> filter mapping");

$obj_sql_filters_users		= New sql_query;
$obj_sql_filters_users->string	= "SELECT id_user, id_filter FROM filters_users";
$obj_sql_filters_users->execute();

if ($obj_sql_filters_users->num_rows())
{
	$obj_sql_filters_users->fetch_array();

	foreach ($obj_sql_filters_users->data as $data_sql)
	{
		if (!$map_filters_users[ $data_sql["id_filter"] ])
		{
			$map_filters_users[ $data_sql["id_filter"] ] = array();
		}

		if (in_array($data_sql["id_user"], $map_users_holiday))
		{
			log_debug("main", "User ". $data_sql["id_user"] ." is on holiday");

			if ($map_users_holiday_redirect[ $data_sql["id_user"] ])
			{
				// redirect mail
				$data_sql["id_user"] = $map_users_holiday_redirect[ $data_sql["id_user"] ];
				
				log_debug("main", "Redirecting mail to user ". $data_sql["id_user"] ."");

			}
			else
			{
				// do not add this user to the map, since we don't want them
				// active in any filters.

				continue;
			}

		}


		// add user to the map (provided that they don't already exist)
		if (!in_array($data_sql["id_user"], $map_filters_users[ $data_sql["id_filter"] ]))
		{
			$map_filters_users[ $data_sql["id_filter"] ][] = $data_sql["id_user"];
		}
	}
}

if ($_SESSION["user"]["debug"])
{
	print "Dump of user <-> filter mappings:\n";
	print_r($map_filters_users);
}




/*
	Fetch all filters
*/

$obj_sql_filters		= New sql_query;
$obj_sql_filters->string	= "SELECT filters.id as id, title, filter_types.type as type, value FROM filters LEFT JOIN filter_types ON filter_types.id = filters.type";
$obj_sql_filters->execute();



/*
	Generate rules - we do this by running through the filters and using
	the previously generated mappings to write procmail rules.
*/

$procmail = array();

if ($obj_sql_filters->num_rows())
{
	$obj_sql_filters->fetch_array();

	foreach ($obj_sql_filters->data as $data_filter)
	{
		log_debug("main", "Writing filter ". $data_filter["id"] ."");

		$procmail[] = "# ". $data_filter["title"] ."\n";
		$procmail[] = ":0\n";

		// process filter type
		switch ($data_filter["type"])
		{
			case "sender_domain":
				$procmail[] = "* ^From.*". $data_filter["value"] .".*\n";
			break;

			case "subject":
				$procmail[] = "* ^Subject.*". $data_filter["value"] .".*\n";
			break;

			case "custom":
				$procmail[] = "* ". $data_filter["value"] ."\n";
			break;
		}

		$procmail[] = "{\n";

		// run through all mapped users for this filter
		if ($map_filters_users[ $data_filter["id"] ])
		{
			foreach ($map_filters_users[ $data_filter["id"] ] as $id_user)
			{
				$procmail[] = "\t:0 c\n";
				$procmail[] = "\t! ". $map_users_email[ $id_user ] ."\n";
				$procmail[] = "\t\n";
			}
		}

		// perform optional folder-based mailbox archiving
		if ($config["archive_filter_folders"])
		{
			$procmail[] = "\t:0 c\n";
			$procmail[] = "\t\"". $data_filter["title"] ."\"";
			$procmail[] = "\t\n";
		}

		$procmail[] = "}\n\n";
	}
}


/*
	Final catch all rules
*/

if ($config["archive_inbox_allmail"])
{
	// save a copy in the inbox
	// this will happen by default, no need to write rules
}
else
{
	// we don't want to retain a copy
	// direct it to /dev/null
	$procmail[] = ":0\n";
	$procmail[] = "/dev/null\n";
	$procmail[] = "\n";
}



/*
	Write procmailrc file
*/

log_debug("main", "Writing .procmailrc file");


if (!$fh = fopen($_ENV["HOME"] ."/.procmailrc", "w"))
{
	log_write("error", "main", "Unable to open file ". $_ENV["HOME"] ."/.procmailrc for writing");
	exit(1);
}

fwrite($fh, "# Automatically generated by MailGuidance - do not manually adjust\n");
fwrite($fh, "\n");
fwrite($fh, "MAILDIR=\$HOME/mail\n");
fwrite($fh, "\n");
			        
foreach ($procmail as $line)
{
	fwrite($fh, $line);
}

fclose($fh);




log_debug("main", "Completed mailguidance-cron.php");

exit(0);

