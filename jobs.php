<?php
	switch(@$_REQUEST['task']){
		case 'jobdetails':
			include_once('job_details.php');
		break;			
		case 'apply':
			include_once('job_apply.php');
			
		break;
		default:
			include_once('jobs_list.php');
		break;
	}
?>