<?php
	global $wpdb;
	$setting = $wpdb->get_row(@$wpdb->prepare("SELECT * FROM ".$wpdb->prefix."jobs_settings"));
	$feed_url = $setting->http_url;//'https://jobs.chameleoni.com/api/PostXML/PostXml.aspx';
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
	$summary_characters =  $setting->summary_characters;
	$t1tag = 'Web Location';
		/* Get T2 tags from T1 tag */
		$request = '<ChameleonIAPI>
			<Method>TagListT2ForT1</Method>
			<APIKey>'.$APIKey.'</APIKey>
			<UserName>'.$UserName.'</UserName>
			<Filter>
				<Param Name="T1" Value="'.$t1tag.'" />
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
			$job_taglist1 =  @$array['TagListT2ForT1']['Tag'];	
		}
	$t2tag = 'Web Expertise';
		/* Get T2 tags from T1 tag */
		$request = '<ChameleonIAPI>
			<Method>TagListT2ForT1</Method>
			<APIKey>'.$APIKey.'</APIKey>
			<UserName>'.$UserName.'</UserName>
			<Filter>
				<Param Name="T1" Value="'.$t2tag.'" />
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
			$job_taglist2 =  @$array['TagListT2ForT1']['Tag'];
		}
		$post = @$_REQUEST['post'];
		$PageNo = (@$_REQUEST['PageNo']) ? @$_REQUEST['PageNo'] : ((@$_SESSION['PageNo']) ? @$_SESSION['PageNo'] : 1);
		$_SESSION['PageNo'] = $PageNo;
		$filter = (@$_REQUEST['filter']) ? @$_REQUEST['filter'] : ((@$_SESSION['filter']) ? @$_SESSION['filter'] : array());
		$_SESSION['filter'] = $filter;
		$permanent = true;
		$contract = true;
		$temporary = true;
		if(count($_SESSION['filter']) > 0){
			if(isset($_SESSION['filter']['permanent'])){ $permanent = true;} else {$permanent = false;}
			if(isset($_SESSION['filter']['contract'])) {$contract = true;} else {$contract = false;}
			if(isset($_SESSION['filter']['temporary'])) {$temporary = true;} else {$temporary = false;}
		}
		$page_rows = 5;
		$t1tag=$t2tag='';
		$t1tag = @$_REQUEST['t1tag'] ? @$_REQUEST['t1tag'] : '' ;
		$t2tag = @$_REQUEST['t2tag'] ? @$_REQUEST['t2tag'] : '';
		$taglistpass = $t1tag.','.$t2tag;

		$taglistpass = trim($taglistpass,',');
		$request = '<ChameleonIAPI>
						<Method>SearchVacancies</Method>
						<APIKey>'.$APIKey.'</APIKey>
						<UserName>'.$UserName.'</UserName>
						<Filter>
									  <Param Name="Permanent" Value="'.$permanent.'" />
									  <Param Name="Contract" Value="'.$contract.'" />
									  <Param Name="Temporary" Value="'.$temporary.'" />
									  <Param Name="PageNo" Value="'.$_SESSION['PageNo'].'" />
									  <Param Name="PageSize" Value="'.$page_size.'" />
									  <Param Name="TagIDCSV" Value="!AND'.$taglistpass.'" />
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
		/*echo $array['SearchVacanciesCount'];
		echo '<pre>';
		print_r($array['SearchVacanciesCount']);
		echo '</pre>';*/
		//exit;
		if($array['Status'] == 'Pass'){
			
			if($array['SearchVacanciesCount'] == 1)
			{	
			  $jobs_vac =  @$array['Vacancies'];	
			  $total = (isset($array['Vacancies']['Vacancy'])) ? $array['Vacancies']['Vacancy']['TotalCount'] : $array['Vacancies']['Vacancy']['TotalCount'];
			}
			else
			{ 
				$jobs_vac =  @$array['Vacancies']['Vacancy']; 
				@$total = (isset($array['Vacancies']['Vacancy'][0])) ? $array['Vacancies']['Vacancy'][0]['TotalCount'] : $array['Vacancies']['Vacancy']['TotalCount'];
			}
		}  
		$_SESSION['currentpage']  = $_SESSION['PageNo'];
?>
<div class="chameleoni_listing">
<div class="search_form">
<div id="vacancy_search_top"><h3>Vacancy Search</h3></div>
<form name="frmsearch" action="" method="post">
    <table border="0" style="width:100%">
		<tbody><tr>
        	<td>
            	<label for="location" class="searchlabel">Location</label>
                <select name="t1tag" id="t1tag">
                <option value="">Select</option>
                <?php foreach($job_taglist1 as $value )
				  { ?>
				<option value="<?php echo $value['TagId']?>" <?php if($value['TagId'] == @$t1tag){?> selected="selected"<?php } ?>><?php echo $value['Tag']?></option>
				<?php }
				?>
                </select>
            </td>
           </tr> 
           <tr>
            <td>
            	<label for="tag" class="searchlabel">Sector</label>
                <select name="t2tag" id="t2tag">
                <option value="">Select</option>
                <?php foreach($job_taglist2 as $value )
				  { ?>
				<option value="<?php echo $value['TagId']?>" <?php if($value['TagId'] == @$t1tag){?> selected="selected"<?php } ?>><?php echo $value['Tag']?></option>
				<?php }
				?>
             </select>
            </td></tr>
            <tr>
            	<td>
                	<input type="checkbox"  id="permanent" name="filter[permanent]" <?php if($permanent){?> checked="checked" <?php } ?> >
                    <label for="permanent">Permanent</label>
                    <input type="checkbox"  <?php if($contract){?> checked="checked" <?php } ?> id="contract" name="filter[contract]" >
                    <label for="Contract">Contract</label>
                    <input type="checkbox"  id="temporary" name="filter[temporary]" <?php if($temporary){?> checked="checked" <?php } ?>>
                    <label for="Temporary">Temporary</label>
                </td>
            </tr>
            <tr>
            <td><input type="submit" name="submit" value="Search" class="jobapply"></td>
        </tr>    
    </tbody></table>
