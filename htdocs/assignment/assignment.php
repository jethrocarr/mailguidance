<?php
/*
	assignment/assignment.php

	access: assignment_read

	Draws a table/form showing all users cross referenced with filters
	to allow configuration.
*/

class page_output
{
	var $obj_form;
	var $obj_table;


	function check_permissions()
	{
		if (user_permissions_get("assignment_read"))
		{
			return 1;
		}
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	function execute()
	{
		/*
			Select user details
		*/

		$this->obj_sql_users		= New sql_query;
		$this->obj_sql_users->string	= "SELECT id, realname FROM users ORDER BY realname";
		$this->obj_sql_users->execute();
		$this->obj_sql_users->fetch_array();


		/*
			Select User <-> Filter mapping
		*/

		$this->obj_sql_map		= New sql_query;
		$this->obj_sql_map->string	= "SELECT id_filter, id_user FROM filters_users";
		$this->obj_sql_map->execute();
		$this->obj_sql_map->fetch_array();

		$mapping_users_filters = array();

		foreach ($this->obj_sql_map->data as $data)
		{
			$mapping_users_filters[ $data["id_user"] ][] = $data["id_filter"];
		}


		/*
			Define table structure

			We generate a normal table object here, although we will custom render it, by generating
			a regular table object, we can use all the cool stuff it provides like filters and value
			handling.
		*/

		// define filter list table
		$this->obj_table		= New table;
		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "assignment";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "filter_title", "filters.title");
		$this->obj_table->columns[] = "filter_title";

		foreach ($this->obj_sql_users->data as $data_users)
		{
			$this->obj_table->add_column("standard", $data_users["id"], "NONE");

			$this->obj_table->custom_column_label($data_users["id"], $data_users["realname"]);

			$this->obj_table->columns[] = $data_users["id"];
		}

//		$this->obj_table->add_column("standard", "type", "filter_types.type");
//		$this->obj_table->add_column("standard", "value", "filters.value");

		// defaults
//		$this->obj_table->columns			= array("title", "type", "value");
//		$this->obj_table->columns_order		= array("title");
//		$this->obj_table->columns_order_options	= array("title", "type", "value");

		// define SQL structure
		$this->obj_table->sql_obj->prepare_sql_settable("filters");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "filters.id");

		// acceptable filter options
		$structure = NULL;
		$structure["fieldname"] = "filter_search";
		$structure["type"]	= "input";
		$structure["sql"]	= "(title LIKE '%value%')";
		$this->obj_table->add_filter($structure);
		

		// load settings from options form
		$this->obj_table->load_options_form();

		// fetch filter data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();


		// fill in fields
		/*
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			// run through all the users
			foreach ($this->obj_sql_users->data as $data_user)
			{
				$this->obj_table->data[$i][ $data_user["id"] ] = "N";

				if ($mapping_users_filters[ $data_user["id"] ])
				{
					if (in_array($this->obj_table->data[$i]["id"], $mapping_users_filters[ $data_user["id"] ]))
					{
						$this->obj_table->data[$i][ $data_user["id"] ] = "Y";
					}
				}
			}
		}
		*/

		// process table data
		$this->obj_table->render_table_prepare();


		/*
			Define form structure

			The form defines all the checkboxes to assign/unassign users.
		*/

		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "assignment_adjust";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "assignment/assignment_adjust-process.php";
		$this->obj_form->method		= "post";
		

		// run through user-filter mappings and define checkboxes
		$this->obj_sql_map->string	= "SELECT id_filter, id_user FROM filters_users";

		// run through all users
		foreach ($this->obj_sql_users->data as $data_user)
		{
			// run through all filters
			for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
			{
				// create checkbox
				$structure = NULL;
				$structure["fieldname"]		= "user_". $data_user["id"] ."_filter_". $this->obj_table->data[$i]["id"];
				$structure["type"]		= "checkbox";
				$structure["options"]["label"]	= " ";

				// if there is a mapping between this user and the filter, then we need
				// to mark the checkbox as being checked.
				if ($mapping_users_filters[ $data_user["id"] ])
				{
					if (in_array($this->obj_table->data[$i]["id"], $mapping_users_filters[ $data_user["id"] ]))
					{
						$structure["defaultvalue"] = "on";
					}
				}
				
				$this->obj_form->add_input($structure);
			}
		}


		// submit button
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Update Assignments";
		$this->obj_form->add_input($structure);


		// load any data returned due to errors
		$this->obj_form->load_data_error();


		return 1;
	}


	function render_html()
	{
		print "<h3>FILTER ASSIGNMENT</h3><br>";
		print "<p>This page allows you to configure the filter assignment for users all from a single interface.</p>";


		$this->obj_table->render_options_form();
//		$this->obj_table->render_table_html();


		/*
			Display the table/form

			Due to the need to insert form logic into the table, this table/form is done
			using a custom drawing of the structures assembled during the execute phase.
		*/

		print "<form enctype=\"multipart/form-data\" method=\"". $this->obj_form->method ."\" action=\"". $this->obj_form->action ."\" class=\"form_standard\">";


		print "<table class=\"table_content\" width=\"100%\" cellspacing=\"0\">";
		print "<tr class=\"header\">";

		foreach ($this->obj_table->columns as $column)
		{
			print "<td><b>". $this->obj_table->render_columns[ $column ] ."</b></td>";
		}

		print "</tr>";


		// display all the rows
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			print "<tr>";
			print "<td>". $this->obj_table->data[$i]["filter_title"] ."</td>";
		
			foreach ($this->obj_table->columns as $column)
			{
				if ($column != "filter_title")
				{
					print "<td>";
					$this->obj_form->render_field("user_". $column ."_filter_". $this->obj_table->data[$i]["id"]);
					print "</td>";
				}
			}

			print "</tr>";
		}

		// end table
		print "</table><br>";


		// SUBMIT

		print "<div align=\"right\">";

		$this->obj_form->render_field("submit");

		print "</div>";

		// end form
		print "</form>";
	}



} // end class page_output


?>
