<?php
	function domain_cat_product($month=null,$year=null,$client_id=null,$option=null){
		
		$CI =& get_instance();
			
		$data = array();
		$report_name = 'domain_cat_product';
		$report_title = 'Source of Conversations based on Thoth current categorized (from the domain names)';
	
		// select subjects from category "general"
		$category_name = "general";
		$sql = "SELECT 	subject.id,subject.subject 
			FROM 	subject,categories WHERE 
				subject.cate_id = categories.cate_id AND 
				client_id = $client_id AND 
				matching_status != 'disable' AND 
				cate_name = '$category_name' ";
			
		$query = $CI->db->query($sql);
		$subjects = $query->result_array();
		$data['subjects'] = $subjects;
		
		$sql = "SELECT dc.id,dc.name FROM domain_categories dc ORDER BY name DESC  ";
		$query = $CI->db->query($sql);
		$domain_cate = $query->result_array();
		$data["domain_cate"] = $domain_cate;
		
		//print_r($data["domain_cate"]);
		
		$domain = array();
		
		foreach($domain_cate as $d){
			
			$vneg = array();
			$neg = array();
			$neu = array();
			$pos = array();
			$vpos = array();
			$total = array();
				
			foreach($subjects as $s){
			
				$sql = "SELECT p.id ,sentiment 
					FROM matchs m, post p, page g, domain d, domain_categories dc
					WHERE m.post_id = p.id 
					AND p.page_id = g.id 
					AND g.domain_id = d.id
					AND d.domain_cate_id = dc.id
					AND month(p.post_date) = $month
					AND year(p.post_date) = $year
					AND m.subject_id = ".$s['id']."
					AND dc.id = ".$d["id"]." ";
				
				//echo $sql."<br/><hr/><br/>";
				
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
						
				$vneg[] = $s1;
				$neg[] = $s2;
				$neu[] = $s3;
				$pos[] = $s4;
				$vpos[] = $s5;
				$total[] = $s1 + $s2 + $s3 + $s4 + $s5;
					
			}
			
			$row = array();
			$row["vneg"] = $vneg;
			$row["neg"] = $neg;
			$row["neu"] = $neu;
			$row["pos"] = $pos;
			$row["vpos"] = $vpos;
			$row["total"] = $total;
			
			array_push($domain,array("id"=>$d["id"],"name"=>$d["name"],"row"=>$row));
		}
		
		$data['data'] = $domain;
		
		//print_r($domain);
		
		/* 
		$query->free_result();
		
		$sql = "SELECT dc.id, dc.name";
		
		foreach($subjects as $s){
			$sql .= ', s_'.$s['id'];
		}
		
		$sql .= " FROM domain_categories dc\n";
		
		foreach($subjects as $s){
			$sql .= "LEFT JOIN
				(SELECT dc.id as id,m.sentiment,count(p.id) as s_".$s['id']." 
				FROM matchs m, post p, page g, domain d, domain_categories dc
				WHERE m.post_id = p.id 
				AND p.page_id = g.id 
				AND g.domain_id = d.id
				AND d.domain_cate_id = dc.id
				AND month(p.post_date) = $month
				AND year(p.post_date) = $year
				AND m.subject_id = ".$s['id']."
				GROUP BY dc.name) as s".$s['id']." on dc.id = s".$s['id'].".id\n";
		}
		
		$query = $CI->db->query($sql);
		$data['data'] = $query->result_array();
		
		*/
		
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
