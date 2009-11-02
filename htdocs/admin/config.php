<?php
/*
	admin/config.php
	
	access: admin users only

	Allows administrators to change system-wide settings stored in the config table that affect
	the key operation of the application.
*/

class page_output
{
	var $obj_form;


	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing to do
		return 1;
	}


	function execute()
	{
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->formname = "config";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "admin/config-process.php";
		$this->obj_form->method = "post";


		// mail default options
		$structure = NULL;
		$structure["fieldname"]				= "MAIL_DEFAULT_MODE";
		$structure["type"]				= "radio";
		$structure["options"]["prelabel"]		= "If an email does not match any of the configured filters, then:<br>";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$structure["values"]				= array("archive", "drop", "forward");
		$structure["translations"]			= array("archive"	=> "Save email to user inbox",
									"drop"		=> "Delete unmatched email",
									"forward"	=> "Forward email to another address");
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "MAIL_DEFAULT_ADDRESS";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
	
		$this->obj_form->add_action("MAIL_DEFAULT_MODE", "default", "MAIL_DEFAULT_ADDRESS", "hide");
		$this->obj_form->add_action("MAIL_DEFAULT_MODE", "forward", "MAIL_DEFAULT_ADDRESS", "show");
		



		// mail archive issues
		$structure = NULL;
		$structure["fieldname"]				= "ARCHIVE_INBOX_ALLMAIL";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Leave a copy of all incomming email messages in the inbox of the mail account.";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
	
		$structure = NULL;
		$structure["fieldname"]				= "ARCHIVE_FILTER_FOLDERS";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Create a folder for each filter and store a copy of all emails recieved that match that filter in the folder.";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
	
/*
		MailGuidance will be used for internal system, brute-force blacklisting
		is not really a requirement.

		// security options
		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_ENABLE";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable to prevent brute-force login attempts";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_LIMIT";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
*/


		// misc	
		$structure = form_helper_prepare_timezonedropdown("TIMEZONE_DEFAULT");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
		
		$structure = NULL;
		$structure["fieldname"]				= "DATEFORMAT";
		$structure["type"]				= "radio";
		$structure["values"]				= array("yyyy-mm-dd", "mm-dd-yyyy", "dd-mm-yyyy");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "UPLOAD_MAXBYTES";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$structure["options"]["label"]			= " Bytes. Server maximum is ". ini_get('upload_max_filesize') .", to increase server limit, you must edit php.ini";
		$this->obj_form->add_input($structure);
		


		// submit section
		$structure = NULL;
		$structure["fieldname"]			= "submit";
		$structure["type"]			= "submit";
		$structure["defaultvalue"]		= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["config_mail_default"]	= array("MAIL_DEFAULT_MODE", "MAIL_DEFAULT_ADDRESS");
		$this->obj_form->subforms["config_mail_archive"]	= array("ARCHIVE_INBOX_ALLMAIL", "ARCHIVE_FILTER_FOLDERS");
//		$this->obj_form->subforms["config_security"]		= array("BLACKLIST_ENABLE", "BLACKLIST_LIMIT");
		$this->obj_form->subforms["config_dateandtime"]		= array("DATEFORMAT", "TIMEZONE_DEFAULT");
		$this->obj_form->subforms["submit"]			= array("submit");

		if ($_SESSION["error"]["message"])
		{
			// load error datas
			$this->obj_form->load_data_error();
		}
		else
		{
			// fetch all the values from the database
			$sql_config_obj		= New sql_query;
			$sql_config_obj->string	= "SELECT name, value FROM config ORDER BY name";
			$sql_config_obj->execute();
			$sql_config_obj->fetch_array();

			foreach ($sql_config_obj->data as $data_config)
			{
				$this->obj_form->structure[ $data_config["name"] ]["defaultvalue"] = $data_config["value"];
			}

			unset($sql_config_obj);
		}
	}



	function render_html()
	{
		// Title + Summary
		print "<h3>CONFIGURATION</h3><br>";
		print "<p>MailGuidance is a flexible application, you can use this page to enable features such as mail archiving and the default address to deliver unread messages to.</p>";
	
		// display the form
		$this->obj_form->render_form();
	}

	
}

?>
