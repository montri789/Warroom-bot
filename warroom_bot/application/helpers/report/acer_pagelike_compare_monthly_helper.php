<?php
function acer_pagelike_compare_monthly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Page Like Compare Monthly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$month = $month+1;
		if($month<10) $month = '0'.$month;
		$date = new Datetime('2012-'.$month.'-01');
		$date->sub(new DateInterval('P1D'));
		$date_format = $date->format('Y-m-d');
			
		$sql = "SELECT pg.*, fb.name from page_like_facebook pg, page_facebook fb
		where pg.facebook_id = fb.facebook_id
		and date(pg.date) = '$date_format'
		order by likes desc";
			
		$query = $CI->db->query($sql);
		
		$data['acer_report'] = $query->result();
		
		return $data;
}
?>