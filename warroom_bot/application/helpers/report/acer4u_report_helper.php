<?php
function acer4u_report($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer4u_report";
		$report_title = "Report Acer4u";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$month = $option['month'];
		$year = $option['year'];
			
		$sql = "SELECT date(ps.post_date) as date, title as topic , url as link from page pg, post ps
		WHERE ps.page_id = pg.id
		AND pg.domain_id = 131
		AND month(ps.post_date) = $month
		AND year(ps.post_date) = $year
		order by date(ps.post_date) desc";
			
		$query = $CI->db->query($sql);
		
		$data['acer4u_report'] = $query->result();;
		
		return $data;
}
?>