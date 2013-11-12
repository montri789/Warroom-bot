<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subject_model extends CI_Model {
	
	var $id;
	var $client_id;
	var $subject;
	var $query;
	var $latest_update;
	var $matching_status;
	var $latest_matching;
	var $to;
	var $bot_id;
	var $run_matching;
	
	function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	function init($id=null)
	{
		$this->id = null;
		$this->client_id = null;
		$this->subject = null;
		$this->query = null;
		$this->latest_update  = null;
		$this->matching_status = 'queue';
		$this->latest_matching  = null;
		$this->to = '2012-01-01';
		$this->bot_id = 0;
		$this->run_matching = '2012-01-01';
		
		if($id!=null)
		{
			$query = $this->db->get_where('subject',array('id'=>$id));
			
			if($query->num_rows())
			{
				$this->id = $query->row()->id;
				$this->client_id = $query->row()->client_id;
				$this->subject = $query->row()->subject;
				$this->query = $query->row()->query;
				$this->latest_update  = $query->row()->latest_update;
				$this->matching_status = $query->row()->matching_status;
				$this->latest_matching  = $query->row()->latest_matching;
				$this->to = $query->row()->to;
				$this->bot_id = $query->row()->bot_id;
				$this->run_matching = $query->row()->run_matching;
			}
			return $this;
		}
	}
	
	function insert()
	{
		$this->db->insert('subject',$this);
		return $this->db->insert_id();
	}
	
	function update()
	{
		$res = $this->db->update('subject',$this,array('id'=>$this->id));
		return $res;
	}
	
	function delete()
	{
		$res = $this->db->delete('subject',array('id'=>$this->id));
		return $res;
	}
}
?>