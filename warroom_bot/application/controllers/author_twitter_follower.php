<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Author_twitter_follower extends CI_Controller {
	
	public function index(){
		$sql = "SELECT author_id,tweet_update_date,follower
			FROM author_twitter
			WHERE author_id IN(396409,397575,397952,397959,456393,466472,482695,624750)";// ORDER BY author_id LIMIT 10 ";
			//Acer,Sony,Intel,Dell,Asus
		
		$query = $this->db->query($sql);
		foreach($query->result() as $val){
			$sql = "SELECT author_id FROM author_twitter_follower 
				WHERE author_id = ".$val->author_id." AND date = DATE('".$val->tweet_update_date."') ";
			$query = $this->db->query($sql);
			
			if($query->num_rows() > 0){
				$sql = "UPDATE author_twitter_follower SET follower=".$val->follower."
					WHERE author_id = ".$val->author_id." AND date = DATE('".$val->tweet_update_date."') ";
				$query = $this->db->query($sql);
			}else{
				$sql = "INSERT INTO author_twitter_follower(author_id,date,follower)
					VALUES('".$val->author_id."',DATE('".$val->tweet_update_date."'),".$val->follower.")";
				$query = $this->db->query($sql);
			}
		}
	}
}
?>