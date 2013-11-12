<?php
function acer_facebookfan_weekly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "acer_report";
		$report_title = "Report Acer Facebook Activity : Weekly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT name,likes from page_facebook WHERE id IN (1,2,3,4,5,6,7,8,9) order by likes desc";
			
		$query = $CI->db->query($sql);
		
		
		$weekly = array();
		foreach($query->result_array() as $row){
			$increase = 200000;
			
			array_push($weekly,array("name"=>$row["name"],"likes"=>$row["likes"],"increase"=>$increase));	
		}
		
		$data['acer_report'] = $weekly;
		
		return $data;
}
?>