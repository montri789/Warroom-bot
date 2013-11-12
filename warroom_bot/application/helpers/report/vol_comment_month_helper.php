<?php
function vol_comment_month($month=null,$year=null,$client_id=null,$option=null)
{
	$CI =& get_instance();
	
	$data = array();
	$report_name = 'vol_comment_month';
	$report_title = 'Trend of Volume of Comments (by Month)';
	
	// select subjects from category "general"
	$category_name = "general";
	$sql = "SELECT subject.id,subject.subject FROM subject,categories WHERE 
		subject.cate_id = categories.cate_id AND
		client_id = $client_id AND
		matching_status = 'update' AND
		cate_name = '$category_name'";
	$query = $CI->db->query($sql);
	$subjects = $query->result_array();
	$query->free_result();
	
	// for each subject, count matchs by day
	foreach($subjects as $s)
	{
		// TALK : query count of matchs by month and year
		$sql_post = "SELECT count(*) as count, date(post.post_date) as date
		FROM matchs,post  
		WHERE matchs.post_id = post.id 
		AND post.type != 'tweet'
		AND post.type != 'retweet'
		AND post.type != 'fb_post'
		AND post.type != 'fb_comment' 
		AND subject_id = ".$s['id']."
		AND month(post.post_date) = $month
		AND year(post.post_date) = $year
		GROUP BY subject_id,date(post.post_date)";
		
		$qt = $CI->db->query($sql_post);
		$data["subject"][$s['id']]['talk'] = $qt->result();
		$qt->free_result();
		
		// Comment : query count of matchs by month and year
/*		$sql_comment = "SELECT count(*) as count, date(post.post_date) as date
		FROM matchs,post   
		WHERE matchs.post_id = post.id 
		AND post.type = 'comment'
		AND subject_id = ".$s['id']."
		AND month(post.post_date) = $month
		AND year(post.post_date) = $year 
		GROUP BY subject_id,date(post.post_date)";
		
		$qc = $CI->db->query($sql_comment);
		$data["subject"][$s['id']]['comment'] = $qc->result();
		$qc->free_result();
*/
	}
	
	//print_r($data["subject"]);
	// table data
	$data["headers"] = $subjects;
	
	//Template
	$data["report_name"] = $report_name;
	$data["report_title"] = $report_title;
	// $data["from_date"] = date('j F Y',mktime(0,0,0,$month,1,$year));
	// $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
	// $data["to_date"] = date('j F Y',mktime(0,0,0,$month,$days_in_month,$year));
	// $data["days"] = $days_in_month;
	// $data["month"] = $month;
	// $data["year"] = $year;
	
	return $data;
}
?>