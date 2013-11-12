<?php
function acer_pagelike_compare_weekly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Page Like Compare Weekly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$date = '2012-01-07';
		$lastweek = new Datetime('2012-01-07');
		$lastweek->sub(new DateInterval('P6D'));
		$lastweek = $lastweek->format('Y-m-d');
			
		$sql = "SELECT pg.*, pg.likes - w.likes as diff, fb.name from page_like_facebook pg
		left join (select * from page_like_facebook p where date(p.date) = '$lastweek') w on w.facebook_id = pg.facebook_id
		left join (select * from page_facebook) fb on fb.facebook_id = pg.facebook_id
		where date(pg.date) = '$date'
		order by w.likes desc";
		
		$query = $CI->db->query($sql);
		
		$data['acer_report'] = $query->result();
		
		return $data;
}
?>