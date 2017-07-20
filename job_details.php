<?php
global $post;
$pageid = $post->ID;
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
	$jobId = $_REQUEST['jobid'];
	
		$request = '
		<ChameleonIAPI>
			<Method>SearchVacancies</Method>
			<APIKey>'.$APIKey.'</APIKey>
			<UserName>'.$UserName.'</UserName>
			<Filter>
				<!-- Options Placeholder -->
				<Param Name="VacancyId" Value="'.$jobId.'" />
			</Filter>	
		</ChameleonIAPI>';
		$encoded = 'Xml='.$request.'&Action=postxml&AuthKey='.$AuthKey.'&AuthPassword='.$AuthPassword;
		$ch = curl_init($feed_url);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = str_replace('utf-16','utf-8',$result);
		$xml = simplexml_load_string($result);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		if($array['Status'] == 'Pass'){
			$job_details = @$array['Vacancies']['Vacancy'];	
			//$session->set('recent_job',$this->job);
		}
?>
<div id="vacancy_details">
    <p><span class="location"><label><b>Job Title:</b></label><?php echo esc_html($job_details['JobTitle']); ?></span></p>
    <p><span class="location"><label><b>Location:</b></label><?php echo esc_html($job_details['LocationTag']); ?></span></p>
    <p><span class="type"><label><b>Job Reference:</b></label><?php echo esc_html($job_details['Reference'])?></span></p>
    <p><span class="type"><label><b>Type:</b></label><?php echo $job_details['JobType']; ?></span></p>
    <p><span class="salary"><label><b>Salary: </b></label>&#163;<?php echo ($job_details['MinSalary']) ? esc_html($job_details['MinSalary']) .' to &#163; '. esc_html($job_details['MaxSalary']) : 'Negotiable';?></span></p>
    <p><span class="type"><label><b>Close Date:</b></label><?php echo esc_html(date('Y-m-d',strtotime($job_details['CloseDate'])))?></span></p>
    <p><span class="summary"><b>Summary: </b><?php echo nl2br(esc_html($job_details['JobDescription'])); ?></span></p>
    <p><span class="type"><label><b>Benefits:</b></label><?php echo esc_html($job_details['Benefits'])?></span></p>
    <p> <span class="consultantname"><label><b>Consultant Name:</b></label><?php echo esc_html($job_details['ConsultantName']); ?></span></p>
	<p><span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto:<?php echo $job_details['ConsultantEmail']; ?>"><?php echo esc_html($job_details['ConsultantEmail']); ?></a></span></p>
 <span>
<!--     <a class="view_morebtn" href="index.php?page_id=<?php echo $pageid?>&task=apply&jobid=<?php echo $_REQUEST['jobid']; ?>&job_title=<?php echo esc_html($job_details['JobTitle']); ?>">Apply</a>-->
     <a class="view_morebtn" href="index.php?page_id=<?php echo $pageid?>&task=apply&jobid=<?php echo $_REQUEST['jobid']; ?>&job_title=<?php echo esc_html($job_details['JobTitle']); ?>">Login & Apply</a>
     <a class="view_morebtn" href="index.php?page_id=<?php echo $pageid?>&task=apply&jobid=<?php echo $_REQUEST['jobid']; ?>&job_title=<?php echo esc_html($job_details['JobTitle']); ?>">Register & Apply</a>
 </span>
<a href="?page_id=<?php echo $pageid?>" class="view_morebtn">Back to Results</a>
</p>
</div>