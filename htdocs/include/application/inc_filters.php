<?php
/*
	include/application/inc_filters.php

	Support class for handling filters.
*/




/*
	CLASS: filters

	Provides functions for handling filters.
*/

class filters
{
	var $id;		// holds filter ID
	var $data;		// holds values of record fields



	/*
		verify_id

		Checks that the provided ID is a valid filter

		Results
		0	Failure to find the ID
		1	Success - filter exists
	*/

	function verify_id()
	{
		log_debug("inc_filters", "Executing verify_id()");

		if ($this->id)
		{
			$sql_obj		= New sql_query;
			$sql_obj->string	= "SELECT id FROM `filters` WHERE id='". $this->id ."' LIMIT 1";
			$sql_obj->execute();

			if ($sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id


	/*
		verify_unique_title

		Check that the filter name is unique.

		Results
		0	Failure - filter already exists
		1	Success - filter does not exist
	*/

	function verify_unique_title()
	{
		log_debug("inc_filters", "Executing verify_unique_title()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `filters` WHERE title='". $this->data["title"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_unique_title


	/*
		verify_unique_logic

		Check that the filter logic is unique.

		Results
		0	Failure - filter already exists
		1	Success - filter does not exist
	*/

	function verify_unique_logic()
	{
		log_debug("inc_filters", "Executing verify_unique_logic()");

		$sql_obj			= New sql_query;
		$sql_obj->string		= "SELECT id FROM `filters` WHERE type='". $this->data["type"] ."' AND value='". $this->data["name_customer"] ."' ";

		if ($this->id)
			$sql_obj->string	.= " AND id!='". $this->id ."'";

		$sql_obj->string		.= " LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			return 0;
		}
		
		return 1;

	} // end of verify_unique_logic



	/*
		load_data

		Load the filter data into the $this->data array.

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("inc_filters", "Executing load_data()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT * FROM filters WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();

			$this->data = $sql_obj->data[0];

			return 1;
		}

		// failure
		return 0;

	} // end of load_data





	/*
		action_create

		Create a new filter based on the data in $this->data - usually called by action_update
		rather than directly by another function.

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("inc_filters", "Executing action_create()");

		// create a new filter
		$sql_obj		= New sql_query;
		$sql_obj->string	= "INSERT INTO `filters` (title) VALUES ('". $this->data["title"]. "')";
		$sql_obj->execute();

		$this->id = $sql_obj->fetch_insert_id();

		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a filter's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("inc_filters", "Executing action_update()");


		/*
			Start Transaction
		*/
		$sql_obj = New sql_query;
		$sql_obj->trans_begin();



		/*
			If no ID supplied, create a new filter first
		*/
		if (!$this->id)
		{
			$mode = "create";

			if (!$this->action_create())
			{
				return 0;
			}
		}
		else
		{
			$mode = "update";
		}



		/*
			Update Filter Details
		*/

		$sql_obj->string	= "UPDATE `filters` SET "
						."title='". $this->data["title"] ."', "
						."type='". $this->data["type"] ."', "
						."value='". $this->data["value"] ."' "
						."WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();

		


		/*
			Commit
		*/

		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "inc_filters", "An error occured when updating filter details.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "inc_filters", "Filter details successfully updated.");
			}
			else
			{
				log_write("notification", "inc_filters", "Filter successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update




	/*
		action_delete

		Deletes a filter.

		Results
		0	failure
		1	success
	*/
	function action_delete()
	{
		log_debug("inc_filters", "Executing action_delete()");

		/*
			Start Transaction
		*/

		$sql_obj = New sql_query;
		$sql_obj->trans_begin();


		/*
			Delete Filter
		*/
			
		$sql_obj->string	= "DELETE FROM filters WHERE id='". $this->id ."' LIMIT 1";
		$sql_obj->execute();


		/*
			Delete Filter <-> User assignments
		*/

		$sql_obj->string	= "DELETE FROM filters_users WHERE id_filter='". $this->id ."'";
		$sql_obj->execute();


		/*
			Commit
		*/
		
		if (error_check())
		{
			$sql_obj->trans_rollback();

			log_write("error", "inc_filters", "An error occured whilst trying to delete the filter.");

			return 0;
		}
		else
		{
			$sql_obj->trans_commit();

			log_write("notification", "inc_filters", "Filter has been successfully deleted.");

			return 1;
		}
	}


} // end of class:filters


?>
