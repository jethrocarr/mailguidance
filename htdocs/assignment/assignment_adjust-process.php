<?php
/*
	assignment/assignment_adjust-process.php

	access:
			assignment_write

	Applies changes that the user has selected to the assignment of users to filter rules.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");


if (user_permissions_get("assignment_write"))
{
	/*
		Load POST data
	*/

	$list_users		= security_form_input("/^[0-9,\s]*$/", "users", 1, "");
	$list_filters		= security_form_input("/^[0-9,\s]*$/", "filters", 1, "");


	/*
		Generate old and new filter mappings from POST data
	*/

	// fetch user list
	$obj_sql_users			= New sql_query;
	$obj_sql_users->string		= "SELECT id FROM users WHERE id IN ($list_users)";
	$obj_sql_users->execute();
	$obj_sql_users->fetch_array();

	// fetch filter list
	$obj_sql_filters		= New sql_query;
	$obj_sql_filters->string	= "SELECT id FROM filters WHERE id IN ($list_filters)";
	$obj_sql_filters->execute();
	$obj_sql_filters->fetch_array();

	// fetch old mapping
	$obj_sql_map			= New sql_query;
	$obj_sql_map->string		= "SELECT id_filter, id_user FROM filters_users";
	$obj_sql_map->execute();
	$obj_sql_map->fetch_array();

	$mapping_old = array();

	foreach ($obj_sql_map->data as $data)
	{
		$mapping_old[ $data["id_user"] ][] = $data["id_filter"];
	}


	// generate new mapping
	$mapping_new = array();

	foreach ($obj_sql_filters->data as $data_filter)
	{
		foreach ($obj_sql_users->data as $data_user)
		{
			// check form returns
			if ($_POST["user_". $data_user["id"] ."_filter_". $data_filter["id"]] == "on")
			{
				// selected
				$mapping_new[ $data_user["id"] ][] = $data_filter["id"];
			}
		}
	}




	/*
		Error Handling
	*/


	// return to input page if any errors occured
	if (error_check())
	{
		$_SESSION["error"]["form"]["assignment_adjust"] = "failed";
		header("Location: ../index.php?page=assignment/assignment.php");
		exit(0);
	}





	/*
		Process Data

		Here we run through the new mapping and check against the old mapping
		to see what changes we need to make to the SQL database.
	*/

	// start transaction
	$sql_obj = New sql_query;
	$sql_obj->trans_begin();


	// apply changes where required
	foreach ($obj_sql_users->data as $data_user)
	{
		foreach ($obj_sql_filters->data as $data_filter)
		{
			if (in_array($data_filter["id"], $mapping_new[ $data_user["id"] ]))
			{
				// filter is enabled in new configuration

				// check old configuration
				if (in_array($data_filter["id"], $mapping_old[ $data_user["id"] ]))
				{
					// enabled in old mapping, no change
				}
				else
				{
					// has been enabled
					$sql_obj->string	= "INSERT INTO filters_users (id_filter, id_user) VALUES ('". $data_filter["id"] ."', '". $data_user["id"] ."')";
					$sql_obj->execute();
				}
			}
			else
			{
				// filter is disabled in new configuration

				// check old configuration
				if (in_array($data_filter["id"], $mapping_old[ $data_user["id"] ]))
				{
					// enabled in old mapping, has been disabled
					$sql_obj->string	= "DELETE FROM filters_users WHERE id_filter='". $data_filter["id"] ."' AND id_user='". $data_user["id"] ."' LIMIT 1";
					$sql_obj->execute();
				}
				else
				{
					// disabled in old mapping, no change.
				}
			}
		}
	}


	// commit
	if (error_check())
	{
		$sql_obj->trans_rollback();

		log_write("error", "process", "An unexpected error occured whilst attempting to update the filter assignment");

		header("Location: ../index.php?page=assignment/assignment.php");
		exit(0);
	}
	else
	{
		$sql_obj->trans_commit();

		log_write("notification", "process", "Configuration updated successfully");

		header("Location: ../index.php?page=assignment/assignment.php");
		exit(0);
	}
	
}
else
{
	// user does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
