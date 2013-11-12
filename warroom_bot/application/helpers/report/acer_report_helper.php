<?php
function acer_report($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Facebook Activity";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT p.author_id, date(p.post_date) as date, 
		p.body as topic, psfb.likes as likes, p.type as type, p.facebook_id as link 
		from post_facebook psfb, post p
		where psfb.page_id = '147818843085'
		and psfb.post_id = p.id
		and month(p.post_date) = $month 
		and year(p.post_date) = $year
		and p.author_id = '930087'
		and p.type = 'fb_post'
		order by date(p.post_date) desc";
			
		$query = $CI->db->query($sql);
		
		$data['acer_report'] = $query->result();;
		
		return $data;
}
?>