<?php
session_start();
$task = @$_REQUEST['action'];
if($task == "logout")
{
    unset($_SESSION['ContactId']);
    wp_redirect(get_site_url());
}
if(isset($_SESSION['ContactId'])):
    if(!empty($_SERVER['QUERY_STRING']))
    {
        $q = "&";
        ?>
<a href="?<?= $_SERVER['QUERY_STRING'] . $q ?>action=logout">Logout</a>
<?php
    }else{
        $q = "?";
        ?>
<a href="<?= $q ?>action=logout">Logout</a>
<?php
    }
?>

<?php endif; ?>