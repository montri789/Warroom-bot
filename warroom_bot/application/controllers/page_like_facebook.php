<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page_like_facebook extends CI_Controller {
	
	public function index(){
		$sql = "SELECT facebook_id,likes,likes_update_date FROM page_facebook";
		//WHERE facebook_id IN(1,2,3,...)
		$query = $this->db->query($sql);
		foreach($query->result() as $val){
			$sql = "SELECT facebook_id FROM page_like_facebook
				WHERE facebook_id = ".$val->facebook_id." AND date = DATE('".$val->likes_update_date."') ";
			$query = $this->db->query($sql);
			
			if($query->num_rows() > 0){	
				$sql = "UPDATE page_like_facebook SET likes=".$val->likes."
				WHERE facebook_id = ".$val->facebook_id." AND date = DATE('".$val->likes_update_date."') ";
				$query = $this->db->query($sql);
			}else{
				$sql = "INSERT INTO page_like_facebook(facebook_id,date,likes)
					VALUES('".$val->facebook_id."',DATE('".$val->likes_update_date."'),".$val->likes.") ";
				$query = $this->db->query($sql);
			}
		}
	}
}
?>