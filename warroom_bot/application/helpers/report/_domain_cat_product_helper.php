<?php
	function domain_cat_product($month=null,$year=null,$client_id=null)
	{
		$CI =& get_instance();
		
		$data = array();
		$report_name = 'domain_cat_product';
		$report_title = 'Source of Conversations based on Thoth current categorized (from the domain names)';
	
		// select subjects from category "general"
		$category_name = "general";
		$sql = "SELECT subject.id,subject.subject FROM subject,categories WHERE 
			subject.cate_id = categories.cate_id AND
			client_id = $client_id AND
			matching_status != 'disable' AND
			cate_name = '$category_name'";
		$query = $CI->db->query($sql);
		$subjects = $query->result_array();
		$data['subjects'] = $subjects;
		$query->free_result();
		
		$sql = "SELECT dc.id, dc.name";
		
		foreach($subjects as $s)
		{
			$sql .= ', s_'.$s['id'];
		}
		
		$sql .= " FROM domain_categories dc\n";
		
		foreach($subjects as $s)
		{
			$sql .= "LEFT JOIN
					(SELECT dc.id as id, count(p.id) as s_".$s['id']." 
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
