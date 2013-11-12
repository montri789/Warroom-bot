<?php
function acer_twitter_activity($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_twitter_activity";
		$report_title = "Report Acer Twitter Activity";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT p.id, date(p.post_date) as date, body, reach, tweet_id from post p
		left join (select max(count) as reach, r.post_id from reach r
		group by r.post_id) r on r.post_id = p.id
		where author_id = 397575
		and month(p.post_date) = $month
		and year(p.post_date) = $year
		order by date(p.post_date) desc";
			
		$query = $CI->db->query($sql);
		
		$data['acer_twitter_activity'] = $query->result();;
		
		return $data;
}
?>