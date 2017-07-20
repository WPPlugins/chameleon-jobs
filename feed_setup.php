<?php
error_reporting(E_ALL ^ E_NOTICE);
/*
  Plugin Name: Chameleoni.com Job Feed
  Plugin URI: https://www.chameleoni.com/chameleoni-website-design/wordpress-website-integration/
  Description: Plugin for displaying jobs from chameleoni.com
  Author: Chameleoni.com
  Version: 1.2.5
  Author URI: https://www.chameleoni.com/
 */

 
function boot_session() {
    session_start();
}

session_start();
define('JOBINFO_FOLDER', dirname(plugin_basename(__FILE__)));
@define('JOBINFO_URL', plugins_url() . '/' . JOBINFO_FOLDER);

function cjf_jobadmin() {
    include('job_admin.php');
}

add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode', 11);

function cjf_jobadmin_menu() {
    @add_menu_page(
                    "Jobs Setting", "Jobs Setting", 8, __FILE__, "cjf_jobadmin", "/wp-admin/images/generic.png");

    add_submenu_page(__FILE__, 'Job Listing', 'Job Listing', 'manage_options', __FILE__ . '/JobListing', 'cjf_listing_admin_fun');
}

function cjf_listing_admin_fun() {
    include 'job_listing_admin_page.php';
}

function cjf_jobs_install() {
    // do NOT forget this global
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    // this if statement makes sure that the table doe not exist already
    $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		  	id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_id INT( 11 ) NOT NULL,
     		job_id VARCHAR(255)  NOT NULL ,
  			job_title VARCHAR(255)  NOT NULL , 
			name VARCHAR(255)  NOT NULL ,
			email VARCHAR(255)  NOT NULL ,
			cv VARCHAR(255)  NOT NULL ,
			date DATE NOT NULL ,
			ordering INT(11)  NOT NULL ,
			state TINYINT(1)  NOT NULL ,
			checked_out INT(11)  NOT NULL ,
			checked_out_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			created_by INT(11)  NOT NULL ,
			UNIQUE KEY `id` (`id`));";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta(@$wpdb->prepare($sql));
    $table_name2 = $wpdb->prefix . 'jobs_settings';
    // this if statement makes sure that the table doe not exist already
    $sql2 = "CREATE TABLE IF NOT EXISTS " . $table_name2 . " (
		  	id mediumint(9) NOT NULL AUTO_INCREMENT,
     		http_url VARCHAR(255)  NOT NULL ,
			authKey  VARCHAR(255)  NOT NULL ,
			authPassword  VARCHAR(255)  NOT NULL ,
			aPIKey  VARCHAR(255)  NOT NULL ,
			userName VARCHAR(255)  NOT NULL ,
			thank_you_page mediumint(9),
  			feed_location VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_type VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_salary VARCHAR(255)  NOT NULL DEFAULT '0',
			feed_summary VARCHAR(255)  NOT NULL DEFAULT '0',
			summary_characters mediumint(9),
			number_of_jobsper_Page mediumint(9),
			UNIQUE KEY `id` (`id`));";
    dbDelta(@$wpdb->prepare($sql2));
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'jobs_settings'));
    if (!$row) {
        $table_name3 = $wpdb->prefix . 'jobs_settings';
        $sql3 = "INSERT INTO " . $table_name3 . " VALUES(null,'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx','Guest','KgwLLm7TL6G6','D12E9CF3-F742-47FC-97CB-295F4488C2FA','David','','0','0','0','0',200,5)";
        dbDelta(@$wpdb->prepare($sql3));
    }
}

function cjf_jobs_install_del() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jobs';
    $structure1 = "DROP TABLE $table_name";
    $wpdb->query(@$wpdb->prepare($structure1));
}

function cjf_settings_install_del() {
    global $wpdb;
    $table_name3 = $wpdb->prefix . 'jobs_settings';
    $structure3 = "DROP TABLE $table_name3";
    $wpdb->query(@$wpdb->prepare($structure3));
}

