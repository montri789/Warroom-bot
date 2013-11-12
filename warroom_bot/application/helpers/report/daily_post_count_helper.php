<?php
function daily_post_count($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "daily_post_count";
		$report_title = "Post (Post+Comment) Counts per day per website";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$date = $year.'-'.$month.'-01';
		$from_date = new Datetime($date);
		$from = $from_date->format('Y-m-d');
		$from_date->add(new DateInterval('P1M'));
		$to = $from_date->format('Y-m-d');	


		$sql = "SELECT * from daily_post_count
		where date(date) >= '$from'
		and date(date) <= '$to'";
			
		$query = $CI->db->query($sql);
		
		$data['post_count'] = $query->result();
		$data['from'] = $from;
		$data['to'] = $to;
		
		return $data;
}
?>