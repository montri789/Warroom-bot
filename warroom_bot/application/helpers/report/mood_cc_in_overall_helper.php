<?php
function mood_cc_in_overall($month=null,$year=null,$client_id=null,$option=null)
{
		$CI =& get_instance();
	
		$data = array();
	
		$report_name = "mood_cc_in_overall";
		$report_title = "Mood (Sentiment) compared to competitors in overall";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
	
		
		$subject_id = NULL;//140;  //Samsung Smart TV 
	
	
		if($subject_id != NULL){
			$compare[] = $subject_id;
			$sql = "SELECT 	id  
				FROM 	subject 
				WHERE 	parent_id = ".$subject_id." 
					AND matching_status = 'update' ";
					
			$query = $CI->db->query($sql);
						
			foreach($query->result() as $row){
				$compare[] = $row->id;	
			}
		}else{
			
			$sql = "SELECT 	parent_id
				FROM 	subject
				WHERE 	client_id = ".$client_id." 
					AND matching_status = 'update' 
					AND cate_id = 8 
					AND parent_id != 0 
					GROUP BY parent_id ";
		
			$query = $CI->db->query($sql);
						
			
			$overall = array();
			
			foreach($query->result() as $row){   
				
				$compare = array();
				$compare[] = $row->parent_id;
				
				$sql2 = "SELECT id 
					 FROM 	subject 
					 WHERE 	parent_id = ".$row->parent_id." 
						AND matching_status = 'update' ";					
								
				$query2 = $CI->db->query($sql2);
						
				foreach($query2->result() as $row2){
					$compare[] = $row2->id;
				}
					
				$chartData = array();
		
				foreach($compare as $val){			
					$sql = "SELECT subject FROM subject WHERE id = $val ";
					$query = $CI->db->query($sql);
					$row = $query->row();
					
					$subject_name = $row->subject;
						
					$sql = "SELECT 	DATE(CASE post_date 
							WHEN '0000-00-00 00:00:00' THEN parse_date
							ELSE post_date
							END) as 'post_date',sentiment   
						FROM 	post p,matchs m,subject s 
						WHERE 	p.id = m.post_id   
							AND m.subject_id = s.id 
							AND s.client_id = $client_id    
							AND month(p.post_date) = $month
							AND year(p.post_date) = $year
							AND matching_status = 'update'
							AND p.type != 'tweet' 
							AND p.type != 'retweet' 
							AND p.type != 'fb_post' 
							AND p.type != 'fb_comment' 
							AND s.id = $val ORDER BY post_date ";
						
					$query = $CI->db->query($sql);
										
					$s1 = 0;
					$s2 = 0;
					$s3 = 0;
					$s4 = 0;
					$s5 = 0;
					
					foreach($query->result() as $row){
						if($row->sentiment  <= 100 && $row->sentiment >= 61){
							$s5++;
						}else if($row->sentiment <= 60 && $row->sentiment >= 21){
							$s4++;
						}else if($row->sentiment <= 20 && $row->sentiment >= -20){
							$s3++;
						}else if($row->sentiment <= -21 && $row->sentiment >= -60){
							$s2++;
						}else if($row->sentiment <= -61 && $row->sentiment >= -100){
							$s1++;
						}
					}
						
					$chartData[] = array("subject"=>$subject_name,"vneg"=>$s1,"neg"=>$s2,"neu"=>$s3,"pos"=>$s4,"vpos"=>$s5);
				}	
				
				$subject = array();
				$vpos  = array();  
				$pos = array();
				$neu = array();
				$neg = array();
				$vneg = array();
				$total = array();
				
				foreach($chartData as $val){
					$subject[] = $val["subject"];
						
					$mood_total = $val["vpos"] + $val["pos"] + $val["neu"] + $val["neg"] + $val["vneg"];
						
					$vpos[] = array("mood"=>$val["vpos"],"per"=>($mood_total == 0) ? "0" : ($val["vpos"] * 100) / $mood_total);
				
					$pos[] = array("mood"=>$val["pos"],"per"=>($mood_total == 0) ? "0" : ($val["pos"] * 100) / $mood_total);
				
					$neu[] = array("mood"=>$val["neu"],"per"=>($mood_total == 0) ? "0" : ($val["neu"] * 100) / $mood_total);
					
					$neg[] = array("mood"=>$val["neg"],"per"=>($mood_total == 0) ? "0" : ($val["neg"] * 100) / $mood_total);
		
					$vneg[] = array("mood"=>$val["vneg"],"per"=>($mood_total == 0) ? "0" : ($val["vneg"] * 100) / $mood_total);
					
					$total[] = $mood_total;
				}
				
				$overall_data = array();
				$overall_data["subject"] = $subject;
				$overall_data["vpos"] = $vpos;
				$overall_data["pos"] = $pos;
				$overall_data["neu"] = $neu;
				$overall_data["neg"] = $neg;
				$overall_data["vneg"] = $vneg;
				$overall_data["total"] = $total;
			
				array_push($overall,$overall_data);
			}
		}
		
		$data["overall"] = $overall;
		
		return $data;
	
}
?>