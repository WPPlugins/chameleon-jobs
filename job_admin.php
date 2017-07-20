<?php
global $wpdb;
if(isset($_POST['save-settings'])){
 	$query = "UPDATE ".$wpdb->prefix . "jobs_settings SET 
    http_url = '".sanitize_text_field($_POST['http_url'])."',
	authKey = '".sanitize_text_field($_POST['authKey'])."',
	authPassword = '".sanitize_text_field($_POST['authPassword'])."',
	aPIKey = '".sanitize_text_field($_POST['aPIKey'])."',
	userName = '".sanitize_text_field($_POST['userName'])."',
	thank_you_page = '".sanitize_text_field($_POST['thank_you_page'])."',
	feed_location = '".sanitize_text_field($_POST['feed_location'])."',
	feed_type = '".sanitize_text_field($_POST['feed_type'])."', 
	feed_salary = '".sanitize_text_field($_POST['feed_salary'])."',
	 feed_summary = '".sanitize_text_field($_POST['feed_summary'])."',
	 summary_characters = '".sanitize_text_field($_POST['summary_characters'])."',
	 number_of_jobsper_Page = '".sanitize_text_field($_POST['number_of_jobsper_Page'])."'";
	$wpdb->query(@$wpdb->prepare($query));
        
        if( get_option("login_page_url")===false ) {
            add_option("login_page_url", sanitize_text_field($_POST['login_page_url']));
        } else {
            update_option("login_page_url", sanitize_text_field($_POST['login_page_url']));
        }
        
        if( get_option("register_page_url")===false ) {
            add_option("register_page_url", sanitize_text_field($_POST['register_page_url']));
        } else {
            update_option("register_page_url", sanitize_text_field($_POST['register_page_url']));
        }
        if( get_option("forget_page_url")===false ) {
            add_option("forget_page_url", sanitize_text_field($_POST['forget_page_url']));
        } else {
            update_option("forget_page_url", sanitize_text_field($_POST['forget_page_url']));
        }
        if( get_option("profile_page_urll")===false ) {
            add_option("profile_page_urll", $_POST['profile_page_urll']);
        } else {
            update_option("profile_page_urll", $_POST['profile_page_urll']);
        }
        if( get_option("jobs_page_url")===false ) {
            add_option("jobs_page_url", $_POST['jobs_page_url']);
        } else {
            update_option("jobs_page_url", $_POST['jobs_page_url']);
        }
        if( get_option("jobs_search_url")===false ) {
            add_option("jobs_search_url", $_POST['jobs_search_url']);
        } else {
            update_option("jobs_search_url", $_POST['jobs_search_url']);
        }
		if( get_option("thank_you_page")===false ) {
            add_option("thank_you_page", $_POST['thank_you_page']);
        } else {
            update_option("thank_you_page", $_POST['thank_you_page']);
        }
        
	echo "<p>Settings Saved!</p>";
}
$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix . "jobs_settings"));
?>
<h2>Chameleoni.com Job Feed Settings</h2>
<form name="frmsettings" action="" method="post">
<table class="table" border="0">
	<tr>
    	<td>HTTPS URL of job feed: </td>
        <td><input type="text" name="http_url" value="<?php echo ($setting->http_url) ? $setting->http_url : 'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx'?>" /></td>
    </tr>
    <tr>
    	<td>AuthKey: </td>
        <td><input type="text" name="authKey" value="<?php echo ($setting->authKey) ? $setting->authKey : 'Guest'?>" /></td>
    </tr>
    <tr>
    	<td>AuthPassword: </td>
        <td><input type="text" name="authPassword" value="<?php echo ($setting->authPassword) ? $setting->authPassword : 'KgwLLm7TL6G6'?>" /></td>
    </tr>
    <tr>
    	<td>APIKey: </td>
        <td><input type="text" name="aPIKey" value="<?php echo ($setting->aPIKey) ? $setting->aPIKey : 'D12E9CF3-F742-47FC-97CB-295F4488C2FA'?>" /></td>
    </tr>
    <tr>
    	<td>UserName: </td>
        <td><input type="text" name="userName" value="<?php echo ($setting->userName) ? $setting->userName : 'David' ?>" /></td>
    </tr>
    <tr>
    	<td>Jobs page url : </td>
        <td><input type="text" name="jobs_page_url" value="<?php echo (get_option("jobs_page_url")!=false) ? get_option("jobs_page_url") : '' ?>" /></td>
    </tr>
    <tr>
    	<td>Jobs search results url : </td>
        <td><input type="text" name="jobs_search_url" value="<?php echo (get_option("jobs_search_url")!=false) ? get_option("jobs_search_url") : '' ?>" /></td>
    </tr>
    <tr>
    	<td>Login page url : </td>
        <td><input type="text" name="login_page_url" value="<?php echo (get_option("login_page_url")!=false) ? get_option("login_page_url") : '' ?>" /></td>
    </tr> 
    <tr>
    	<td>Register page url : </td>
        <td><input type="text" name="register_page_url" value="<?php echo (get_option("register_page_url")!=false) ? get_option("register_page_url") : '' ?>" /></td>
    </tr> 
    <tr>
    	<td>Forget password page url : </td>
        <td><input type="text" name="forget_page_url" value="<?php echo (get_option("forget_page_url")!=false) ? get_option("forget_page_url") : '' ?>" /></td>
    </tr>
    <tr>
    	<td>Profile page url : </td>
        <td><input type="text" name="profile_page_urll" value="<?php echo (get_option("profile_page_urll")!=false) ? get_option("profile_page_urll") : '' ?>" /></td>
    </tr>
     <tr>
    	<td>Thank You Page ID : </td>
        <td><input type="text" name="thank_you_page" value="<?php echo (get_option("thank_you_page")!=false) ? get_option("thank_you_page") : '' ?>" /></td>
    </tr>    
    <tr>
    	<td colspan="2"><h3>Frontend Field Parameters</h3></td>
    </tr>
    <tr>
    	<td>Show Location:</td>
        <td>
        	<select name="feed_location">
        		<option value="0" <?php if($setting->feed_location == '0'){?> selected="selected" <?php } ?>>Hide</option>
                <option value="1" <?php if($setting->feed_location == '1'){?> selected="selected" <?php } ?>>Show</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td>Show Type:</td>
        <td>
        	<select name="feed_type">
        		<option value="0" <?php if($setting->feed_type == '0'){?> selected="selected" <?php } ?>>Hide</option>
                <option value="1" <?php if($setting->feed_type == '1'){?> selected="selected" <?php } ?>>Show</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td>Show Salary:</td>
        <td>
        	<select name="feed_salary">
        		<option value="0" <?php if($setting->feed_salary == '0'){?> selected="selected" <?php } ?>>Hide</option>
                <option value="1" <?php if($setting->feed_salary == '1'){?> selected="selected" <?php } ?>>Show</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td>Show Summary:</td>
        <td>
        	<select name="feed_summary">
        		<option value="0" <?php if($setting->feed_summary == '0'){?> selected="selected" <?php } ?>>Hide</option>
                <option value="1" <?php if($setting->feed_summary == '1'){?> selected="selected" <?php } ?>>Show</option>
            </select>
        </td>
    </tr>
    <tr>
    	<td>Summary Characters</td>
        <td><input type="text" name="summary_characters" value="<?php echo ($setting->summary_characters) ? $setting->summary_characters : 200 ?>" /></td>
    </tr> 
    <tr>
    	<td>Number Of Jobs Per Page</td>
        <td><input type="text" name="number_of_jobsper_Page" value="<?php echo ($setting->number_of_jobsper_Page) ? $setting->number_of_jobsper_Page : 5 ?>" /></td>
    </tr>   
    <tr>
    	<td colspan="2"><input type="submit" value="Save Settings" name="save-settings" /></td>
    </tr>
</table>
</form>
</br>
<!--<p>To show the jobs on the frontend, create a page and place the shortcode [Jobs_disp_front] in it.</p>-->
<p>To show the search widget on any section of your website, use the shortcode [Jobs_disp_search_widget].</p>
<p>To show the job search results on the frontend, create a page and place the shortcode [Jobs_disp_search_results] in it.</p>
<p>To show the search widget on any page along with the search results on your website, use the shortcode [Jobs_disp_front].</p>
<p>To show the registration form place this shortcode on any page [Jobs_disp_register_form_front] </p>
<p>To show the login form place this shortcode on any page [Jobs_disp_login_form_front] </p>
<p>To show the forget password form place this shortcode on any page [Jobs_disp_forgetpass_form_front] </p>
<p>To show the profile form place this shortcode on any page [Jobs_disp_profile_form_front] </p>
<p>To show the logout button place this shortcode on any page [Jobs_disp_logout_front] </p>
</br>
The CSS styling can be customised in /wp-content/plugins/chameleon-wp/css/job_style.css