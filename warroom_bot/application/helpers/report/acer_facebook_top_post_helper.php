<?php
function acer_facebook_top_post($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_facebook_top_post";
		$report_title = "Report Acer Facebook Top Post";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT author_id, count(p.id) as count, a.username, a.facebook_id from post p, author a
		where p.facebook_id LIKE '147818843085_%'
		and month(p.post_date) = $month
		and year(p.post_date) = $year
		and a.id = author_id
		and a.facebook_id != 147818843085
		group by author_id
		order by count desc";
			
		$query = $CI->db->query($sql);
		
		$data['acer_facebook_top_post'] = $query->result();
		
		$sql = "SELECT p.id, p.body, p.post_date, p.facebook_id as link, p.type, a.username, a.facebook_id from post p, author a
		where p.facebook_id LIKE '147818843085_%'
		and month(p.post_date) = $month
		and year(p.post_date) = $year
		and a.facebook_id != 147818843085
		and a.id = author_id";
		
		$query = $CI->db->query($sql);
		
		$data['acer_facebook_top_post_list'] = $query->result();
		
		
		return $data;
}
?>