function cjf_front_view_job_func() {
    switch (@$_REQUEST['task']) {
        case 'jobdetails':
            global $post;
            $pageid = $post->ID;
            global $wpdb;

            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $feed_url = $setting->http_url; //'http://jobs.chameleoni.com/xmlfeed.aspx';
            $AuthKey = $setting->authKey;
            $AuthPassword = $setting->authPassword;
            $APIKey = $setting->aPIKey;
            $UserName = $setting->userName;
            $Thank_you_page = get_option("thank_you_page");
            $feed_location = $setting->feed_location;
            $feed_type = $setting->feed_type;
            $feed_salary = $setting->feed_salary;
            $feed_summary = $setting->feed_summary;
            $jobId = $_REQUEST['jobid'];


            if (isset($_GET['action']) && $_GET['action'] == "apply") {
                $contact_id = $_SESSION['ContactId'];

                if ($contact_id) {

                    // Apply to the job
                    $job_name = $_GET['job_title'];
                    $job_id = $_GET['jobid'];
                    $job_ref = $_GET['job_ref'];

                    $res_job_apply = '<ChameleonIAPI><Method>CandidateApplication</Method>
				  <APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData>
                                <Input Name="RequirementId" Value="' . $job_id . '"/>
<Input Name="CandidateId" Value="' . $contact_id . '"/>
<Input Name="WebApplicationTaskTypeId" Value="119"/>
<Input Name="ApplicationText" Value="' . $job_name . '"/>
<Input Name="MailName" Value="Email - Application"/>
<Input Name="SiteName" Value=""/>
				</InputData>
				</ChameleonIAPI>';

                    $encoded = 'Xml=' . $res_job_apply . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                    $ch = curl_init($feed_url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $result = str_replace('utf-16', 'utf-8', $result);
                    $xml = simplexml_load_string($result);
                    $json = json_encode($xml);
                    $array_res = json_decode($json, TRUE);
                    if ($array_res['Status'] == "Pass") {

                        echo "<div class='alert alert-success'>Thank you for your application for vacancy " . $job_ref . ". One of our consultants will be in touch shortly.
<a href='" . get_option("jobs_page_url") . "'>Please click here to go back to our live vacancies.</a></div><style> #vacancy_details{ display: none; } </style>";
                    }
                }
            }

            $request = '
						<ChameleonIAPI>
							<Method>SearchVacancies2</Method>
							<APIKey>' . $APIKey . '</APIKey>
							<UserName>' . $UserName . '</UserName>
							<Filter>
								<!-- Options Placeholder -->
                                                                <Param Name="VacancyId" Value="' . $jobId . '"  Operator="="/>
							</Filter>	
						</ChameleonIAPI>';
            $encoded = 'Xml=' . $request . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
            $ch = curl_init($feed_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = str_replace('utf-16', 'utf-8', $result);
            $xml = simplexml_load_string($result);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);

            $job_details = array();
            if (count($array['Vacancies']['Vacancy'])) {
                $job_details = @$array['Vacancies']['Vacancy'];
                //$session->set('recent_job',$this->job);
            }
            //print_r($job_details);
            $job_details_str = '<div id="vacancy_details">
					<p><span class="location"><label><b>Job Title:</b></label>' . esc_html($job_details['JobTitle']) . '</span></p>
					<p><span class="location"><label><b>Location:</b></label>' . esc_html($job_details['LocationTag']) . '</span></p>
					<p><span class="type"><label><b>Job Reference:</b></label>' . esc_html($job_details['Reference']) . '</span></p>
					<p><span class="type"><label><b>Type:</b></label>' . $job_details['JobType'] . '</span></p>
					<p><span class="salary"><label><b>Salary: </b></label>' . esc_html(($job_details['Pay']));
            $job_details_str .= '</span></p>
					<p><span class="type"><label><b>Close Date:</b></label>' . esc_html(date("d/m/Y", strtotime($job_details['DateClosed']))) . '</span></p>
					<p><span class="summary"><label><b>Summary: </b></label><div class="job_summary">' . nl2br(esc_html($job_details['Description'])) . '</div></span></p>
					<p><span class="type"><label><b>Benefits:</b></label>' . esc_html($job_details['Benefits']) . '</span></p>
					<p> <span class="consultantname"><label><b>Consultant Name:</b></label>' . esc_html($job_details['ConsultantName']) . '</span></p>
					<p><span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto:' . $job_details['ConsultantEmail'] . '">' . esc_html($job_details['ConsultantEmail']) . '</a></span></p>
				 <span>
                                 <!--<a class="view_morebtn" href="index.php?page_id=' . $pageid . '&task=apply&jobid=' . $_REQUEST['jobid'] . '&job_title=' . esc_html($job_details['JobTitle']) . '">Apply</a>-->';

            if (isset($_SESSION['ContactId'])) {
                $res_job_apply = '<?xml version="1.0" encoding="utf-16" ?>
                                       <ChameleonIAPI>
                                           <Method>CheckAppliedStatus</Method>
                                           <APIKey>' . $APIKey . '</APIKey>
                                           <UserName>' . $UserName . '</UserName>
                                           <InputData>
                                                <Input Name="RequirementId" Value="' . $_REQUEST['jobid'] . '" />
                                                <Input Name="CandidateId" Value="' . $_SESSION['ContactId'] . '" />
                                           </InputData>
                                       </ChameleonIAPI>';

                $encoded = 'Xml=' . $res_job_apply . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                $ch = curl_init($feed_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                $result = curl_exec($ch);


                curl_close($ch);

                $result = str_replace('utf-16', 'utf-8', $result);
                $xml = simplexml_load_string($result);
                $json = json_encode($xml);
                $array_res2 = json_decode($json, TRUE);
                if ($array_res2['AppliedStatus'] == 1) {
                    $btns = '<a class="view_morebtn" href="#" disabled>Already Applied</a>';
                } elseif ($array_res2['AppliedStatus'] == 0) {
                    $btns = '<a class="view_morebtn" href="index.php?page_id=' . $pageid . '&task=jobdetails&action=apply&jobid=' . $_REQUEST['jobid'] . '&job_title=' . esc_html($job_details['JobTitle']) . '&job_ref=' . $job_details['Reference'] . '">Apply</a>';
                }
            } else {
                $btns = '<a class="view_morebtn" href="index.php?page_id=' . $pageid . '&task=login_apply&jobid=' . $_REQUEST['jobid'] . '&job_title=' . esc_html($job_details['JobTitle']) . '&job_ref=' . $job_details['Reference'] . '">Login & Apply</a>
                                     <a class="view_morebtn" href="index.php?page_id=' . $pageid . '&task=register_apply&jobid=' . $_REQUEST['jobid'] . '&job_title=' . esc_html($job_details['JobTitle']) . '&job_ref=' . $job_details['Reference'] . '">Register & Apply</a>';
            }
            $job_details_str .= $btns . '</span>
				<a href="?page_id=' . $pageid . '" class="view_morebtn">Back to Results</a>
				</p>
				</div>';


            return $job_details_str;
            break;
        case 'register_apply':
            global $post;
            $pageid = $post->ID;
            global $wpdb;
            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $job_settings_str = '';
            if (isset($_POST['apply-submit']) == "Submit") {

                global $wpdb;
                $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
                $feed_url = $setting->http_url; //'http://jobs.chameleoni.com/xmlfeed.aspx';
                $AuthKey = $setting->authKey;
                $AuthPassword = $setting->authPassword;
                $APIKey = $setting->aPIKey;
                $UserName = $setting->userName;
                $Thank_you_page = get_option("thank_you_page");
                $feed_location = $setting->feed_location;
                $feed_type = $setting->feed_type;
                $feed_salary = $setting->feed_salary;
                $feed_summary = $setting->feed_summary;
                $page_size = $setting->number_of_jobsper_Page;

                $request_email = '<ChameleonIAPI>
				<Method>CheckEmail</Method>
				<APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				 <InputData>
				 <Input Name="Email" Value="' . sanitize_email($_POST['Email']) . '" />
				 </InputData>
				</ChameleonIAPI>';

                $encoded = 'Xml=' . $request_email . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                $ch = curl_init($feed_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                $result_email = curl_exec($ch);
                $result_email = str_replace('utf-16', 'utf-8', $result_email);
                $xml = simplexml_load_string($result_email);
                $json = json_encode($xml);
                $array_email = json_decode($json, TRUE);

                // end verification 



                if ($array_email['ContactCount'] == 0) {


                    $request = '<ChameleonIAPI>
				<Method>CandidateRegister</Method>
				<APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData>
						<Input Name="TitleId" Value="1" />
						<Input Name="Forename" Value="' . sanitize_text_field($_POST['Forename']) . '" />
						<Input Name="Surname" Value="' . sanitize_text_field($_POST['Surname']) . '" />
						<Input Name="Email" Value="' . sanitize_email($_POST['Email']) . '" />
						<Input Name="WebPassword" Value="' . sanitize_text_field($_POST['WebPassword']) . '" />
						<Input Name="HomeTelNo" Value="' . sanitize_text_field($_POST['HomeTelNo']) . '" />
						<Input Name="MobileTelNo" Value="' . sanitize_text_field($_POST['MobileTelNo']) . '" />
						<Input Name="WorkTelNo" Value="' . sanitize_text_field($_POST['WorkTelNo']) . '" />
				</InputData>
			</ChameleonIAPI>';


                    $encoded = 'Xml=' . $request . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                    $ch = curl_init($feed_url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);
                    $result = curl_exec($ch);
                    $result = str_replace('utf-16', 'utf-8', $result);
                    $xml = simplexml_load_string($result);
                    $json = json_encode($xml);
                    $array_contactid = json_decode($json, TRUE);
                    curl_close($ch);

                    //if(!session_id())
                    //session_start();


                    $ContactId = $array_contactid['ContactId'];
                    $_SESSION['ContactId'] = $ContactId;
                    if (isset($_FILES['cv']) and strlen($_FILES['cv']['name'])) {
                        $cv_parts = explode('.', @$_FILES['cv']['name']);
                        $ftype = end($cv_parts);
                        $fcontent = file_get_contents($_FILES['cv']['tmp_name']);
                        $fcontent = base64_encode($fcontent);

                        $res_conid = '<ChameleonIAPI><Method>AttachCV</Method>
				  <APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData><Input Name="ContactId" Value="' . $ContactId . '" />
				<Input Name="CVDocType" Value="' . $ftype . '" />
				 <Input Name="CVBase64" Value="' . $fcontent . '"/>
				</InputData>
				</ChameleonIAPI>';

                        $encoded = 'Xml=' . $res_conid . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                        $ch = curl_init($feed_url);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_VERBOSE, 0);
                        $result = curl_exec($ch);


                        curl_close($ch);
                    }


                    $_SESSION['Forename'] = '';
                    $_SESSION['Surname'] = '';
                    $_SESSION['WebPassword'] = '';
                    $_SESSION['HomeTelNo'] = '';
                    $_SESSION['MobileTelNo'] = '';
                    $_SESSION['WorkTelNo'] = '';
                    $_SESSION['cv'] = '';
                    $_SESSION['Email'] = '';


                    // Apply to the job
                    $job_name = $_GET['job_title'];
                    $job_id = $_GET['jobid'];
                    $job_ref = $_GET['job_ref'];

                    $res_job_apply = '<ChameleonIAPI><Method>CandidateApplication</Method>
				  <APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData>
                                <Input Name="RequirementId" Value="' . $job_id . '"/>
<Input Name="CandidateId" Value="' . $ContactId . '"/>
<Input Name="WebApplicationTaskTypeId" Value="119"/>
<Input Name="ApplicationText" Value="' . $job_name . '"/>
<Input Name="MailName" Value="Email - Application"/>
<Input Name="SiteName" Value=""/>
				</InputData>
				</ChameleonIAPI>';

                    $encoded = 'Xml=' . $res_job_apply . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                    $ch = curl_init($feed_url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);
                    $result = curl_exec($ch);


                    curl_close($ch);

                    $result = str_replace('utf-16', 'utf-8', $result);
                    $xml = simplexml_load_string($result);
                    $json = json_encode($xml);
                    $array_res = json_decode($json, TRUE);
                    if ($array_res['AppliedStatus'] == 0) {
                        echo "<div class='alert alert-success'>Thank you for your application for vacancy " . $job_ref . ". One of our consultants will be in touch shortly.
<a href='" . get_option("jobs_page_url") . "'>Please click here to go back to our live vacancies.</a><style> #form-application{ display:none !important; } #apply_head{ display:none; } </style></div>";
                    } elseif ($array_res['AppliedStatus'] == 1) {
                        echo "<div class='alert alert-danger'>You already applied</div>";
                    }

//                    if ($setting->thank_you_page == 0) {
//
//
//                        $redpage = "index.php";
//                    } else {
//                        $redpage = "?page_id=$setting->thank_you_page";
//                    }
                } // if email not exit;
                else {


                    //if (!session_id())
                    //  session_start();


                    $_SESSION['Forename'] = sanitize_text_field($_POST['Forename']);
                    $_SESSION['Surname'] = sanitize_text_field($_POST['Surname']);
                    $_SESSION['WebPassword'] = sanitize_text_field($_POST['WebPassword']);
                    $_SESSION['HomeTelNo'] = sanitize_text_field($_POST['HomeTelNo']);
                    $_SESSION['MobileTelNo'] = sanitize_text_field($_POST['MobileTelNo']);
                    $_SESSION['WorkTelNo'] = sanitize_text_field($_POST['WorkTelNo']);
                    $_SESSION['cv'] = sanitize_file_name($_FILES['cv']['name']);
                    $_SESSION['Email'] = sanitize_email($_POST['Email']);


                    $job_settings_str = '<div class="alert alert-danger">Your Email Address Already Exists</div>';
                }
            }
            //$job_settings_str = '';
            $job_settings_str .= '<script type="application/javascript">
				function app_validate()
				{
					validation_string = new Array();
					var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;

					if(document.getElementById("Forename").value == "")
					{
						document.getElementById("Forename").focus();
						validation_string.push("Forename");
					}
					if(document.getElementById("Surname").value == "")
					{
						document.getElementById("Surname").focus();
						validation_string.push("Surname");
					}
                                        if(document.getElementById("WebPassword").value == "")
					{
						document.getElementById("WebPassword").focus();
						validation_string.push("Password");
					}
					if(document.getElementById("Email").value == "")
					{
						validation_string.push("Email");
						document.getElementById("Email").focus();
					}
					
					if(document.getElementById("Email").value != "" && !document.getElementById("Email").value.match(emailExp))
					{
						validation_string.push("Valid Email address");
						document.getElementById("Email").focus();
					}
                                        if(document.getElementById("MobileTelNo").value == "")
					{
						document.getElementById("MobileTelNo").focus();
						validation_string.push("MobileTelNo");
                                        }
					if(validation_string != ""){
						alert("Please enter "+validation_string.join(", ").toString());
						return false;
					}
					
					return true;
				}
				</script>';
            $job_settings_str .= '<div class="application-edit front-end-edit">
					<h3 id="apply_head">Apply for position: ' . $_REQUEST['job_title'] . '</h3>
					<form id="form-application" action="" method="post" class="form-validate" enctype="multipart/form-data" >        		
						<input  type="hidden" class="required"  id="job_id" name="job_id"   value="' . $_REQUEST['jobid'] . '">
						<input type="hidden" class="required" value="' . $_REQUEST['job_title'] . '" id="job_title" name="job_title">
						<table border="0">
							<tr>							
								<td>Forename <span class="star">&nbsp;*</span> </td>
								<td><input type="hidden" name="TitleId" value="1"><input type="text" name="Forename" id="Forename"  value="' . @$_SESSION['Forename'] . '" /></td>
							</tr>
							<tr>
								<td>Surname <span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Surname" id="Surname"  value="' . @$_SESSION['Surname'] . '" /></td>
							</tr>
							<tr>
								<td>Email<span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Email" id="Email" value="' . @$_SESSION['Email'] . '" /></td>
							</tr>
							<tr>
								<td>Password<span class="star">&nbsp;*</span></td>
								<td><input type="password" name="WebPassword" id="WebPassword"  value="' . @$_SESSION['WebPassword'] . '" /></td>
							</tr> <tr>
								<td>Home Tel </td>
								<td><input type="text" name="HomeTelNo" id="HomeTelNo"  value="' . @$_SESSION['HomeTelNo'] . '" /></td>
							</tr> <tr>
								<td>Mobile Tel<span class="star">&nbsp;*</span></td>
								<td><input type="text" name="MobileTelNo" id="MobileTelNo"  value="' . @$_SESSION['MobileTelNo'] . '" /></td>
							</tr> <tr>
								<td>Work Tel  </td>
								<td><input type="text" name="WorkTelNo" id="WorkTelNo"  value="' . @$_SESSION['WorkTelNo'] . '" /></td>
							</tr>
							<tr>
								<td>Attach CV </td>
								<td><input type="file" name="cv"  id="cv"  value="' . @$_SESSION['cv'] . '" /></td>
							</tr>
							<tr>
								<td><input type="submit" value="Submit" name="apply-submit"  class="jobapply" onclick="return app_validate();"/>
								
								</td>
							</tr>
						</table>
					 </form>
				</div>';
            return $job_settings_str;
            break;
        case 'login_apply':
            global $post;
            $pageid = $post->ID;
            global $wpdb;
            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $job_settings_str = '';
            if (isset($_POST['login_submit']) == "Submit") {
                global $wpdb;
                $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
                $feed_url = $setting->http_url; //'http://jobs.chameleoni.com/xmlfeed.aspx';
                $AuthKey = $setting->authKey;
                $AuthPassword = $setting->authPassword;
                $APIKey = $setting->aPIKey;
                $UserName = $setting->userName;
                $Thank_you_page = get_option("thank_you_page");
                $feed_location = $setting->feed_location;
                $feed_type = $setting->feed_type;
                $feed_salary = $setting->feed_salary;
                $feed_summary = $setting->feed_summary;
                $page_size = $setting->number_of_jobsper_Page;

                $request_login = '
        <?xml version="1.0" encoding="utf-16" ?>
<ChameleonIAPI>
    <Method>CandidateLogin</Method>
    <APIKey>' . $APIKey . '</APIKey>
    <UserName>' . $UserName . '</UserName>
    <InputData>
            <Input Name="Email" Value="' . sanitize_email($_POST['email']) . '" />
            <Input Name="Password" Value="' . $_POST['password'] . '" />
    </InputData>
</ChameleonIAPI>';

                $encoded = 'Xml=' . $request_login . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                $ch = curl_init($feed_url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_VERBOSE, 0);
                $result = curl_exec($ch);

                curl_close($ch);

                //if (!session_id())
                //  session_start();

                $result = str_replace('utf-16', 'utf-8', $result);
                $xml = simplexml_load_string($result);
                $json = json_encode($xml);
                $array_contactid = json_decode($json, TRUE);
                if (isset($array_contactid['ContactId']) && !empty($array_contactid['ContactId'])) {
                    $_SESSION['ContactId'] = $array_contactid['ContactId'];

                    // Apply to the job
                    $job_name = $_GET['job_title'];
                    $job_id = $_GET['jobid'];
                    $job_ref = $_GET['job_ref'];

                    $res_job_apply = '<ChameleonIAPI><Method>CandidateApplication</Method>
				  <APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData>
                                <Input Name="RequirementId" Value="' . $job_id . '"/>
<Input Name="CandidateId" Value="' . $_SESSION['ContactId'] . '"/>
<Input Name="WebApplicationTaskTypeId" Value="119"/>
<Input Name="ApplicationText" Value="' . $job_name . '"/>
<Input Name="MailName" Value="Email - Application"/>
<Input Name="SiteName" Value=""/>
				</InputData>
				</ChameleonIAPI>';

                    $encoded = 'Xml=' . $res_job_apply . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
                    $ch = curl_init($feed_url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);
                    $result = curl_exec($ch);

                    curl_close($ch);

                    $result = str_replace('utf-16', 'utf-8', $result);
                    $xml = simplexml_load_string($result);
                    $json = json_encode($xml);
                    $array_res = json_decode($json, TRUE);
                    if ($array_res['AppliedStatus'] == 0) {

                        echo "<div class='alert alert-success'>Thank you for your application for vacancy " . $job_ref . ". One of our consultants will be in touch shortly.
<a href='" . get_option("jobs_page_url") . "'>Please click here to go back to our live vacancies.</a></div><style>  #apply_head{ display:none; } .front-end-edit{ display:none; }  </style>";
                    } elseif ($array_res['AppliedStatus'] == 1) {
                        echo "<div class='alert alert-danger'>You already applied</div>";
                    }

//                    if ($setting->thank_you_page == 0) {
//
//
//                        $redpage = "index.php";
//                    } else {
//                        $redpage = "?page_id=$setting->thank_you_page";
//                    }
                } else {
                    echo "<div class='alert alert-danger'>Error no such user!</div>";
                }
            }
            //$job_settings_str = '';
            $job_settings_str .= '<script type="application/javascript">
				function app_validate()
				{
					validation_string = new Array();
        if (document.getElementById("email").value == "")
        {
            document.getElementById("email").focus();
            validation_string.push("email");
        }else if (document.getElementById("email").value != "")
        {
            var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
            if (!document.getElementById("email").value.match(emailExp)) {
                document.getElementById("email").focus();
                validation_string.push("Valid Email Address");
            } 
        }
        if (document.getElementById("password").value == "")
        {
            document.getElementById("password").focus();
            validation_string.push("password");
        }
        if (validation_string != "") {
            alert("Please enter " + validation_string.join(", ").toString());
            return false;
        }
        
				}
				</script>';

            $job_settings_str .= '<div class="application-edit front-end-edit">
					<h3 id="apply_head">Apply for position: ' . $_REQUEST['job_title'] . '</h3>
					<form id="form-application" action="" method="post" class="form-validate" enctype="multipart/form-data" autocomplete="off">        		
						<input  type="hidden" class="required"  id="job_id" name="job_id"   value="' . $_REQUEST['jobid'] . '">
						<input type="hidden" class="required" value="' . $_REQUEST['job_title'] . '" id="job_title" name="job_title">
						<table border="0">
							<tr>
								<td>Email </td>
								<td><input type="email" name="email" id="email" autocomplete="off" value=""></td>
							</tr>
                                                        <tr>
								<td>Password </td>
								<td><input type="password" name="password" id="password" autocomplete="off" value=""></td>
							</tr>
							<tr>
								<td><input type="submit" name="login_submit" value="Login & Apply" onclick="return app_validate();">
							<p><a href="' . get_option("forget_page_url") . '">Forgot your password</a></p>
								</td>
							</tr>
						</table>
                                                
					 </form>
				</div>';

            return $job_settings_str;
            break;
        case 'apply':
            global $post;
            $pageid = $post->ID;
            global $wpdb;
            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $job_settings_str = '';
            global $wpdb;
            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $feed_url = $setting->http_url; //'http://jobs.chameleoni.com/xmlfeed.aspx';
            $AuthKey = $setting->authKey;
            $AuthPassword = $setting->authPassword;
            $APIKey = $setting->aPIKey;
            $UserName = $setting->userName;
            $Thank_you_page = get_option("thank_you_page");
            $feed_location = $setting->feed_location;
            $feed_type = $setting->feed_type;
            $feed_salary = $setting->feed_salary;
            $feed_summary = $setting->feed_summary;
            $page_size = $setting->number_of_jobsper_Page;


            //$job_settings_str = '';
            return $job_settings_str;
            break;
        default:

            global $wpdb;
            $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
            $feed_url = $setting->http_url; //'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx';
            $AuthKey = $setting->authKey;
            $AuthPassword = $setting->authPassword;
            $APIKey = $setting->aPIKey;
            $UserName = $setting->userName;
            $Thank_you_page = get_option("thank_you_page");
            $feed_location = $setting->feed_location;
            $feed_type = $setting->feed_type;
            $feed_salary = $setting->feed_salary;
            $feed_summary = $setting->feed_summary;
            $page_size = $setting->number_of_jobsper_Page;
            $summary_characters = $setting->summary_characters;
            $t1tag = 'Web Location';
            /* Get T2 tags from T1 tag */
            $request = '<ChameleonIAPI>
							<Method>TagListT2ForT1</Method>
							<APIKey>' . $APIKey . '</APIKey>
							<UserName>' . $UserName . '</UserName>
							<Filter>
								<Param Name="T1" Value="' . $t1tag . '" />
							</Filter>
						</ChameleonIAPI>';
            $encoded = 'Xml=' . $request . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
            $ch = curl_init($feed_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = str_replace('utf-16', 'utf-8', $result);
            $xml = simplexml_load_string($result);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
            if ($array['Status'] == 'Pass') {
                $job_taglist1 = @$array['TagListT2ForT1']['Tag'];
            }
            $t2tag = 'Web Expertise';
            /* Get T2 tags from T1 tag */
            $request = '<ChameleonIAPI>
							<Method>TagListT2ForT1</Method>
							<APIKey>' . $APIKey . '</APIKey>
							<UserName>' . $UserName . '</UserName>
							<Filter>
								<Param Name="T1" Value="' . $t2tag . '" />
							</Filter>
						</ChameleonIAPI>';
            $encoded = 'Xml=' . $request . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
            $ch = curl_init($feed_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = str_replace('utf-16', 'utf-8', $result);
            $xml = simplexml_load_string($result);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
            if ($array['Status'] == 'Pass') {
                $job_taglist2 = @$array['TagListT2ForT1']['Tag'];
            }
            $post = @$_REQUEST['post'];
            $PageNo = (@$_REQUEST['PageNo']) ? @$_REQUEST['PageNo'] : 1;
            $_SESSION['PageNo'] = $PageNo;
            $filter = (@$_REQUEST['filter']) ? @$_REQUEST['filter'] : array();
            //$_SESSION['filter'] = $filter;
            $permanent = true;
            $contract = true;
            $temporary = true;
            $self_employed = true;
            $job_type_arr = array("Permanent" => "Permanent", "Contract" => "Contract", "Temporary" => "Temporary", "Self_Employed" => "Self Employed");
            if (isset($_REQUEST["do"])) {
                if (isset($filter['permanent'])) {
                    $permanent = true;
                } else {
                    $permanent = false;
                    unset($job_type_arr["Permanent"]);
                }

                if (isset($filter['contract'])) {
                    $contract = true;
                } else {
                    $contract = false;
                    unset($job_type_arr["Contract"]);
                }
                if (isset($filter['temporary'])) {
                    $temporary = true;
                } else {
                    $temporary = false;
                    unset($job_type_arr["Temporary"]);
                }
                if (isset($filter['self_employed'])) {
                    $self_employed = true;
                } else {
                    $self_employed = false;
                    unset($job_type_arr["Self_Employed"]);
                }
            }
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $job_type_str = implode(",", $job_type_arr);
            $page_rows = $page_size;
            $t1tag = $t2tag = '';
            $t1tag = @$_REQUEST['t1tag'] ? @$_REQUEST['t1tag'] : '';
            $t2tag = @$_REQUEST['t2tag'] ? @$_REQUEST['t2tag'] : '';
            $taglistpass = $t1tag . ',' . $t2tag;

            $taglistpass = trim($taglistpass, ',');
            $request = '<ChameleonIAPI>
										<Method>SearchVacancies2</Method>
										<APIKey>' . $APIKey . '</APIKey>
										<UserName>' . $UserName . '</UserName>
                                                                                <OrderBy>JobTitle ASC</OrderBy>
										<Filter>
			   <Param Name="VacancyId" Operator="=" Value="" />

                           <Param Name="AgencyId" Operator="=" Value="" />

                           <Param Name="JobType" Operator="CONTAINS" Value="' . $job_type_str . '" />

                           <Param Name="DatePosted" Operator="=" Value="" />

                           <Param Name="DateClosed" Operator="=" Value="" />

                           <Param Name="StartDate" Operator="=" Value="" />

                           <Param Name="EndDate" Operator="=" Value="" />

                           <Param Name="TagSearch" Operator="=" Value="' . $t2tag . '" />

                           <Param Name="RatesPer" Operator="=" Value="" />

                           <Param Name="Currency" Operator="=" Value="" />

                           <Param Name="RateFrom" Operator="=" Value="" />

                           <Param Name="RateTo" Operator="BETWEEN" Value="" />

                           <Param Name="Benefits" Operator="=" Value="" />

                           <Param Name="LocationTag" Operator="=" Value="' . $t1tag . '" />

                           <Param Name="Location" Operator="=" Value="" />

                           <Param Name="Towns" Operator="=" Value="" />

                           <Param Name="Country" Operator="=" Value="" />

                           <Param Name="TownId" Operator="=" Value="" />

                           <Param Name="Description" Operator="=" Value="" />

                           <Param Name="Bonus" Operator="=" Value=""/>

                           <Param Name="ShowOnHomePage" Operator="=" Value="" />

                           <Param Name="HotJob" Operator="=" Value="" />

                           <Param Name="JobPostCode" Operator="=" Value="" />

                           <Param Name="ConsultantName" Operator="=" Value="" />

                           <Param Name="ConsultantTelNo" Operator="=" Value="" />

                           <Param Name="ConsultantEmail" Operator="=" Value="" />
                           <Param Name="ClientIP" Operator="=" Value="' . $ip . '" />
                           <Param Name="PageNo" Value="' . $PageNo . '" />
                           <Param Name="PageSize" Value="' . $page_size . '" />
										</Filter>
									</ChameleonIAPI>';
            $encoded = 'Xml=' . $request . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
            $ch = curl_init($feed_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = str_replace('utf-16', 'utf-8', $result);

            $xml = simplexml_load_string($result);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
            if (isset($array['Vacancies']) && count($array['Vacancies']) > 0) {
                if (isset($array['Vacancies']['Vacancy'][0])) {
                    $jobs_vac = @$array['Vacancies']['Vacancy'];
                    //@$total = count($array['Vacancies']['Vacancy']);
                    @$total = @$array['Vacancies']['Vacancy'][0]['TotalCount'];
                    
                } else {
                    $temp_arr = $array['Vacancies']['Vacancy'];
                    unset($array['Vacancies']['Vacancy']);
                    //foreach($temp_arr as $k => $v)
                    //{
                    $array['Vacancies']['Vacancy'][] = $temp_arr;
                    //}
                    $jobs_vac = $array['Vacancies']['Vacancy'];
                    @$total = @$array['Vacancies']['Vacancy'][0]['TotalCount'];
                }
            }
            $_SESSION['currentpage'] = $_SESSION['PageNo'];
            ?>
            <?php
            $job_listing = "";
            $job_listing .= '<div class="chameleoni_listing">
				<div class="search_form">
				<div id="vacancy_search_top"><h3>Vacancy Search</h3></div>
				<form name="frmsearch" action="" method="get">
					<input type="hidden" name="do" value="1"/>
                                                <table border="0" style="width:100%" class="VacancySearch_tbl">
						<tbody><tr>
							<td>
								<label for="location" class="searchlabel">Location</label>
								<select name="t1tag" id="t1tag">
								<option value="">Select</option>';
            foreach ($job_taglist1 as $value) {
                $job_listing .= '<option value="' . $value['Tag'] . '"';
                if ($value['Tag'] == @$t1tag) {
                    ?>
                    <?php $job_listing .= 'selected="selected"'; ?>
                <?php } ?>
                <?php $job_listing .= '>'; ?>
                <?php $job_listing .= $value['Tag'] ?>
                <?php $job_listing .= '</option>'; ?>

                <?php
            }
            ?>
            <?php
            $job_listing .= '</select>
							</td>
						   </tr> 
						   <tr>
							<td>
								<label for="tag" class="searchlabel">Sector</label>
								<select name="t2tag" id="t2tag">
								<option value="">Select</option>';
            foreach ($job_taglist2 as $value) {
                ?>
                <?php $job_listing .= '<option value="' . $value['TagId'] . '"'; ?> <?php
                if ($value['TagId'] == @$t2tag) {
                    $job_listing .= ' selected="selected"';
                }
                $job_listing .= '>' . $value['Tag'] . '</option>';
            }

            $job_listing .= ' </select>
							</td></tr>';

            $job_listing .= '<tr>
								<td style="padding-top: 6px;">';

            $job_listing .= '<span class="chk_filter"><input type="checkbox" value="1"  id="permanent" name="filter[permanent]"';
            if ($permanent) {
                $job_listing .= ' checked="checked" ';
            }
            $job_listing .= ' >';
            $job_listing .= '<label for="permanent">Permanent</label></span>
									<span class="chk_filter"><input type="checkbox" ';
            if ($contract) {
                $job_listing .= ' checked="checked" ';
            }
            $job_listing .= 'id="contract" value="1" name="filter[contract]" >
									<label for="Contract">Contract</label></span>
									<span class="chk_filter"><input type="checkbox"  value="1" id="temporary" name="filter[temporary]" ';
            if ($temporary) {
                $job_listing .= ' checked="checked" ';
            }
            $job_listing .= ' >';
            $job_listing .= ' <label for="Temporary">Temporary</label></span>';

            $job_listing .= '<span class="chk_filter"><input type="checkbox" value="1"  id="self_employed" name="filter[self_employed]" ';
            if ($self_employed) {
                $job_listing .= ' checked="checked" ';
            }
            $job_listing .= ' >';
            $job_listing .= '<label for="self_employed">Self Employed</label></span>';
            $job_listing .= '</td>
							</tr>
							<tr>
							<td><input type="submit" name="submit" value="Search" class="jobapply"></td>
						</tr>    
					</tbody></table>
				</form>
				</div>
				</div>';

            global $post;
            $pageid = $post->ID;
            $det = JOBINFO_URL . '/jobs_details.php';
            //echo '<a href"'.$det.'">View</a>';
            //print_r($jobs_vac);
            if (!empty($jobs_vac) and count($jobs_vac)) {
                $job_listing .= '<h2 class="title">Search Results</h2>';
                foreach ($jobs_vac as $job) {


                    $job_listing .= '<div class="job">
						<p><span class="type"><label><b>Job Title:</b></label><div class="title-wrapper">' . esc_html($job['JobTitle']) . '</div></span></p>';
                    if ($feed_location == '1') {
                        $job_listing .= '<p><span class="location"><label><b>Location:</b></label>';
                        $job_listing .= esc_html(is_array($job['LocationTag']) ? implode(', ', $job['LocationTag']) : $job['LocationTag']);
                        $job_listing .= '</span><br></p>';
                    }
                    if ($feed_type == '1') {
                        $job_listing .= '<p><span class="type"><label><b>Type:</b></label>' . esc_html(($job['JobType'])) . '</span></p>';
                    }
                    if ($feed_salary == '1') {
                        $job_listing .= '<p><span class="type"><label><b>Salary:</b></label>' . esc_html(($job['Pay'])) . '</span></p>';
                    }
                    $job_listing .= '<p><span class="type"><label><b>Date Posted:</b></label>';
                    $job_listing .= esc_html(date("d/m/Y", strtotime($job['DatePosted'])));
                    $job_listing .= '</span></p>';

                    $job_listing .= '<p><span class="type"><label><b>Close Date:</b></label>';
                    $job_listing .= esc_html(date("d/m/Y", strtotime($job['DateClosed'])));
                    $job_listing .= '</span></p>';
                    if ($feed_summary == '1' && $job['Description'] != '') {

                        $job_listing .= '<p><span class="summary"><label><b>Summary:</b></label>';
                        $job_listing .= '<div class="desc-wrapper">' . (($job['Description']) ? substr(nl2br($job['Description']), 0, $summary_characters) . ((strlen($job['Description']) > $summary_characters) ? '.....' : '..') : '..') . '</div>';
                        $job_listing .= '</span></p> ';
                    }
                    $job_listing .= ' <p><span class="consultantname"><label><b>Consultant Name:</b></label>';
                    $job_listing .= esc_html($job['ConsultantName']);
                    $job_listing .= ' </span></p>
					   <p> <span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto:' . esc_html($job['ConsultantEmail']) . '">' . $job['ConsultantEmail'] . '</a></span></p>';
                    unset($_REQUEST['submit']);
                    unset($_REQUEST['task']);
                    $url_params = http_build_query($_REQUEST);
                    $job_listing .= ' <span><a class="view_morebtn" href="?page_id=' . $pageid . '&task=jobdetails&jobid=' . esc_html($job['VacancyId']) . '&' . $url_params . '">View More</a> </span>
					  </div>';
                }  // foreach 
                //$job_listing .= '  <form name="frmpagination" id="frmpagination" method="post" action="">';
                //This is the number of results displayed per page 
                //This checks to see if there is a page number. If not, it will set it to page 1 
                if ($total > $page_size) {
                    $additional_params = '';
                    if (!empty($_GET)) {
                        $p = array();
                        foreach ($_GET as $k => $v) {
                            if ($k == "PageNo")
                                continue;
                            $p[$k] = $v;
                        }
                        $additional_params .= "&" . http_build_query($p);
                    }
                    if (!(isset($pagenum))) {
                        $pagenum = 1;
                    }
                    $page_rows = $page_size;
                    //This tells us the page number of our last page 
                    $last = ceil($total / $page_rows);
                    //this makes sure the page number isn't below one, or more than our maximum pages 
                    if ($pagenum < 1) {
                        $pagenum = 1;
                    } elseif ($pagenum > $last) {
                        $pagenum = $last;
                    }
                    $pagenum;

                    $job_listing .= '<ul class="pageul">';

                    if ($PageNo != 1) {

                        $job_listing .= "<li><b><a href='?PageNo=" . ($PageNo - 1) . $additional_params . "'>Previous << </a></b> </li>";
                        ?>
                                                                                                                                                                                                                        <!--<input    type="button" name="pervious" value="<<"  />-->
                        <?php
                    }
                    for ($t = 1; $t <= $last; $t++) {
                        $job_listing .= "	  <li><b><a href='?PageNo=" . ($t) . $additional_params . "'> $t </a></b> </li>";
                    }
                    ?>
                    <?php if ($last != $PageNo) { ?>
                                                                                                                                                                                                                        <!-- <input    onclick="nextprevious('n')" type="button" name="next" value=">>"  />-->
                        <?php
                        $job_listing .= "	 <li><b><a href='?PageNo=" . ($PageNo + 1) . $additional_params . "'>Next >></a></b> </li>";
                    }
                    $job_listing .= '	 </ul>
				<input type="hidden" name="PageNo" id="PageNo" value="1" />
				';
                }
            } else {
                $job_listing .= '<h3>No listing found</h3>';
            }
            $job_listing .= '<script type="text/javascript">function nextprevious(ch){if(ch == "p"){document.getElementById("PageNo").value = ';
            $job_listing .= $PageNo - 1;
            $job_listing .= '}else if(ch == "n"){document.getElementById("PageNo").value = ';
            $job_listing .= $PageNo + 1;
            $job_listing .= '} document.frmpagination.submit();} function mysubmit(PageNo){document.getElementById("PageNo").value = PageNo; document.frmpagination.submit();}</script>';

            $job_listing .= "<style>
				.pageul li{ 
				list-style:none;cursor:pointer;
					display:inline-block;
					margin:0 5px;
					border:solid 1px #ccc;
					border-radius: 3px;
					padding:0 3px;
					
				}
				.pageul{
					margin:5px 0;
				text-align:center;
				}
				</style>";


            return $job_listing;
            break;
    }
}

require_once plugin_dir_path(__FILE__) . '/jobsearch.php';
add_shortcode("Jobs_disp_search_widget", "jobs_search_widget");
add_shortcode("Jobs_disp_search_results", "jobs_do_chameloni_search");

function cjf_job_loginfunc() {
    include 'joblogin.php';
}

add_shortcode('Jobs_disp_login_form_front', 'cjf_job_loginfunc');

function cjf_job_logoutfunc() {
    include 'joblogout.php';
}

add_shortcode('Jobs_disp_logout_front', 'cjf_job_logoutfunc');

function cjf_job_forgetpassfunc() {
    include 'jobforgetpass.php';
}

add_shortcode('Jobs_disp_forgetpass_form_front', 'cjf_job_forgetpassfunc');

function cjf_job_profilefunc() {
    include 'profile.php';
}

add_shortcode('Jobs_disp_profile_form_front', 'cjf_job_profilefunc');

function cjf_stylesheetcss_scripts() {
    wp_register_style('prefix-style', JOBINFO_URL . '/css/job_style.css');
    wp_enqueue_style('prefix-style');
}

add_action('wp_enqueue_scripts', 'cjf_stylesheetcss_scripts');
add_action('admin_menu', 'cjf_jobadmin_menu');
register_activation_hook(__FILE__, 'cjf_jobs_install');
register_deactivation_hook(__FILE__, 'cjf_settings_install_del');
add_shortcode('Jobs_disp_front', 'cjf_front_view_job_func');

if (isset($_SESSION['ContactId'])) {
    $menu_login_style = ".menu-login{
        display: none;
    }";
} else {
    $menu_login_style = ".menu-login{
        display: block;
    }";
}
wp_add_inline_style('prefix-style', $menu_login_style);

function candidate_registeration() {
    require_once plugin_dir_path(__FILE__) . '/jobregister.php';
}

add_shortcode('Jobs_disp_register_form_front', 'candidate_registeration');
