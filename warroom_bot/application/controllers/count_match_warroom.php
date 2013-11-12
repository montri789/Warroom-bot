<?php  
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Count_match_warroom extends CI_Controller {

	function Index()
	{
		
		$sqlc ="SELECT * FROM clients WHERE status='enabled' ORDER BY id ASC";
			
		echo 'SQL Query ...'.PHP_EOL;	
	    $queryc = $this->db->query($sqlc);			
		foreach($queryc->result() as $valc){
			 
			$sql ="SELECT COUNT(1) AS 'count_match',DATE(post_date) AS 'post_date1'
					FROM message_client_".$valc->id." 
					WHERE DATE(post_date) BETWEEN DATE_SUB(CURDATE(),INTERVAL 15 DAY) AND CURDATE()
					GROUP BY DATE(post_date)
					ORDER BY DATE(post_date) ASC";
				
			//echo 'SQL Query ...'.PHP_EOL;	
			$query = $this->db->query($sql);	
			
			$chart_data = array();
			foreach($query->result() as $val){
					
				$data = array();			
			
				$data["client_id"] 		= $valc->id;
				$data["post_date"] 		= $val->post_date1;
				$data["match_amt"] 		= $val->count_match;
				$data["update_date"] 	= date("Y-m-d H:i:s");
		
		
				$query_chk = $this->db->get_where('status_match_warroom', array('client_id'=> $data["client_id"],'post_date'=> $data["post_date"]));
				
				echo "Found to date :".$query_chk->num_rows().PHP_EOL;
				if($query_chk->num_rows() > 0){
					echo '==Update ='.$val->post_date1." ID :".$valc->id."".PHP_EOL;					
					$this->db->update('status_match_warroom', $data, array('client_id'=> $data["client_id"],'post_date'=> $data["post_date"]));
				}
				else{
					echo 'Insert ='.$data["post_date"]." ID :".$valc->id."".PHP_EOL;
					$insert_query = $this->db->insert_string("status_match_warroom",$data);
					$this->db->query($insert_query);
					//$insert_id =$this->db->insert_id();
				}
			}
		}
	}
}