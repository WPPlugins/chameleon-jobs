<?php

error_reporting(E_ALL ^ E_NOTICE);
add_action('wp_loaded', 'boot_session');
if (isset($_SESSION['ContactId']) && !empty($_SESSION['ContactId'])) {
    include_once("profile.php");
} else {
    echo submit_job_register();
}

function submit_job_register() {
    global $post;
    $pageid = $post->ID;
    global $wpdb;
    $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));

    $job_settings_str = '';
    if (isset($_POST['apply-submit']) == "Submit") {
        global $wpdb;
        $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
        $feed_url = $setting->http_url;
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
            if (isset($_FILES['cv']) and strlen($_FILES['cv'])) {
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

//            if ($setting->thank_you_page == 0) {
//
//                $redpage = "index.php";
//            } else {
//                $redpage = "?page_id=$setting->thank_you_page";
//            }

            echo "<div class='alert alert-success'>Thank you, we have received your details successfully. You may now apply for the vacancies advertised on this site.
<style> #form-application{ display:none !important; } </style>
</div>";
        } // if email not exit;
        else {
            //if (!session_id())
            //session_start();
            $_SESSION['Forename'] = sanitize_text_field($_POST['Forename']);
            $_SESSION['Surname'] = sanitize_text_field($_POST['Surname']);
            $_SESSION['WebPassword'] = sanitize_text_field($_POST['WebPassword']);
            $_SESSION['HomeTelNo'] = sanitize_text_field($_POST['HomeTelNo']);
            $_SESSION['MobileTelNo'] = sanitize_text_field($_POST['MobileTelNo']);
            $_SESSION['WorkTelNo'] = sanitize_text_field($_POST['WorkTelNo']);
            $_SESSION['cv'] = sanitize_file_name($_FILES['cv']['name']);
            $_SESSION['Email'] = sanitize_email($_POST['Email']);


            $job_settings_str = '<div class="alert alert-danger">Your email address already exists.</div>';
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
						validation_string.push("Valid Email Address");
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
				</script> 
				</script>';
    $job_settings_str .= '<div class="application-edit front-end-edit">
					<form id="form-application" action="" method="post" class="form-validate" enctype="multipart/form-data" > 
						<table border="0">
							<tr>							
								<td><span class="label">Forename</span> <span class="star">&nbsp;*</span> </td>
								<td><input type="hidden" name="TitleId" value="1"><input type="text" name="Forename" id="Forename"  value="" /></td>
							</tr>
							<tr>
								<td><span class="label">Surname</span> <span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Surname" id="Surname"  value="" /></td>
							</tr>
							<tr>
								<td><span class="label">Email</span><span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Email" id="Email" value="" /></td>
							</tr>
							<tr>
								<td><span class="label">Password</span><span class="star">&nbsp;*</span></td>
								<td><input type="password" name="WebPassword" id="WebPassword"  value="" /></td>
							</tr> <tr>
								<td><span class="label">Home Tel</span> </td>
								<td><input type="text" name="HomeTelNo" id="HomeTelNo"  value="" /></td>
							</tr> <tr>
								<td><span class="label">Mobile Tel</span> <span class="star">&nbsp;*</span></td>
								<td><input type="text" name="MobileTelNo" id="MobileTelNo"  value="" /></td>
							</tr> <tr>
								<td><span class="label">Work Tel</span>  </td>
								<td><input type="text" name="WorkTelNo" id="WorkTelNo"  value="" /></td>
							</tr>
							<tr>
								<td><span class="label">Attach CV</span>  </td>
								<td><input type="file" name="cv"  id="cv"  value="" /></td>
							</tr>
							<tr>
								<td><input type="submit" value="Submit" name="apply-submit"  class="jobapply" onclick="return app_validate();"/>
								
								</td>
							</tr>
						</table>
					 </form>
				</div>';
    return $job_settings_str;
}