</form>
</div>
</div>
<?php
global $post;
$pageid = $post->ID;
$det =  JOBINFO_URL . '/jobs_details.php';
//echo '<a href"'.$det.'">View</a>';

if(!empty($jobs_vac)){
	echo '<h2 class="title">Search Results</h2>';
   foreach($jobs_vac as $job){
echo "test";
?>
        
	<div class="job">
    	<p><span class="type"><label><b>Job Title:</b></label><?php echo esc_html($job['JobTitle'])?></span></p>
        <?php if($feed_location == '1') { ?>
        <p><span class="location"><label><b>Location:</b></label><?php echo esc_html(is_array($job['LocationTag'])?implode(', ',$job['LocationTag']):$job['LocationTag']); ?></span><br></p>
        <?php } ?>
        <p><span class="type"><label><b>Job Reference:</b></label><?php echo esc_html(($job['Reference']))?></span></p>
        <?php   if($feed_type == '1') { ?>
        <p><span class="type"><label><b>Type:</b></label><?php echo esc_html(($job['JobType']))?></span></p>
        <?php }   if($feed_salary == '1' && $job['MinSalary'] != '') { ?>
        <p><span class="salary"><label><b>Salary:</b></label>&#163;<?php echo ($job['MinSalary']) ? esc_html($job['MinSalary']) .' to &#163;'. $job['MaxSalary'] : 'Negotiable'?></span><br /></p> 
        <?php } ?>
        <p><span class="type"><label><b>Close Date:</b></label><?php echo esc_html(date('Y-m-d',strtotime($job['CloseDate'])))?></span></p>
		<?php  if($feed_summary == '1' && $job['JobDescription'] != '') {
			?>       
        <p><span class="summary"><label><b>Summary:</b></label>		<?php echo ($job['JobDescription']) ? substr(nl2br($job['JobDescription']),0,$summary_characters).((strlen($job['JobDescription']) > $summary_characters) ? '.....' : '') : ''?></span><br /></p> 
        <?php }  ?>   
       <p><span class="consultantname"><label><b>Consultant Name:</b></label><?php echo esc_html($job['ConsultantName'])?></span></p>
       <p> <span class="consultantemail"><label><b>Consultant Email:</b></label><a href="mailto: <?php echo esc_html($job['ConsultantEmail']); ?>"><?php echo $job['ConsultantEmail']?></a></span></p>
 	  <span><a class="view_morebtn" href="?page_id=<?php echo $pageid; ?>&task=jobdetails&jobid=<?php echo esc_html($job['VacancyID']); ?>">View More</a> </span>
      </div>
      

<?php 	}  // foreach 
?>

      <form name="frmpagination" id="frmpagination" method="post" action="">


<?php //This is the number of results displayed per page 
//This checks to see if there is a page number. If not, it will set it to page 1 

 if (!(isset($pagenum))) 
 { 
	 $pagenum = 1; 
 } 
 $page_rows = 5; 
 //This tells us the page number of our last page 
 $last = ceil($total/$page_rows); 
 //this makes sure the page number isn't below one, or more than our maximum pages 
 if ($pagenum < 1) 
 { 
	 $pagenum = 1; 
 } 
 elseif ($pagenum > $last) 
 { 
	 $pagenum = $last; 
 } 
 $pagenum;
?>
<ul class="pageul"><?php 
if($_SESSION['currentpage'] != 1){
?>
<li onclick="nextprevious('p')"><b>Previous << </b> </li>
<!--<input    type="button" name="pervious" value="<<"  />-->
<?php 
}
for($t=1;$t<=$last;$t++){
	?>
      <li onclick="mysubmit(<?php echo $t ?>);" ><b><?php echo $t; ?></b> </li>
	
<?php } ?>
<?php if($last != $_SESSION['currentpage']){ ?>
    <!-- <input    onclick="nextprevious('n')" type="button" name="next" value=">>"  />-->
     <li onclick="nextprevious('n')"><b>Next >></b> </li>
     <?php } ?>
     </ul>
<input type="hidden" name="PageNo" id="PageNo" value="1" />
</form>
 <?php } // if
else { ?>
	<h3>No listing found</h3>
<?php } ?>
<script type="text/javascript">
function nextprevious(ch){
	if(ch == 'p'){
		document.getElementById('PageNo').value = <?php echo  $_SESSION['currentpage'] -1 ?>;
	}else if(ch == 'n'){
			document.getElementById('PageNo').value = <?php echo  $_SESSION['currentpage'] + 1 ?>;
	} 
	document.frmpagination.submit();
	
}
function mysubmit(PageNo){
	document.getElementById('PageNo').value = PageNo;
	document.frmpagination.submit();
}
</script>
<style>
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
</style>