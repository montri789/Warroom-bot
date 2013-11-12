<?php
function acer_follower_compare_monthly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Follower Compare Monthly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$month = $month+1;
		if($month<10) $month = '0'.$month;
		$date = new Datetime('2012-'.$month.'-01');
		$date->sub(new DateInterval('P1D'));
		$date_format = $date->format('Y-m-d');
			
		$sql = "SELECT f.*, a.username from author_twitter_follower f, author a
		where date(f.date) = '$date_format'
		and f.`author_id` = a.id
		order by f.follower desc";
		
		echo $sql;
			
		$query = $CI->db->query($sql);
		
		$data['acer_report'] = $query->result();
		
		return $data;
}
?>