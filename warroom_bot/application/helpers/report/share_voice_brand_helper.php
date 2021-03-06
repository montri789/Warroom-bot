<?php
	function share_voice_brand($month=null,$year=null,$client_id=null,$option=null)
	{
		$CI =& get_instance();
		
		$data = array();
		$report_name = 'share_voice_brand';
		$report_title = 'Share of Voice messages compared to competitors';
	
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
		
		foreach($subjects as $s)
		{
			// select competitor subject for each subject
			$competitors = '';
			$query = $CI->db->get_where('subject',array('parent_id'=>$s['id'],'matching_status !='=>'disable'));
			$data['competitor'][$s['id']] = $query->result_array();
			foreach($query->result() as $row) {$competitors .= ','.$row->id;}
			$query->free_result();
			
			// query talk for subjects and its competitors
			// TALK : query count of matchs by month and year
			$sql_talk = "SELECT subject.subject, count(*) as count
						FROM matchs,post,subject
						WHERE matchs.post_id = post.id
						AND matchs.subject_id = subject.id 
						AND post.type != 'tweet'
						AND post.type != 'retweet'
						AND post.type != 'fb_post'
						AND post.type != 'fb_comment'
						AND subject_id IN (".$s['id'].$competitors.")
						AND month(post.post_date) = $month
						AND year(post.post_date) = $year
						GROUP BY subject_id";
						
			$qt = $CI->db->query($sql_talk);
			$data['data'][$s['id']] = $qt->result_array();
			
		}
	
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
