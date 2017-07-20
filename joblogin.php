<?php
add_action('wp_loaded','boot_session');
if (!isset($_SESSION)) @session_start();
 if(!empty($_SESSION['ContactId']))
{
    $task = @$_REQUEST['action'];
if(isset($task) && $task == "logout")
{
    unset($_SESSION['ContactId']);
    session_unset();
    session_destroy();
    echo '<div class="alert alert-success">You logged out!</div><script> setTimeout(function(){ location.assign("'.get_site_url().'"); }, 3000); </script>';
}else{
    if(!empty($_SERVER['QUERY_STRING']))
    {
        $logout = '<a href="?'. $_SERVER['QUERY_STRING'] . '&' .'action=logout">Logout</a>';
    }else{
        $logout = '<a href="?action=logout">click here</a>';
    }
    echo '<div class="alert alert-success">You already logged in. To log out '. $logout .'</div>';
 }
}else{
if (isset($_POST['login_submit']) == 'Login') {
    global $wpdb;
    $setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "jobs_settings"));
    $feed_url = $setting->http_url; //'http://jobs.chameleoni.com/xmlfeed.aspx';
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

    if (!session_id())
      session_start();

    $result = str_replace('utf-16', 'utf-8', $result);
    $xml = simplexml_load_string($result);
    $json = json_encode($xml);
    $array_contactid = json_decode($json, TRUE);
    if (isset($array_contactid['ContactId']) && !empty($array_contactid['ContactId'])) {
        $_SESSION['ContactId'] = $array_contactid['ContactId'];
        //setcookie("ContactId", $array_contactid['ContactId']);
        echo "<div class='alert alert-success'>You logged in successfully!Redirecting to profile page...</div><script> setTimeout(function(){ location.assign('".get_option("profile_page_urll")."'); }, 3000); </script>";
        echo "<style> #form-application{ display: none; } </style>";
    } else {
        echo "<div class='alert alert-danger'>Error no such user!</div>";
    }
    //wp_redirect(get_option("profile_page_url"));
    
}
?>
<script type="text/javascript">
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
        return true;
    }
</script>
<form name="login_form" id="form-application" action="" method="post" autocomplete='off'>
<p align="center"><a href="http://www.chameleoni.com/wordpress-demo/wp-content/uploads/2015/03/wordpress-logo.gif" rel="attachment wp-att-1059"><img class="alignnone size-medium wp-image-1059" src="http://www.chameleoni.com/wordpress-demo/wp-content/uploads/2015/03/wordpress-logo-300x186.gif" alt="wordpress-logo" width="300" height="186" /></a></p>
<table border="0">
                            <tr>
                                <td>Email </td>
                                <td><input autocomplete='off' type="email" name="email" id="email" value=""></td>
                            </tr>
                                                        <tr>
                                <td>Password </td>
                                <td><input autocomplete='off' type="password" name="password" id="password" value=""></td>
                            </tr>
                            <tr>
                                <td><input type="submit" name="login_submit" value="Login" onclick="return app_validate();">
                            
                                </td>
                            </tr>
                        </table>

<p><a href="<?= get_option("forget_page_url") ?>">Forgot your password</a></p>
</form>
<?php } ?>