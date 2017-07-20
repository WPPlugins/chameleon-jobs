<?php
global $post;
$pageid = $post->ID;

global $wpdb;
$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));

//require_once('/wp-includes/registration.php');

if(!function_exists('register_new_user')) {
function register_new_user( $user_login, $user_email ) {
	$errors = new WP_Error();

	$sanitized_user_login = sanitize_user( $user_login );
	$user_email = apply_filters( 'user_registration_email', $user_email );

	// Check the username
	if ( $sanitized_user_login == '' ) {
		$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.' ) );
	} elseif ( ! validate_username( $user_login ) ) {
		$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
		$sanitized_user_login = '';
	} elseif ( username_exists( $sanitized_user_login ) ) {
		$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.' ) );
	}

	// Check the e-mail address
	if ( $user_email == '' ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.' ) );
	} elseif ( ! is_email( $user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address is not correct.' ) );
		$user_email = '';
	} elseif ( email_exists( $user_email ) ) {
		$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
	}

	do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

	$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

	if ( $errors->get_error_code() )
		return $errors;

	$user_pass = wp_generate_password();
	$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
	if ( ! $user_id ) {
		$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
		return $errors;
	}

	update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

	wp_new_user_notification( $user_id, $user_pass );

	return $user_id;
}
/* function validate_username( $username ) {
	$sanitized = sanitize_user( $username, true );
	$valid = ( $sanitized == $username );
	return apply_filters( 'validate_username', $valid, $username );
}
function username_exists( $username ) {
	if ( $user = get_userdatabylogin( $username ) ) {
		return $user->ID;
	} else {
		return null;
	}
}
function email_exists( $email ) {
	if ( $user = get_user_by_email($email) )
		return $user->ID;

	return false;
}
function wp_create_user($username, $password, $email = '') {
	$user_login = esc_sql( $username );
	$user_email = esc_sql( $email    );
	$user_pass = $password;

	$userdata = compact('user_login', 'user_email', 'user_pass');
	return wp_insert_user($userdata);
} */

} // if exit
?>
<?php
if(isset($_POST['apply-submit']) == "Submit")
{
    global $wpdb;
	$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));
	$feed_url = $setting->http_url;//'http://jobs.chameleoni.com/xmlfeed.aspx';
	$AuthKey = $setting->authKey;
	$AuthPassword = $setting->authPassword;
	$APIKey = $setting->aPIKey;
	$UserName = $setting->userName;
	$Thank_you_page = $setting->thank_you_page;
	$feed_location = $setting->feed_location;
	$feed_type = $setting->feed_type;
	$feed_salary = $setting->feed_salary;
	$feed_summary = $setting->feed_summary;
	$page_size = $setting->number_of_jobsper_Page; 


  $request_email = '<ChameleonIAPI>
    <Method>CheckEmail</Method>
    <APIKey>'.$APIKey.'</APIKey>
    <UserName>'.$UserName.'</UserName>
     <InputData>
     <Input Name="Email" Value="'.sanitize_email($_POST['Email']).'" />
     </InputData>
    </ChameleonIAPI>';
     
     $encoded = 'Xml='.$request_email.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
     $ch = curl_init($feed_url);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_VERBOSE, 0);
     $result_email = curl_exec($ch);
     $result_email = str_replace('utf-16','utf-8',$result_email);
     $xml = simplexml_load_string($result_email);
     $json = json_encode($xml);
     $array_email = json_decode($json,TRUE);

     // end verification 
     
    if($array_email['ContactCount'] == 0)
    {
    	

      $request = '<ChameleonIAPI>
    <Method>CandidateRegister</Method>
    <APIKey>'.$APIKey.'</APIKey>
    <UserName>'.$UserName.'</UserName>
    <InputData>
            <Input Name="TitleId" Value="1" />
            <Input Name="Forename" Value="'.sanitize_text_field($_POST['Forename']).'" />
            <Input Name="Surname" Value="'.sanitize_text_field($_POST['Surname']).'" />
            <Input Name="Email" Value="'.sanitize_email($_POST['Email']).'" />
            <Input Name="WebPassword" Value="'.sanitize_text_field($_POST['WebPassword']).'" />
            <Input Name="HomeTelNo" Value="'.sanitize_text_field($_POST['HomeTelNo']).'" />
            <Input Name="MobileTelNo" Value="'.sanitize_text_field($_POST['MobileTelNo']).'" />
            <Input Name="WorkTelNo" Value="'.sanitize_text_field($_POST['WorkTelNo']).'" />
    </InputData>
</ChameleonIAPI>';


    $encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
     $ch = curl_init($feed_url);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_VERBOSE, 0);
     $result = curl_exec($ch);
     $result = str_replace('utf-16','utf-8',$result);
     $xml = simplexml_load_string($result);
     $json = json_encode($xml);
     $array_contactid = json_decode($json,TRUE);
     curl_close($ch);
     session_start();
     

    $ContactId = $array_contactid['ContactId']; 
    echo '</br>';
    $ftype = end(explode('.',@$_POST['cv']));
    $fcontent = file_get_contents($_FILES['cv']['tmp_name']); 
    $fcontent =  base64_encode ($fcontent);
   
          
      
     

     $res_conid = '<ChameleonIAPI><Method>CandidateRegister</Method>
      <APIKey>'.$APIKey.'</APIKey>
    <UserName>'.$UserName.'</UserName>
    <InputData><Input Name="ContactId" Value="'.$ContactId .'" />
    <Input Name="CVDocType" Value="'.$ftype	.'" />
     <Input Name="CVBase64" Value="'.$fcontent.'"/>
    </InputData>
    </ChameleonIAPI>';

     $encoded = 'Xml='.$res_conid.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
     $ch = curl_init($feed_url);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
     curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
     curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_VERBOSE, 0);
     $result = curl_exec($ch);


     curl_close($ch); 


    	$_SESSION['Forename'] = '';
		$_SESSION['Surname'] = '';
		$_SESSION['WebPassword'] = '';
		$_SESSION['HomeTelNo'] = '';
		$_SESSION['MobileTelNo'] = '';
		$_SESSION['WorkTelNo'] = '';
		$_SESSION['cv'] = '';
		$_SESSION['Email'] ='';

		 if($setting->thank_you_page == 0)
		 {
          
          $redpage = "index.php";
		 }
		 else
		 {
		 	 $redpage = "?page_id=$setting->thank_you_page";
		 }



     ?>
      <script type="text/javascript">alert('Thank you for applying for this job.');  window.location = "<?php echo $redpage; ?>"; </script>	
