<?php
	function topics_compare_competitors($month=null,$year=null,$client_id=null,$option=null)
	{
		$CI =& get_instance();
		
		$data = array();
		$report_name = 'topics_compare_competitors';
		$report_title = 'Topics of messages compared with competitors';
	
		// select subjects LIKE " - " and count all matchs
		$sql = "SELECT count(m.post_id) as count,s.subject 
			FROM 	matchs m, post p, subject s
			WHERE 	m.post_id = p.id 
				AND m.subject_id = s.id
				AND s.matching_status = 'update'
				AND month(p.post_date) = $month
				AND year(p.post_date) = $year
				AND s.subject like '% - %'
				AND s.client_id = $client_id
				AND p.type != 'tweet'
				AND p.type !='retweet'
				AND p.type != 'fb_post'
				AND p.type != 'fb_comment'
				GROUP BY m.subject_id ";
				
		//echo $sql."<br/>";
		
		$query = $CI->db->query($sql);
		$data['data'] = $query->result_array();
	
	
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
