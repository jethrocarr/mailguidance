<?php
/*
	mailguidance-cron.php

	This script provides the main logic for generating procmailrc files
	based on the configuration in MailGuidance.

	Do not execute this file directly, instead execute mailguidance-cron-user.php
	which will call this script with the correct configuration.


	(c) Copyright 2012 Jethro Carr

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License version 3
	only as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

require($config["app_path"] ."/htdocs/include/database.php");
require($config["app_path"] ."/htdocs/include/amberphplib/main.php");

log_debug("main", "Starting mailguidance-cron.php");



/*
	Check if we need to update procmail rules

	If there are no changes, would be a waste of resources to regenerate the file.
*/

if (sql_get_singlevalue("SELECT value FROM config WHERE name='PROCMAIL_UPDATE_STATUS' LIMIT 1") != "update_required")
{
	log_debug("main", "No need to update procmail rules");
	exit(0);
}
	




/*
	Fetch mail handling configuration
*/
$config["archive_filter_folders"]	= sql_get_singlevalue("SELECT value FROM config WHERE name='ARCHIVE_FILTER_FOLDERS' LIMIT 1");
$config["archive_inbox_allmail"]	= sql_get_singlevalue("SELECT value FROM config WHERE name='ARCHIVE_INBOX_ALLMAIL' LIMIT 1");

$config["mail_default_mode"]		= sql_get_singlevalue("SELECT value FROM config WHERE name='MAIL_DEFAULT_MODE' LIMIT 1");
$config["mail_default_address"]		= sql_get_singlevalue("SELECT value FROM config WHERE name='MAIL_DEFAULT_ADDRESS' LIMIT 1");


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
		// create map of user ids to email addresses
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


/*
	TODO: solve recursiveness

	If mail is re-directed from one user to another, but the destination user is also
	going away, the re-direct will not be properly applied and some emails would
	still be delivered to users who are away.
*/



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
		// prep the arrays (prevents PHP warnings)
		if (!$map_filters_users[ $data_sql["id_filter"] ])
		{
			$map_filters_users[ $data_sql["id_filter"] ] = array();
		}


		/*
			Process holiday redirections
		*/
		if (in_array($data_sql["id_user"], $map_users_holiday))
		{
			log_debug("main", "User ". $data_sql["id_user"] ." is on holiday");

			if ($map_users_holiday_redirect[ $data_sql["id_user"] ])
			{
				// redirect emails by renaming the old user ID with the target userid
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


		/*
			Add the user's email address to the filter, provided that they
			don't already exist.

			(Duplicate entries can occur when a user is on holiday and redirecting all
			 their filters to another user)
		*/
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


		// start filter rule
		$procmail[] = "# ". $data_filter["title"] ."\n";
		$procmail[] = ":0\n";

		// fix value for use by rule logic - certain chars need to be escaped
		$target		= array();
		$replace	= array();

		$target[]	= "/\[/";		$replace[]	= "\[";
		$target[]	= "/\]/";		$replace[]	= "\]";

		$data_filter["value"] = preg_replace($target, $replace, $data_filter["value"]);

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

		// add all mapped users for this filter
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
			$procmail[] = "\t\"". $data_filter["title"] ."\"\n";
			$procmail[] = "\t\n";
		}


		// finally either save mail to inbox, or delete
		if ($config["archive_inbox_allmail"])
		{
			// if ARCHIVE_INBOX_ALLMAIL is set, save a copy in the main inbox

			$procmail[] = "\t:0\n";
			$procmail[] = "\t\$DEFAULT\n";
			$procmail[] = "\t\n";
		}
		else
		{
			// mail has been processed, delete it to prevent
			// any further processing.
			$procmail[] = "\t:0\n";
			$procmail[] = "\t/dev/null\n";
			$procmail[] = "\t\n";
		}

		$procmail[] = "}\n\n";
	}
}



/*
	Final catch all rules

	These rules ONLY APPLY TO UNMATCHED EMAILS.
*/


// do inbox archiving if required
if ($config["archive_inbox_allmail"] && $config["mail_default_mode"] != "inbox")
{
	// the default mode is not to save in the inbox, but the archive options require
	// saving email to the inbox.
	//
	// therefore, save to the inbox and then proceed with default rule.

	$procmail[] = "# archive unmatched emails to inbox\n";
	$procmail[] = ":0 c\n";
	$procmail[] = "\$DEFAULT\n";
	$procmail[] = "\n";
}

// process default mode rules
switch ($config["mail_default_mode"])
{
	case "forward":
		// forward email to another address
		$procmail[] = "# forward unmatched mail\n";
		$procmail[] = ":0\n";
		$procmail[] = "! ". $config["mail_default_address"] ."\n";
		$procmail[] = "\n";
	break;

	case "drop":
		// we don't want to retain a copy, delete the mail
		$procmail[] = "# delete unmatched mail\n";
		$procmail[] = ":0\n";
		$procmail[] = "/dev/null\n";
		$procmail[] = "\n";
	break;

	case "everyone":
		// send unmatched mail to all users
		$procmail[] = "# send unmatched mail to all users\n";

		foreach (array_keys($map_users_email) as $id_user)
		{
			// exclude users who are on holiday
			if (!in_array($id_user, $map_users_holiday))
			{
				$procmail[] = ":0 c\n";
				$procmail[] = "! ". $map_users_email[ $id_user ] ."\n";
			}
		}

		$procmail[] = "\n";
		$procmail[] = ":0\n";
		$procmail[] = "/dev/null\n";
		$procmail[] = "\n";
	break;

	case "inbox":
	default:
		// nothing todo, the email will automatically land in the inbox by default
	break;
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
//fwrite($fh, "LOGFILE=\$HOME/.procmaillog\n");		// not much use, since it only shows mailbox changes, not forwards
fwrite($fh, "\n");
			        
foreach ($procmail as $line)
{
	fwrite($fh, $line);
}

fclose($fh);




/*
	Complete
*/

$sql_obj->string = "UPDATE config SET value='synced' WHERE name='PROCMAIL_UPDATE_STATUS' LIMIT 1";
$sql_obj->execute();

log_debug("main", "Completed mailguidance-cron.php");
exit(0);

