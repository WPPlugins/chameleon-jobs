<?php
if (!isset($_SESSION['ContactId'])) {
    echo '<div class="alert alert-danger">Please <a href="' . get_option('login_page_url') . '">login</a> to view the profile page!</div>';
} else {
    echo submit_job_profile();
}

function submit_job_profile() {
    global $post;
    $pageid = $post->ID;
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

    
    $contact_id = $_SESSION['ContactId'];
    $request_user_data = '<?xml version="1.0" encoding="utf-16" ?>
<ChameleonIAPI>
    <Method>ReadLogin</Method>
         <APIKey>' . $APIKey . '</APIKey>
        <UserName>' . $UserName . '</UserName>
       <InputData>
      <Input Name="ContactId" Value="' . $contact_id . '"  />
     </InputData>
</ChameleonIAPI>';
    

    $encoded = 'Xml=' . $request_user_data . '&Action=postxml&AuthKey=' . $AuthKey . '&AuthPassword=' . $AuthPassword;
    $ch = curl_init($feed_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    $result_data = curl_exec($ch);
    curl_close($ch);
    $result_data = str_replace('utf-16', 'utf-8', $result_data);
    $xml = simplexml_load_string($result_data);
    $json = json_encode($xml);
    $res_array = json_decode($json, TRUE);

    if (isset($res_array['Contacts']['Contact']) && !empty($res_array['Contacts']['Contact'])) {
        $data = $res_array['Contacts']['Contact'];
        $Christian = $data['Christian'];
        $Surname = $data['Surname'];
        $EMail = $data['EMail'];
        $WorkTelNo = is_array($data['WorkTelNo']) ? "" : $data['WorkTelNo'];
        $MobileTelNo = is_array($data['MobileTelNo']) ? "" : $data['MobileTelNo'];
        $WebPassword = $data['WebPassword'];
        $TelNo = is_array($data['TelNo']) ? "" : $data['TelNo'];
    } else {
        $Christian = "";
        $Surname = "";
        $EMail = "";
        $WorkTelNo = "";
        $MobileTelNo = "";
        $WebPassword = "";
        $TelNo = "";
    }

    $job_settings_str = '';

    // end verification 

    if (isset($_POST['apply-submit']) == 'Submit') {


        $request = '<ChameleonIAPI>
				<Method>update_profile</Method>
				<APIKey>' . $APIKey . '</APIKey>
				<UserName>' . $UserName . '</UserName>
				<InputData>
						<Input Name="CandidateId" Value="' . $contact_id . '" />
						<Input Name="Christian" Value="' . sanitize_text_field($_POST['Christian']) . '" />
						<Input Name="Surname" Value="' . sanitize_text_field($_POST['Surname']) . '" />
						<Input Name="Email" Value="' . sanitize_email($_POST['Email']) . '" />
						<Input Name="Password" Value="' . sanitize_text_field($_POST['Password']) . '" />
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
        $res_update = json_decode($json, TRUE);
        curl_close($ch);

        $ContactId = $_SESSION['ContactId'];

        if (isset($_FILES['cv']['name']) && strlen($_FILES['cv']['name'])) {
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
        echo "<div class='alert alert-success'>Data updated successfully!Redirecting...<script> setTimeout(function(){ location.assign('".get_option("profile_page_urll")."'); }, 3000); </script>
<style> #form-application{ display: none; } </style>
</div>";
    }
    //$job_settings_str = '';
    $job_settings_str .= '<script type="application/javascript">
				function app_validate()
				{
					validation_string = new Array();
					if(document.getElementById("Christian").value == "")
					{
						document.getElementById("Christian").focus();
						validation_string.push("Christian");
					}
					if(document.getElementById("Surname").value == "")
					{
						document.getElementById("Surname").focus();
						validation_string.push("Surname");
					}
					if(document.getElementById("Email").value == "")
					{
						validation_string.push("Email");
						document.getElementById("Email").focus();
					}else if (document.getElementById("Email").value != "")
                                        {
                                                var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
                                                if (!document.getElementById("Email").value.match(emailExp)) {
                document.getElementById("Email").focus();
                validation_string.push("Valid Email Address");
            } 
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
								<td>Forename <span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Christian" id="Christian"  value="' . $Christian . '" /></td>
							</tr>
							<tr>
								<td>Surname <span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Surname" id="Surname"  value="' . $Surname . '" /></td>
							</tr>
							<tr>
								<td>Email<span class="star">&nbsp;*</span> </td>
								<td><input type="text" name="Email" id="Email" value="' . $EMail . '" /></td>
							</tr>
							<tr>
								<td>Password </td>
								<td><input type="password" name="Password" id="Password"  value="' . $WebPassword . '" /></td>
							</tr> <tr>
								<td>Home Tel </td>
								<td><input type="text" name="HomeTelNo" id="HomeTelNo"  value="' . $TelNo . '" /></td>
							</tr> <tr>
								<td>Mobile Tel<span class="star">&nbsp;*</span></td>
								<td><input type="text" name="MobileTelNo" id="MobileTelNo"  value="' . $MobileTelNo . '" /></td>
							</tr> <tr>
								<td>Work Tel  </td>
								<td><input type="text" name="WorkTelNo" id="WorkTelNo"  value="' . $WorkTelNo . '" /></td>
							</tr>
							<tr>
								<td>Attach CV <span class="star">&nbsp;*</span></td>
								<td><input type="file" name="cv"  id="cv"  value="" /></td>
							</tr>
							<tr>
								<td><input type="submit" value="Update" name="apply-submit"  class="jobapply" onclick="return app_validate();"/>
								</td>
							</tr>
						</table>
					 </form>
				</div>';
    return $job_settings_str;
}