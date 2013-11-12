<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Match_model extends CI_Model {
	
	var $post_id;
	var $subject_id;
	var $sentiment;
	var $matching_date;
	var $user_correct;
	var $user_correct_date;
	var $by_user;
	var $admin_correct;
	var $admin_correct_date;
	var $system_correct;
	var $system_correct_date;

	function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	function init($post_id=null,$subject_id=null)
	{
		$this->post_id = null;
		$this->subject_id = null;
		$this->sentiment = 0;
		$this->matching_date = null;
		$this->user_correct = null;
		$this->user_correct_date = null;
		$this->by_user = null;
		$this->admin_correct = null;
		$this->admin_correct_date = null;
		$this->system_correct = null;
		$this->system_correct_date = null;
		
		
		if($post_id != null && $subject_id != null)
		{
			$query = $this->db->get_where('matchs',array('post_id'=>$post_id,'subject_id'=>$subject_id));
			
			if($query->num_rows())
			{
				$this->post_id = $query->row()->post_id;
				$this->subject_id = $query->row()->subject_id;
				$this->sentiment = $query->row()->sentiment;
				$this->matching_date = $query->row()->matching_date;
				$this->user_correct = $query->row()->user_correct;
				$this->user_correct_date = $query->row()->user_correct_date;
				$this->by_user = $query->row()->by_user;
				$this->admin_correct = $query->row()->admin_correct;
				$this->admin_correct_date = $query->row()->admin_correct_date;
				$this->system_correct = $query->row()->system_correct;
				$this->system_correct_date = $query->row()->system_correct_date;
			}
			return $this;
		}
	}
	
	function insert()
	{
		$this->db->insert('matchs', $this);
		log_message('info',"matchs_model : inserted");
		return $this->db->insert_id();
	}
	
	function update()
	{
		$res = $this->db->update('matchs',$this,array('post_id'=>$this->post_id,'subject_id'=>$this->subject_id));
		log_message('info',"matchs_model : updated");
		return $res;
	}
	
	function delete()
	{
		$res = $this->db->delete('page',array('post_id'=>$this->post_id,'subject_id'=>$this->subject_id));
		log_message('info',"matchs_model : updated");
		return $res;
	}
	
}
