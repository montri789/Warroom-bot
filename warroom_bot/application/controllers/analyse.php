<?PHP
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Analyse extends CI_Controller{
	
	public function match(){

		//$client_id = 1;
		
		$sql = "SELECT 	* 
				FROM 	subject  ";
				//WHERE 	client_id = $client_id ";
				
		$query = $this->db->query($sql);
		
		foreach($query->result() as $row){
			
			//----------------------------------------------------------
			$sql_match = "SELECT post_id FROM matchs WHERE subject_id = ".$row->id." ";
			
			$query_match = $this->db->query($sql_match);
			$post_id = "";
			if($query_match->num_rows() > 0){
				$post =  array();
				foreach($query_match->result() as $post_id_row){
					$post[] = $post_id_row->post_id;
				}
				$post_id = implode(",",$post);
			}
			//----------------------------------------------------------

			$in = $row->inclusive;
			$ex = $row->exclusive;	
			
			$in_c = explode(",",$in);
			$ex_c = explode(",",$ex);
			
			$in_sql = "";
			$ex_sql = "";
							
			foreach($in_c as $val){
				if(!empty($val)){
					if(strpos($val,"+")){
						$in_replace = str_replace("+","%' AND body LIKE '%",$val);
						$in_sql .= (!empty($in_sql) ? 'OR' : '' )." (body LIKE '%".$in_replace."%') ";
					}else{
						$in_sql .= (!empty($in_sql) ? 'OR' : '' )." body LIKE '%".$val."%' ";
					}
				}
			}	
			
			$ex_replace = str_replace(",","%' AND body NOT LIKE '%",$ex);
			$ex_sql =  " body NOT LIKE '%".$ex_replace."%' ";
			
			$sql =  "SELECT id,body 
					 FROM post 
					 WHERE (".$in_sql . ") ";
					 
			$sql .= (!empty($ex)) ? " AND (".$ex_sql.") " : "";
			$sql .= (!empty($post_id)) ? " AND id NOT IN(".$post_id.") " : "";		 
			
			
			$query2 = $this->db->query($sql);
			
			foreach($query2->result() as $row2){
				$data = array("post_id"=>$row2->id,
							  "subject_id"=>$row->id);
				$this->db->insert("matchs",$data);
			}	
		}
	}
	
	function match_subject_post()
	{
		$this->db->order_by('latest_update');
		$query = $this->db->get('subject');
		log_message('info','ANALYSE : found subjects : '.$query->num_rows());
		foreach($query->result() as $row)
		{
			$subject_id = $row->id;
			
			// check if subject is newer, then clear match
			$option = array('subject_id' => $subject_id);
			$this->db->order_by('matching_date','desc');
			$query = $this->db->get_where('matchs',$option,1,0);

			if($query->num_rows() > 0)
			{
				$last_matching = strtotime($query->row()->matching_date);
				$latest_update_subject = strtotime($this->custom_model->get_value('subject','latest_update',$subject_id));

				if($last_matching < $latest_update_subject) 
				{
					log_message('info','clear match : '.$subject_id);
					$this->clear_match($subject_id);
				}
			}
			
			log_message('info','start matching : '.$subject_id);
			$subject = $this->get_subject($subject_id);
			
			$posts = $this->filter('inc',$subject->inc);
			if($posts)
			{
				log_message('info','match inc : '.count($posts));
				foreach($posts as $p)
				{
					$option = array(
						'post_id' => $p->id,
						'subject_id' => $subject->id,
					);
					$this->db->insert('matchs',$option);
				}
			}
		}
		
	}
	
	function update_subject_date()
	{
		$query = $this->db->get('subject');
		
		foreach($query->result() as $row)
		{
			$obj = $row;
			$obj->latest_update = mdate('%Y-%m-%d %H:%i',time());
			$option = array('id'=>$obj->id);
			$this->db->update('subject',$obj,$option);
		}
	}
	
	function filter($type,$keywords)
	{
		foreach($keywords as $k=>$v)
		{
			if($k==0) 
			{
				$this->db->like('title',$v);
				$this->db->or_like('body',$v);
			}
			else
			{
				$this->db->or_like('title',$v);
				$this->db->or_like('body',$v);
			}
		}
		$query = $this->db->get('post');
		
		$posts = array();
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$post = new Post_model();
				$post->init($row->id);
				$posts = $post;
			}
			return $posts;
		}
		else return false;
	}
	
	function get_subject($subject_id)
	{
		$option = array('id'=>$subject_id);
		$query = $this->db->get_where('subject',$option);
		
		$subject->id = $query->row()->id;
		$subject->client_id = $query->row()->client_id;
		$subject->cate_id = $query->row()->cate_id;
		$subject->parent_id = $query->row()->parent_id;
		$subject->latest_update = strtotime($query->row()->latest_update);
		$subject->inc = explode(",",$query->row()->inclusive);
		$subject->exc = explode(",",$query->row()->exclusive);
		
		return $subject;
	}
	
	function clear_match($subject_id)
	{
		$this->db->delete('matchs',array('subject_id'=>$subject_id));
	}

}