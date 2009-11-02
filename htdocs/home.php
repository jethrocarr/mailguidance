<?php
/*
	home.php

	Summary page to MailGuidance
*/

if (!user_online())
{
	// Because this is the default page to be directed to, if the user is not
	// logged in, they should go straight to the login page.
	//
	// All other pages will display an error and prompt the user to login.
	//
	include_once("user/login.php");
}
else
{
	class page_output
	{
		function check_permissions()
		{
			// this page has a special method for handling permissions - please refer to code comments above
			return 1;
		}

		function check_requirements()
		{
			// nothing todo
			return 1;
		}
			
		function execute()
		{
			// nothing todo
			return 1;
		}

		function render_html()
		{
			print "<h3>OVERVIEW</h3>";
			print "<p>Welcome to <a target=\"new\" href=\"http://www.amberdms.com/mailguidance\">MailGuidance</a>, an open source application for generating procmail filtering rulesets, ideal for use by teams of system administrators for easily defining who recives messages from particular servers or customers.</p>";


			/*
				Check holiday mode
			*/
			print "<br><br>";
			print "<h3>DELIVERY STATUS</h3>";

			$obj_sql		= New sql_query;
			$obj_sql->string	= "SELECT id, id_user_redirect FROM users_holidaymode WHERE id_user='". $_SESSION["user"]["id"] ."'";
			$obj_sql->execute();

			if ($obj_sql->num_rows())
			{
				$obj_sql->fetch_array();

				if ($obj_sql->data[0]["id_user_redirect"])
				{
					$username = sql_get_singlevalue("SELECT realname as value FROM users WHERE id='". $obj_sql->data[0]["id_user_redirect"] ."'");

					format_msgbox("important", "<p><img src=\"images/icons/cross_16.gif\"> Holiday mode enabled, any emails delivered to you will instead be redirected to <u>$username</u>.</p>");
				}
				else
				{
					format_msgbox("important", "<p><img src=\"images/icons/cross_16.gif\"> Holiday mode enabled, no emails will be delivered to your user account. Emails will not be redirected anywhere.</p>");
				}
			}
			else
			{
				format_msgbox("green", "<p><img src=\"images/icons/tick_16.gif\"> Any emails maching the filters for your user, will be delivered to your mailbox.</p>");
			}


			/*
				Help/Basic Guide
			*/
			print "<br><br>";
			print "<h3>GETTING STARTED</h3>";
			print "<p>To get started:"
				."<ul>"
				."<li>Create all the users required (if not done so already by your administrator) in the User Management page.</li>"
				."<li>Use the Filter Configuration page to create the filters you require - these filters can be using one of the pre-defined types, or you can enter custom procmail compatible rulesets.</li>"
				."<li>Once you've added all the desired rules, go to the assignment page to setup who should recieve the emails that match each filter.</li>"
				."</ul>";
		}
	}
}

?>
