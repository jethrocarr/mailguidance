<?php
/*
	filters/delete.

	access:	filters_write

	Allows unwanted filters to be deleted.
*/


// custom includes
require("include/application/inc_filters.php");


class page_output
{
	var $obj_menu_nav;
	var $obj_form;
	var $obj_fiter;

	function page_output()
	{
		$this->obj_filter = New filters;

		// fetch variables
		$this->obj_filter->id = security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;
		$this->obj_menu_nav->add_item("Filter Details", "page=filters/view.php&id=". $this->obj_filter->id ."");
		$this->obj_menu_nav->add_item("Delete Filter", "page=filters/delete.php&id=". $this->obj_filter->id ."", TRUE);
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
		/*
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "filter_delete";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action = "filters/delete-process.php";
		$this->obj_form->method = "post";
		

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "title";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden
		$structure = NULL;
		$structure["fieldname"] 	= "id_filter";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_filter->id;
		$this->obj_form->add_input($structure);
		
		
		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this filter and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);



		// define submit field
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
				
		$this->obj_form->add_input($structure);


		
		// define subforms
		$this->obj_form->subforms["filter_delete"]	= array("title");
		$this->obj_form->subforms["hidden"]		= array("id_filter");
		$this->obj_form->subforms["submit"]	= array("delete_confirm", "submit");
		
		// fetch the form data
		$this->obj_form->sql_query = "SELECT title FROM `filters` WHERE id='". $this->obj_filter->id ."' LIMIT 1";
		$this->obj_form->load_data();
		
	}
	


	function render_html()
	{

		// title/summary
		print "<h3>DELETE FILTER</h3><br>";
		print "<p>This page allows you to delete an unwanted filter.</p>";


		// display the form
		$this->obj_form->render_form();
	}


} // end page_output class


?>
