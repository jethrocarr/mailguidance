<?php
/*
	filters.php

	access: public

	Lists all the configured filters in the system and allows them to be edited, deleted or new filters to be added.
*/

class page_output
{
	var $obj_table_list;


	function check_permissions()
	{
		if (user_permissions_get("filters_read"))
		{
			return 1;
		}
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}


	/*
		Define table and load data
	*/
	function execute()
	{
		// define filter list table
		$this->obj_table_list			= New table;
		$this->obj_table_list->language		= $_SESSION["user"]["lang"];
		$this->obj_table_list->tablename	= "filter_list";

		// define all the columns and structure
		$this->obj_table_list->add_column("standard", "title", "filters.title");
		$this->obj_table_list->add_column("standard", "type", "filter_types.type");
		$this->obj_table_list->add_column("standard", "value", "filters.value");

		// defaults
		$this->obj_table_list->columns			= array("title", "type", "value");
		$this->obj_table_list->columns_order		= array("title");
//		$this->obj_table_list->columns_order_options	= array("title", "type", "value");

		// define SQL structure
		$this->obj_table_list->sql_obj->prepare_sql_settable("filters");
		$this->obj_table_list->sql_obj->prepare_sql_addjoin("LEFT JOIN filter_types ON filters.type = filter_types.id");
		$this->obj_table_list->sql_obj->prepare_sql_addfield("id", "filters.id");

		// acceptable filter options
		$structure = NULL;
		$structure["fieldname"] = "searchbox";
		$structure["type"]	= "input";
		$structure["sql"]	= "(filters.title LIKE '%value%' OR filters.type LIKE '%value%')";
		$this->obj_table_list->add_filter($structure);
		

		// load settings from options form
		$this->obj_table_list->load_options_form();

		// fetch filter data
		$this->obj_table_list->generate_sql();
		$this->obj_table_list->load_data_sql();


		return 1;
	}



	function render_html()
	{
		// heading
		print "<h3>EMAIL FILTERS</h3><br><br>";

		// load options form
		$this->obj_table_list->render_options_form();


		// display results
		if (!count($this->obj_table_list->columns))
		{
			format_msgbox("important", "<p>Please select some valid options to display.</p>");
		}
		elseif (!$this->obj_table_list->data_num_rows)
		{
			format_msgbox("info", "<p>There are currently no filters configured.</p>");
		}
		else
		{
			if (user_permissions_get("filters_write"))
			{
				// details link
				$structure = NULL;
				$structure["id"]["column"]	= "id";
				$this->obj_table_list->add_link("tbl_lnk_details", "filters/view.php", $structure);

				// delete link
				$structure = NULL;
				$structure["id"]["column"]	= "id";
				$this->obj_table_list->add_link("tbl_lnk_delete", "filters/delete.php", $structure);
			}


			// display the table
			$this->obj_table_list->render_table_html();
		}
	}


} // end class page_output


?>
