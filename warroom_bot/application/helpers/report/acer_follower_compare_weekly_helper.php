<?php
function acer_follower_compare_weekly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Follower Compare Weekly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$date = '2012-01-07';
		$lastweek = new Datetime('2012-01-07');
		$lastweek->sub(new DateInterval('P6D'));
		$lastweek = $lastweek->format('Y-m-d');
			
		$sql = "SELECT f.*, f.follower - w.follower as diff, a.username from author_twitter_follower f
		left join (select * from author_twitter_follower where date(date) = '$lastweek') w
		on w.author_id = f.author_id
		left join (select * from author) a
		on a.id = w.author_id
		where date(f.date) = '$date'
		order by f.follower desc";
		
		$query = $CI->db->query($sql);
		
		$data['acer_report'] = $query->result();
		
		return $data;
}
?>