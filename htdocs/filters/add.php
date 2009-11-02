<?php
/*
	filters/add.php

	access:	filters_write

	Form to add a new filter.
*/

class page_output
{
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("filters_write");
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		// define basic form details
		$this->obj_form = New form_input;
		$this->obj_form->formname = "filter_add";
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
	

		// submit button
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "filter_add";
		$this->obj_form->add_input($structure);
		

		// define subforms
		$this->obj_form->subforms["filter_details"]	= array("title", "type", "value");
		$this->obj_form->subforms["submit"]		= array("submit");
		
		// load any data returned due to errors
		$this->obj_form->load_data_error();
	}



	function render_html()
	{
		// title and summary
		print "<h3>ADD FILTER</h3><br>";
		print "<p>Use this form to create a new filter.</p>";

		// display the form
		$this->obj_form->render_form();
	}


} // end page_output class

?>
