<?php
function acer_report_op($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report_op";
		$report_title = "Report Acer Online Support";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT date(p.post_date) as date, p.body as topic, 
		p.facebook_id as link, psfb.likes as likes
		from post p, post_facebook psfb
		where p.type = 'fb_post'
		and psfb.page_id = '147818843085'
		and psfb.post_id = p.id
		and month(p.post_date) = $month
		and year(p.post_date) = $year
		and p.author_id != '930087'";
			
		$query = $CI->db->query($sql);
		
		$data['acer_report_op'] = $query->result();;
		
		return $data;
}
?>