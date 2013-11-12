<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test_report extends CI_Controller {
	
	public function index($title){}
	public function title($report_name){
		// report title
		//$report_name = "vol_comment_brand";
		
		// test client = Samsung
		$client_id = 7;
		$month = 3;
		$year = 2012;
		
		// get date period
		if($month==null || $month < 1 || $month > 12) $month = date('n');
		if($year==null || $year < 2012 || $year > date('Y')) $year = date('Y');
		
		// get client
		if($client_id==null)
		{
			echo "invalid client";
			return false;
		}
		else
		{
			$query = $this->db->get_where('clients',array('client_id'=>$client_id));
			$client_name = $query->row()->client_name;
		}
		
		// Call Helpers
		//$this->load->helper('/report/'.$report_name);
		$data = $this->$report_name($month,$year,$client_id);
		$data["client_name"] = $client_name;
		$data["from_date"] = date('j F Y',mktime(0,0,0,$month,1,$year));
		$days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
		$data["to_date"] = date('j F Y',mktime(0,0,0,$month,$days_in_month,$year));
		$data["days"] = $days_in_month;
		$data["month"] = $month;
		$data["year"] = $year;
		
		// Load to view
		$data["content"] = $this->load->view("report/".$report_name,$data,true);
		$this->load->view('report/template',$data);
	}
	public function mood_cc_in_overall($month,$year,$client_id){
		
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
					
			$query = $this->db->query($sql);
						
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
		
			$query = $this->db->query($sql);
						
			
			$overall = array();
			
			foreach($query->result() as $row){   
				
				$compare = array();
				$compare[] = $row->parent_id;
				
				$sql2 = "SELECT id 
					 FROM 	subject 
					 WHERE 	parent_id = ".$row->parent_id." 
						AND matching_status = 'update' ";					
								
				$query2 = $this->db->query($sql2);
						
				foreach($query2->result() as $row2){
					$compare[] = $row2->id;
				}
					
				$chartData = array();
		
				foreach($compare as $val){			
					$sql = "SELECT subject FROM subject WHERE id = $val ";
					$query = $this->db->query($sql);
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
						
					$query = $this->db->query($sql);
										
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
						
					$vpos[] = array("mood"=>$val["vpos"],"per"=>($mood_total == 0) ? "100" : ($val["vpos"] * 100) / $mood_total);
				
					$pos[] = array("mood"=>$val["pos"],"per"=>($mood_total == 0) ? "100" : ($val["pos"] * 100) / $mood_total);
				
					$neu[] = array("mood"=>$val["neu"],"per"=>($mood_total == 0) ? "100" : ($val["neu"] * 100) / $mood_total);
					
					$neg[] = array("mood"=>$val["neg"],"per"=>($mood_total == 0) ? "100" : ($val["neg"] * 100) / $mood_total);
		
					$vneg[] = array("mood"=>$val["vneg"],"per"=>($mood_total == 0) ? "100" : ($val["vneg"] * 100) / $mood_total);
					
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
	
	
	public function mood_cc_in_each($month,$year,$client_id){
		$report_name = "mood_cc_in_overall";
		$report_title = 'Mood (Sentiment) compared to competitors in each subtopics/factors';
		
		$subject_id = 140;
		$subject_name = "Samsung Smart TV";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		$compare = array();	
		if($subject_id != NULL){
			array_push($compare,array("subject_id"=>$subject_id,"subject_name"=>$subject_name));
			$sql = "SELECT 	id,subject   
				FROM 	subject 
				WHERE 	parent_id = ".$subject_id." 
					AND matching_status = 'update' ";
					
			$query = $this->db->query($sql);
						
			foreach($query->result() as $row){
				array_push($compare,array("subject_id"=>$row->id,"subject_name"=>$row->subject));
			}
		}
		
		$factors = array();
		
		foreach($compare as $val_parent){
			
			$sql = "SELECT 	id,subject 
				FROM 	subject 
				WHERE 	subject LIKE '".$val_parent["subject_name"]."%'
					AND id != ".$val_parent["subject_id"]." 
					AND matching_status = 'update' ";
					
			$query = $this->db->query($sql);
			
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
					ORDER BY post_date ";
				
				$query = $this->db->query($sql);
									
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
				
				
				//echo "SSS<br/>";
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
				
				$pos_vpos[] = array("mood"=>$pos_vpos_data,"per"=>(($mood_total == 0) ? "100" : ($pos_vpos_data * 100) / $mood_total));
			
				$neu[] = array("mood"=>$val_mood["neu"],"per"=>(($mood_total == 0) ? "100" : ($val_mood["neu"] * 100) / $mood_total));
				
				$neg_vneg_data = $val_mood["vneg"] + $val_mood["neg"];
				$neg_vneg[] = array("mood"=>$neg_vneg_data,"per"=>(($mood_total == 0) ? "100" : ($neg_vneg_data * 100) / $mood_total));
				
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

		$data["factors"] = $factors;
		
		return $data;
		
	}
	
	public function top_influencers($month,$year,$client_id){
		$report_name = "top_influencers";
		$report_title = "Top Influencers (Including Seedings)";
		
		$data["report_name"] = $report_name;
		$data["report_title"] = $report_title;
		
		
		$sql = "SELECT 	id,subject
			FROM 	subject
			WHERE 	client_id = $client_id AND cate_id = 7 ";
			
		$query = $this->db->query();
		
		
		/*
		$sql = "select id,username, s98, s103 from author a

		left join
		(select p.author_id, count(p.id) as s98 from post p, matchs m, subject s, author a
		where p.id = m.post_id
		and s.id = m.subject_id
		and a.id = p.author_id
		AND p.type != 'tweet'
		AND p.type != 'retweet'
		AND p.type != 'fb_post'
		AND p.type != 'fb_comment'
		AND subject_id = 98
		AND month(p.post_date) = 3
		AND year(p.post_date) = 2012
		GROUP BY p.author_id
		order by s98 desc) s_98 on s_98.author_id = a.id
		
		left join
		(select p.author_id, count(p.id) as s103 from post p, matchs m, subject s, author a
		where p.id = m.post_id
		and s.id = m.subject_id
		and a.id = p.author_id
		AND p.type != 'tweet'
		AND p.type != 'retweet'
		AND p.type != 'fb_post'
		AND p.type != 'fb_comment'
		AND subject_id = 103
		AND month(p.post_date) = 3
		AND year(p.post_date) = 2012
		GROUP BY p.author_id
		order by s103 desc) s_103 on s_103.author_id = a.id
		
		where s98 != 'null'
		or s103 != 'null'";
		*/
	}
		
}
?>