<?php
function acer_follower_monthly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
		
		$author_id = '397575';
		
		$report_name = "acer_follower_monthly";
		$report_title = "Report Acer Follower : Monthly";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$month = $option["month"];
		$year = date("Y");
		
		$acer_follower = array();	
		
		$sql = "SELECT 	MAX(date) as 'date',follower 
				FROM 	author_twitter_follower 
				WHERE  	author_id = '$author_id'  
				GROUP 	BY month(date),year(date) ORDER BY date  LIMIT 1 ";	
		
		$query = $CI->db->query($sql);
		$row = $query->row_array();
		
		array_push($acer_follower,array("date"=>$row["date"],"follower"=>$row["follower"],"increase"=>""));
		
		$follower = $row["follower"];
		
		$sql = "SELECT MAX(date) as 'date',follower 
				FROM author_twitter_follower  
				WHERE  author_id = '$author_id' 
				AND month(date) <= '$month' 
				GROUP BY month(date),year(date) ORDER BY date DESC LIMIT 7 ";
		
		$query = $CI->db->query($sql);
		$res = $query->result_array();
		
		sort($res);
		
		foreach($res as $val){
			if($val["date"] != $row["date"]){
				
				$increase = $val["follower"] - $follower;
				$follower = $val["follower"];
				
				array_push($acer_follower,array("date"=>$val["date"],"follower"=>$follower,"increase"=>$increase));
			}
		}
		
		if(count($acer_follower) >= 8){
			unset($acer_follower[1]);
		}
				
		$data['acer_follower'] = $acer_follower;

		return $data;
}
?>