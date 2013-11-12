<?php
	function show_content_details($month=null,$year=null,$client_id=null,$option=null){
		
		$CI =& get_instance();
			
		$data = array();
		$report_name = 'show_content_details';
		$report_title = 'Show Very Positive / Positive / Negative / Very Negative Content Details with URL';
	
		$category_name = "general";
		$sql = "SELECT 	subject.id,subject.subject 
			FROM 	subject,categories
			WHERE 	subject.cate_id = categories.cate_id AND 
				client_id = $client_id AND 
				matching_status != 'disable' AND 
				cate_name = '$category_name'  ";
			
		$query = $CI->db->query($sql);
		$subjects = $query->result_array();
		$data['subjects'] = $subjects;
		
		$arr = array();
		
		foreach($subjects as $s){
				
			$sql = "SELECT 	id,subject 
				FROM 	subject 
				WHERE 	subject LIKE '".$s["subject"]."%'
					AND id != ".$s["id"]." 
					AND matching_status = 'update'  ";
							
			$query = $CI->db->query($sql);
			
			$post = array();
			
			foreach($query->result_array() as $row){
				
				$sql = "SELECT 	p.id as 'post_id',
						(CASE post_date 
							WHEN '0000-00-00 00:00:00' THEN parse_date 
							ELSE post_date
							END) as 'post_date',dt.name as 'domain_type',
						d.name as 'domain_name',c.cate_name as 'categories',
						c.cate_name,s.subject,pa.url as 'page_url',p.title as 'post_title', 
						d.root_url as 'root_url',m.sentiment,p.body as 'post_body',a.username as 'by'       
					FROM 	post p,matchs m,subject s,page pa,  
						domain d,domain_type dt,domain_categories dc,categories c ,
						author a 
					WHERE 	s.id = ".$row["id"]."  
						AND m.subject_id = s.id 
						AND p.id = m.post_id  
						AND p.page_id = pa.id 
						AND pa.domain_id = d.id 
						AND d.domain_type_id = dt.id 
						AND d.domain_cate_id = dc.id 
						AND s.cate_id = c.cate_id
						AND matching_status = 'update'
						AND (p.type = 'post' OR p.type = 'comment') 
						AND month(p.post_date) = $month 
						AND year(p.post_date) = $year
						AND (m.sentiment > 20 OR m.sentiment < -20)
						AND p.author_id = a.id ORDER BY sentiment DESC ";
				
				//echo $sql;
				//echo "<br/><hr/><br/>";
				
				$query = $CI->db->query($sql);
				foreach($query->result_array() as $p_row){
					
					$topic = trim(str_replace($s["subject"]." - ","",$p_row["subject"]));
					$link = substr($p_row["root_url"],0,-1)."".$p_row["page_url"];
					
					$sentiment = "";
					if($p_row["sentiment"]  <= 100 && $p_row["sentiment"] >= 61){
						$sentiment = "Very Positive";
					}else if($p_row["sentiment"] <= 60 && $p_row["sentiment"] >= 21){
						$sentiment = "Positive";
					}else if($p_row["sentiment"] <= 20 && $p_row["sentiment"] >= -20){
						$sentiment = "Neutral";
					}else if($p_row["sentiment"] <= -21 && $p_row["sentiment"] >= -60){
						$sentiment = "Negative";
					}else if($p_row["sentiment"] <= -61 && $p_row["sentiment"] >= -100){
						$sentiment = "Very Negative";
					}
								
					array_push($post,array("topic"=>$topic,
						       "sentiment"=>$sentiment,
						       "mood"=>$p_row["sentiment"],
						       "post_date"=>$p_row["post_date"],
						       "source"=>$p_row["domain_type"],
						       "website"=>$p_row["domain_name"],
						       "content"=>$p_row["post_body"],
						       "link"=>$link,
						       "influencer"=>$p_row["by"]));
				}
			}
			
			array_push($arr,array("subject"=>$s["subject"],"post"=>$post));
		}
		
		
		
		$data["data"] = $arr;
				
	
		//print_r($data['data']);
	
		// table data
		$data["headers"] = '';
	
		//Template
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		// $data["from_date"] = date('j F Y',mktime(0,0,0,$month,1,$year));
		// $days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
		// $data["to_date"] = date('j F Y',mktime(0,0,0,$month,$days_in_month,$year));
		// $data["days"] = $days_in_month;
		// $data["month"] = $month;
		// $data["year"] = $year;
	
		return $data;
	}
?>
