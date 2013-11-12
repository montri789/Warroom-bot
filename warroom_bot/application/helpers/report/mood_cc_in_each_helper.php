<?php
function mood_cc_in_each($month=null,$year=null,$client_id=null,$option=null){
		
		$CI =& get_instance();
		$data = array();
		
		$report_name = "mood_cc_in_each";
		$report_title = 'Mood (Sentiment) compared to competitors in each subtopics/factors';
		
		//$subject_id = 140;
		//$subject_name = "Samsung Smart TV";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		/*
		$compare = array();	
		if($subject_id != NULL){
			array_push($compare,array("subject_id"=>$subject_id,"subject_name"=>$subject_name));
			$sql = "SELECT 	id,subject   
				FROM 	subject 
				WHERE 	parent_id = ".$subject_id." 
					AND matching_status = 'update' ";
					
			$query = $CI->db->query($sql);
						
			foreach($query->result() as $row){
				array_push($compare,array("subject_id"=>$row->id,"subject_name"=>$row->subject));
			}
		}*/
		
		$sql = "SELECT 	id,subject
			FROM 	subject
			WHERE 	client_id = $client_id AND cate_id = 7 
			AND matching_status = 'update' ";
			
		$query = $CI->db->query($sql);
							
		$factors = array();
			
		foreach($query->result() as $row){   
				
				$compare = array();
				//$compare[] = $row->parent_id;
				array_push($compare,array("subject_id"=>$row->id,"subject_name"=>$row->subject));
				
				
				$sql2 = "SELECT id,subject  
					 FROM 	subject 
					 WHERE 	parent_id = ".$row->id." 
						AND matching_status = 'update' ";					
								
				$query2 = $CI->db->query($sql2);
						
				foreach($query2->result() as $row2){
					array_push($compare,array("subject_id"=>$row2->id,"subject_name"=>$row2->subject));
				}
											
				foreach($compare as $val_parent){
				
				//echo $val_parent["subject_id"]."<br/>";
					
					$sql = "SELECT 	id,subject 
						FROM 	subject 
						WHERE 	subject LIKE '".$val_parent["subject_name"]."%'
							AND id != ".$val_parent["subject_id"]." 
							AND matching_status = 'update' ";
							
					$query = $CI->db->query($sql);
					
					$mood = array();
					foreach($query->result() as $row_sub){
						
						$curr_subject = $row_sub->subject;
						
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
							AND s.id = ".$row_sub->id."
							AND (p.type = 'post' OR p.type = 'comment')
							ORDER BY post_date ";
						
						$query = $CI->db->query($sql);
											
						$s1 = 0; $s2 = 0; $s3 = 0; $s4 = 0; $s5 = 0;
						
						foreach($query->result() as $row_sen){
							if($row_sen->sentiment  <= 100 && $row_sen->sentiment >= 61){
								$s5++;
							}else if($row_sen->sentiment <= 60 && $row_sen->sentiment >= 21){
								$s4++;
							}else if($row_sen->sentiment <= 20 && $row_sen->sentiment >= -20){
								$s3++;
							}else if($row_sen->sentiment <= -21 && $row_sen->sentiment >= -60){
								$s2++;
							}else if($row_sen->sentiment <= -61 && $row_sen->sentiment >= -100){
								$s1++;
							}
						}
						
						$s = trim(str_replace($val_parent["subject_name"]." - ","",$curr_subject));
						
						array_push($mood,array("subject"=>$s,"vneg"=>$s1,"neg"=>$s2,"neu"=>$s3,"pos"=>$s4,"vpos"=>$s5));
					}
		
					$subtopic = array();
					$pos_vpos  = array();  
					$neu = array();
					$neg_vneg = array();
					$total = array();
					
					foreach($mood as $val_mood){
						$subtopic[] = $val_mood["subject"];
							
						$mood_total = $val_mood["vpos"] + $val_mood["pos"] + $val_mood["neu"] + $val_mood["neg"] + $val_mood["vneg"];
								
						$pos_vpos_data = $val_mood["vpos"] + $val_mood["pos"];
						
						$pos_vpos[] = array("mood"=>$pos_vpos_data,"per"=>(($mood_total == 0) ? "0" : ($pos_vpos_data * 100) / $mood_total));
					
						$neu[] = array("mood"=>$val_mood["neu"],"per"=>(($mood_total == 0) ? "0" : ($val_mood["neu"] * 100) / $mood_total));
						
						$neg_vneg_data = $val_mood["vneg"] + $val_mood["neg"];
						$neg_vneg[] = array("mood"=>$neg_vneg_data,"per"=>(($mood_total == 0) ? "0" : ($neg_vneg_data * 100) / $mood_total));
						
						$total[] = $mood_total;
					}
					
					$factors_mood = array();
					$factors_mood["subtopic"] = $subtopic;
					$factors_mood["pos_vpos"] = $pos_vpos;
					$factors_mood["neu"] = $neu;
					$factors_mood["neg_vneg"] = $neg_vneg;
					$factors_mood["total"] = $total;
							
					array_push($factors,array("subject_id"=>$val_parent["subject_id"],
							       "subject_name"=>$val_parent["subject_name"],
							       "mood"=>$factors_mood));
				}
		}
		
		$data["factors"] = $factors;
		
		return $data;
	
}
?>