<?php 

 } // if email not exit;
 else
 {

 
 	if ( !session_id() )
			session_start();

      
		$_SESSION['Forename'] = sanitize_text_field($_POST['Forename']);
		$_SESSION['Surname'] = sanitize_text_field($_POST['Surname']);
		$_SESSION['WebPassword'] = sanitize_text_field($_POST['WebPassword']);
		$_SESSION['HomeTelNo'] = sanitize_text_field($_POST['HomeTelNo']);
		$_SESSION['MobileTelNo'] = sanitize_text_field($_POST['MobileTelNo']);
		$_SESSION['WorkTelNo'] = sanitize_text_field($_POST['WorkTelNo']);
		$_SESSION['cv'] = sanitize_file_name($_FILES['cv']['name']); 
		$_SESSION['Email'] =  sanitize_email($_POST['Email']);


 	echo 'Email Address Already Exits';

 }








	
	/*global $wpdb;
	$table_user = $wpdb->prefix . 'users';
	$sel_user = "select id from $table_user where user_email = '".$_POST['email']."'";
	$res_user = $wpdb->get_results($sel_user);
	if($res_user[0]->id == '')
	{
		function random_password( $length = 8 ) {
    			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    			$password = substr( str_shuffle( $chars ), 0, $length );
    			return $password;
		}
		$user_password = random_password(12).'<br><br>';
		$username = $_POST['name1'].rand();
		$dt = date('Y-m-d');
		$userdata = array(
		'user_login'  =>  $_POST['email'],
		   'user_pass'   => $user_password ,
		   'user_email' =>  $_POST['email']);
    	//$ins_userid = wp_insert_user( $userdata ) ;
       $ins_userid =  register_new_user($_POST['email'],$_POST['email']);
		//On success
		if( !is_wp_error($ins_userid) ) {
		 //echo "User created : ". $ins_userid;
		} 
	 //$user_id = wp_insert_user( $userdata ) ;
	/* $ins_user = "Insert into $table_user(user_login,user_pass,user_nicename,user_email,user_registered,display_name) values('".$username."',
			'".md5($user_password)."',
			'".$_POST['name1']."',
			'".$_POST['email']."',
			'".$dt."','".$_POST['name1']."')";
			$wpdb->query($ins_user);
			$ins_userid = $wpdb->insert_id;   */
   /* $from = 'testing.perceptinfotech.com'; // sender
    $subject = 'Register User Info';
    $message = 'You have registered successfully  Please send below info of user login<br>';
	$message .= 'Username = '.$username.'<br>';
	$message .= 'Password = '.$user_password.'<br>';
    // mail("gova@perceptinfotech.com",$subject,$message,"From: $from\n");
   	}
	else
	{
		$ins_userid	= $res_user[0]->id;
	}
	$rn = rand(5, 15);
	$file = $_REQUEST['jobid'].'_'.$rn.'_'.$_FILES["cv"]["name"];
    if (file_exists("wp-content/plugins/myjobs_feed/files/" . $file))
    {
      echo $_FILES["cv"]["name"] . " already exists. ";
    }
    else
    {
      move_uploaded_file($_FILES["cv"]["tmp_name"],
      "wp-content/plugins/myjobs_feed/files/" .$file);
    }
    $dt = date('Y-m-d');	
	$table_name = $wpdb->prefix . 'jobs';
	$ins_job = "Insert Into ".$table_name."(user_id,job_id,job_title,name,email,cv,date,state,checked_out,created_by) values('".$ins_userid."','".$_REQUEST['jobid']."','".$_REQUEST['job_title']."','".$_POST['name1']."','".$_POST['email']."','".$file."','".$dt."',0,0,'".$ins_userid."')";
$wpdb->query($ins_job); 
	if($setting->thank_you_page == 0)
	{
		echo "Thank you for applying for this job";
	}
	else
	{ ?>
	  
	  <script type="text/javascript">window.location = "?page_id=<?php echo $setting->thank_you_page; ?>"; </script>	
<?php 	} */
}
?>
<script type="application/javascript">
function app_validate()
{
	validation_string = new Array();
	if(document.getElementById('Forename').value == "")
	{
		document.getElementById('Forename').focus();
		validation_string.push('Forename');
	}
	if(document.getElementById('Surname').value == "")
	{
		document.getElementById('Surname').focus();
		validation_string.push('Surname');
	}
	if(document.getElementById('Email').value == "")
	{
		validation_string.push('Email');
		document.getElementById('Email').focus();
	}

  	if(document.getElementById('cv').value == "")
	{
		validation_string.push('CV');
		document.getElementById('cv').focus();
	}
	if(validation_string != ''){
		alert('Please enter '+validation_string.toString());
		return false;
	}
	if(document.getElementById('Email').value != "")
	{
			var emailExp = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
			if(document.getElementById('Email').value.match(emailExp)){
			}else{
				alert('Please enter Valid Email Address');
				document.getElementById('Email').focus();
				return false;
			}
	}
	return true;
}
</script> 
</script>
<div class="application-edit front-end-edit">
    <h3>Apply for position: <?php echo $_REQUEST['job_title']; ?></h3>
    <form id="form-application" action="" method="post" class="form-validate" enctype="multipart/form-data" >        		
        <input  type="hidden" class="required"  id="job_id" name="job_id"   value="<?php echo $_REQUEST['jobid']; ?>">
		<input type="hidden" class="required" value="<?php echo $_REQUEST['job_title']; ?>" id="job_title" name="job_title">
        <table border="0">
        	<!-- <tr>
            	<td>Name <span class="star">&nbsp;*</span> </td>
                <td><input type="text" name="name1" id="name1"  value="" /></td>
            </tr> --> 
            <!-- <tr>
            	<td>TitleId 	</td>
            	<td>
            	<select name="TitleId"><option value="1">1</option></select>
            	</td>

            </tr> --> 
            <tr>
            
            	<td>Forename <span class="star">&nbsp;*</span> </td>
                <td><input type="hidden" name="TitleId" value="1"><input type="text" name="Forename" id="Forename"  value="<?php echo @$_SESSION['Forename'];  ?>" /></td>
            </tr>
            <tr>
            	<td>Surname <span class="star">&nbsp;*</span> </td>
                <td><input type="text" name="Surname" id="Surname"  value="<?php echo @$_SESSION['Surname'];  ?>" /></td>
            </tr>
            <tr>
            	<td>Email<span class="star">&nbsp;*</span> </td>
                <td><input type="text" name="Email" id="Email" value="<?php echo @$_SESSION['Email'];  ?>" /></td>
            </tr>
            <tr>
            	<td>Password </td>
                <td><input type="password" name="WebPassword" id="WebPassword"  value="<?php echo @$_SESSION['WebPassword'];  ?>" /></td>
            </tr> <tr>
            	<td>Home Tel </td>
                <td><input type="text" name="HomeTelNo" id="HomeTelNo"  value="<?php echo @$_SESSION['HomeTelNo'];  ?>" /></td>
            </tr> <tr>
            	<td>Mobile Tel </td>
                <td><input type="text" name="MobileTelNo" id="MobileTelNo"  value="<?php echo @$_SESSION['MobileTelNo'];  ?>" /></td>
            </tr> <tr>
            	<td>Work Tel  </td>
                <td><input type="text" name="WorkTelNo" id="WorkTelNo"  value="<?php echo @$_SESSION['WorkTelNo'];  ?>" /></td>
            </tr>
            <tr>
            	<td>Attach CV <span class="star">&nbsp;*</span></td>
                <td><input type="file" name="cv"  id="cv"  value="<?php echo @$_SESSION['cv'];  ?>" /></td>
            </tr>
            <tr>
            	<td><input type="submit" value="Submit" name="apply-submit"  class="jobapply" onclick="return app_validate();"/>
                <button class="jobapply" onclick="window.history.back()">Back</button>
            	</td>
            </tr>
		</table>
     </form>
</div>