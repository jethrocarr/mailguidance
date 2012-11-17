<?php
/*
	assignment/assignment.php

	access:
		assignment_read		(Read-only - just displays configuration)
		assignment_write	(Writable - shows form for enabling/disabling filters for users)

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
		
		$this->obj_table->columns_order            = array("filter_title");

		
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


		// hidden fields
		//
		// These fields are used to pass back the IDs of the filters and users
		// that have been processed.
		//
		// We need to do this, so that when the user makes an adjustment on a page that
		// has been filtered to only show some users/filters we will ignore any other
		// users/filters when we apply the changes.
		//
		$structure = NULL;
		$structure["fieldname"] 	= "users";
		$structure["type"]		= "hidden";
		
		foreach ($this->obj_table->columns as $column)
		{
			if ($column != "filter_title")
			{
				$list_users[] = $column;
			}
		}
		
		$structure["defaultvalue"]	= format_arraytocommastring($list_users);
		$this->obj_form->add_input($structure);


		$structure = NULL;
		$structure["fieldname"] 	= "filters";
		$structure["type"]		= "hidden";
		
		foreach ($this->obj_table->data as $data_table)
		{
			$list_filters[] = $data_table["id"];
		}
		
		$structure["defaultvalue"]	= format_arraytocommastring($list_filters);
		$this->obj_form->add_input($structure);



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
		print "<p>This page allows you to configure the filter assignment for users all from a single interface by checking filters on/off for specific users. If you want to simply disable all filters for a specific user, put the user into holiday mode.</p>";

		$this->obj_table->render_options_form();


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

					if (user_permissions_get("assignment_write"))
					{
						$this->obj_form->render_field("user_". $column ."_filter_". $this->obj_table->data[$i]["id"]);
					}
					else
					{
						if ($this->obj_form->structure["user_". $column ."_filter_". $this->obj_table->data[$i]["id"] ]["defaultvalue"] == "on")
						{
							print "<img src=\"images/icons/tick_16.gif\" alt=\"Y\">";
						}
						else
						{
							print "<img src=\"images/icons/cross_16.gif\" alt=\"N\">";
						}
					}

					print "</td>";
				}
			}

			print "</tr>";
		}

		// end table
		print "</table><br>";


		// SUBMIT
		if (user_permissions_get("assignment_write"))
		{
			$this->obj_form->render_field("users");
			$this->obj_form->render_field("filters");

			print "<div align=\"right\">";
			$this->obj_form->render_field("submit");
			print "</div>";
		}
		else
		{
			format_msgbox("locked", "<p>If you wish to adjust the filter &lt;-&gt user assignement, you will need your administrator to give you assignment_write privillages</p>");
		}

		// end form
		print "</form>";
	}



} // end class page_output


?>
