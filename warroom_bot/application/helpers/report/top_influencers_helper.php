<?php
function top_influencers($month=null,$year=null,$client_id=null,$option=null){
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "top_influencers";
		$report_title = "Top Influencers (Including Seedings)";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
			
		$sql = "SELECT 	id,subject
			FROM 	subject
			WHERE 	client_id = $client_id AND cate_id = 7
			AND matching_status = 'update' ";
			
		$query = $CI->db->query($sql);
		
		$subject_id = array();
		$subject_name = array();
		foreach($query->result() as $row){
			$subject_id[] = $row->id;
			$subject_name[] = $row->subject;
		}
		$data["subject"] = $subject_name;
		$data["subject_id"] = $subject_id;	
		
		$select = "SELECT id,username,s".implode(",s",$subject_id)." FROM author a "; 
			
		$join = "";	
		foreach($subject_id as $val){
			$join .= " left join 
				(SELECT p.author_id, COUNT(p.id) as s$val FROM post p, matchs m, subject s, author a 
				WHERE p.id = m.post_id 
				AND s.id = m.subject_id 
				AND a.id = p.author_id 
				AND p.type != 'tweet' 
				AND p.type != 'retweet' 
				AND p.type != 'fb_post' 
				AND p.type != 'fb_comment' 
				AND subject_id = $val 
				AND MONTH(p.post_date) = $month  
				AND YEAR(p.post_date) = 2012 
				GROUP BY p.author_id 
				ORDER BY s$val desc) s_$val ON s_$val.author_id = a.id ";	
		}
		
		$where = "WHERE s".implode(" != 'null' or s",$subject_id)." != 'null'";
				
		$sql = $select." ".$join." ".$where;
		
		$query = $CI->db->query($sql);
		
		$top_data = array();
		$total = array();
		
		foreach($query->result_array() as $row){
			$row_data = array();
			
			foreach($subject_id as $id){
				$row_data["s".$id] = $row["s".$id];
				$total["total_s".$id] = (!isset($total["total_s".$id])) ? $row["s".$id] : ($total["total_s".$id] + $row["s".$id]);
			}
			
			array_push($top_data,array("username"=>$row["username"],"col"=>$row_data));
		}
	
		$total_msg = array();
		foreach($total as $val){
			$total_msg[] = (empty($val)) ? 0 : $val;
		}
		
		
		$data["tops"] = $top_data;
		$data["total_msg"] = $total_msg;
		
		return $data;
}
?>