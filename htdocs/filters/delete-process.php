<?php
/*
	filters/delete-process.php

	access: filters_write

	Deletes an unwanted filter.
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");

// custom includes
require("../include/application/inc_filters.php");


if (user_permissions_get("filters_write"))
{
	$obj_filter = New filters;


	/*
		Load POST data
	*/

	$obj_filter->id			= security_form_input_predefined("int", "id_filter", 1, "");

	// these exist to make error handling work right
	$data["title"]			= security_form_input_predefined("any", "title", 0, "");

	// confirm deletion
	$data["delete_confirm"]		= security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");

	

	/*
		Error Handling
	*/


	// make sure the filter actually exists
	if (!$obj_filter->verify_id())
	{
		log_write("error", "process", "The filter you have attempted to delete - ". $obj_filter->id ." - does not exist in this system.");
	}

	

	// return to the input page in the event of an error
	if ($_SESSION["error"]["message"])
	{	
		$_SESSION["error"]["form"]["filter_delete"] = "failed";
		header("Location: ../index.php?page=filters/delete.php&id=". $obj_filter->id);
		exit(0);
	}



	/*
		Delete Filter
	*/

	// delete filter
	$obj_filter->action_delete();


	// return to filters list
	header("Location: ../index.php?page=filters/filters.php");
	exit(0);
	
}
else
{
	// user does not have perms to view this page/isn't logged on
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
