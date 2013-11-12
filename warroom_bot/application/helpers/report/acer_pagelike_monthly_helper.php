<?php
function acer_pagelike_monthly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
		
		$facebook_id = '147818843085';
		
		$report_name = "acer_page_like_fb_monthly";
		$report_title = "Report Pages Likes Facebook : Monthly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$month = $option["month"];
		$year = date("Y");
		
		$pagelike = array();	
		
		$sql = "SELECT MAX(date) as 'date',likes 
				FROM page_like_facebook 
				WHERE  	 facebook_id = '$facebook_id'  
				GROUP BY month(date),year(date) ORDER BY date  LIMIT 1 ";	
		
		$query = $CI->db->query($sql);
		$row = $query->row_array();
		
		array_push($pagelike,array("date"=>$row["date"],"likes"=>$row["likes"],"increase"=>""));
		
		$like = $row["likes"];
		
		$sql = "SELECT MAX(date) as 'date',likes 
				FROM page_like_facebook 
				WHERE  facebook_id = '$facebook_id' 
				AND month(date) <= '$month' 
				GROUP BY month(date),year(date) ORDER BY date DESC LIMIT 7 ";
		
		$query = $CI->db->query($sql);
		$res = $query->result_array();
		
		sort($res);
		
		foreach($res as $val){
			if($val["date"] != $row["date"]){
				
				$increase = $val["likes"] - $like;
				$like = $val["likes"];
				
				array_push($pagelike,array("date"=>$val["date"],"likes"=>$like,"increase"=>$increase));
			}
		}
		
		if(count($pagelike) >= 8){
			unset($pagelike[1]);
		}
				

		$data['acer_pagelike'] = $pagelike;

		return $data;
}
?>