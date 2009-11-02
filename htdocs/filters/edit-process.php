<?php
/*
	filters/edit-process.php

	access:
			filters_write

	Allows new filter rules to be added or exisiting filter rules to be adjusted
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");

// additional includes
require("../include/application/inc_filters.php");


if (user_permissions_get("filters_write"))
{
	$obj_filter = New filters;


	/*
		Load POST data
	*/

	$obj_filter->id						= security_form_input_predefined("int", "id_filter", 0, "");
	$obj_filter->data["title"]				= security_form_input_predefined("any", "title", 1, "");
	$obj_filter->data["type"]				= security_form_input_predefined("int", "type", 1, "");
	$obj_filter->data["value"]				= security_form_input_predefined("any", "value", 1, "");



	/*
		Error Handling
	*/


	// verify valid ID (if performing update)
	if ($obj_filter->id)
	{
		if (!$obj_filter->verify_id())
		{
			log_write("error", "process", "The filter you have attempted to edit - ". $obj_filter->id ." - does not exist in this system.");
		}
	}

	// ensure that the filter title/name is unique
	if (!$obj_filter->verify_unique_title())
	{
		log_write("error", "process", "This filter name/title is already in use by another rule.");
		error_flag_field("title");
	}

	// ensure that the filter logic is unique
	if (!$obj_filter->verify_unique_logic())
	{
		log_write("error", "process", "This filter logic already exists as part of another filter rule.");
		error_flag_field("type");
		error_flag_field("value");
	}


	// return to input page if any errors occured
	if (error_check())
	{
		if ($obj_filter->id)
		{
			$_SESSION["error"]["form"]["filter_view"] = "failed";
			header("Location: ../index.php?page=filters/view.php&id=". $obj_filter->id ."");
			exit(0);
		}
		else
		{
			$_SESSION["error"]["form"]["filter_add"] = "failed";
			header("Location: ../index.php?page=filters/add.php");
			exit(0);
		}
	}


	/*
		Process Data
	*/

	if ($obj_filter->action_update())
	{
		// success
		header("Location: ../index.php?page=filters/view.php&id=". $obj_filter->id);
		exit(0);
	}
	else
	{
		// unexpected failure
		header("Location: ../index.php?page=filters/view.php&id=". $obj_filter->id ."");
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
