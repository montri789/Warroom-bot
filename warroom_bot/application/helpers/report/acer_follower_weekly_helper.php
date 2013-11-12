<?php
function acer_follower_weekly($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$author_id = '397575';
	
		$data = array();
	
		$report_name = "acer_follower_weekly";
		$report_title = "Report Follower Twitter";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$start = '';
		$end = '';
		
		$month = $option["month"];
		
		switch($option["week"]){
			case 'week1' :
				$start = date("Y-$month-1");
				$end = date("Y-$month-7");
				break;
			case 'week2' :
				$start = date("Y-$month-8");
				$end = date("Y-$month-14");
				break;
			case 'week3' :
				$start = date("Y-$month-15");
				$end = date("Y-$month-21");
				break;
			case 'week4' :
				$start = date("Y-$month-22");
				$end = date("Y-$month-t", mktime(0, 0, 0,$month, 1,date('Y')));
				break;
			default : break;
		}		
		 
		//$start = '2012-01-1';
		//$end = '2012-01-7';
		
		$sql = "SELECT date,follower  
		        FROM author_twitter_follower
		        WHERE (date(date) between date('".$start."') AND date('".$end."') ) 
				AND author_id = '".$author_id."' ";
		
		$query = $CI->db->query($sql);
		
		$follower = array();
		
		foreach($query->result_array() as $row){	
			$date = explode("-",$row["date"]);
			array_push($follower,array("date"=>$date[2]."-".$date[1]."-".$date[0],"follower"=>$row["follower"]));
		}
		
		$data['acer_follower'] = $follower;
		
		return $data;
}
?>