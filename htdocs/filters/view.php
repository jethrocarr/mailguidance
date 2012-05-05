<?php
/*
	filters/view.php

	access:		filters_read	<no access>
			filters_write	Full Access

	Shows all the details of the filter and allows it to be adjusted.
*/

// custom includes
require("include/application/inc_filters.php");


class page_output
{
	var $obj_filter;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{
		// create filter object
		$this->obj_filter = New filters;

		// fetch variables
		$this->obj_filter->id = security_script_input('/^[0-9]*$/', $_GET["id"]);

		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Filter Details", "page=filters/view.php&id=". $this->obj_filter->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete Filter", "page=filters/delete.php&id=". $this->obj_filter->id ."");
	}



	function check_permissions()
	{
		return user_permissions_get("filters_write");
	}



	function check_requirements()
	{
		// verify that filter exists
		if (!$this->obj_filter->verify_id())
		{
			log_write("error", "page_output", "The requested filter (". $this->obj_filter->id .") does not exist - possibly the filter has been deleted.");
			return 0;
		}

		return 1;
	}


	function execute()
	{

		// define basic form details
		$this->obj_form = New form_input;
		$this->obj_form->formname = "filter_view";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "filters/edit-process.php";
		$this->obj_form->method = "post";
		

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "title";
		$structure["type"]		= "input";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure = form_helper_prepare_dropdownfromdb("type", "SELECT id, type as label FROM filter_types ORDER BY label");
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "value";
		$structure["type"]		= "input";
		$this->obj_form->add_input($structure);


		// hidden
		$structure = NULL;
		$structure["fieldname"] 	= "id_filter";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_filter->id;
		$this->obj_form->add_input($structure);


		// submit button
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "submit";
		$this->obj_form->add_input($structure);
		

		// define subforms
		$this->obj_form->subforms["filter_details"]	= array("title", "type", "value");
		$this->obj_form->subforms["hidden"]		= array("id_filter");
		$this->obj_form->subforms["submit"]		= array("submit");
		

		// fetch the form data
		$this->obj_form->sql_query = "SELECT * FROM `filters` WHERE id='". $this->obj_filter->id ."' LIMIT 1";
		$this->obj_form->load_data();
	}


	function render_html()
	{
		// title	
		print "<h3>FILTER DETAILS</h3><br>";
		print "<p>This page allows you to view and adjust the filter rules.</p>";

		// display the form
		$this->obj_form->render_form();
	}


} // end of page_output class

?>
