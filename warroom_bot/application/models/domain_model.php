<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Domain_model extends CI_Model {
	
	var $id;
	var $name;
	var $helper_name;
	var $url;
	var $root_url;
	var $group_pattern;
	var $child_pattern;
	var $sub_comment_pattern;
	var $latest_update;
	var $status;
	var $domain_type_id;
	var $domain_cate_id;
	var $country;
	
	function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	function init($id=null)
	{
		$this->id = null;
		$this->name = null;
		$this->helper_name = null;
		$this->url = null;
		$this->root_url = null;
		$this->group_pattern = null;
		$this->child_pattern = null;
		$this->sub_comment_pattern = null;
		$this->latest_update = null;
		$this->status = null;
		$this->domain_type_id = 0;
		$this->domain_cate_id = 0;
		$this->country = null;
		
		if($id!=null)
		{
			$query = $this->db->get_where('domain',array('id'=>$id));
			
			if($query->num_rows())
			{
				$this->id = $query->row()->id;
				$this->name = $query->row()->name;
				$this->helper_name = $query->row()->helper_name;
				$this->url = $query->row()->url;
				$this->root_url = $query->row()->root_url;
				$this->group_pattern = $query->row()->group_pattern;
				$this->child_pattern = $query->row()->child_pattern;
				$this->sub_comment_pattern = $query->row()->sub_comment_pattern;
				$this->latest_update = $query->row()->latest_update;
				$this->status = $query->row()->status;
				$this->domain_type_id = $query->row()->domain_type_id;
				$this->domain_cate_id = $query->row()->domain_cate_id;
				$this->country = $query->row()->country;
			}
		}
		return $this;
	}
	
	function insert()
	{
		$this->db->insert('domain', $this);
		return $this->db->insert_id();
	}
	
	function update()
	{
		$res = $this->db->update('domain',$this,array('id'=>$this->id));
		return $res;
	}
	
	function delete()
	{
		$res = $this->db->delete('domain',array('id'=>$this->id));
		return $res;
	}
	
	function list_domain($status = 'idle')
	{
		$query = $this->db->get_where('domain',array('status'=>$status));
		$list = array();
		
		if($query->num_rows())
		{
			foreach($query->result() as $row)
			{
				$d = new Domain_model();
				$d->init($row->id);
				$list[] = $d;
			}
		}
		return $list;
	}
	
	function get_root_page()
	{
		$param = array(
			'domain_id' => $this->id,
			'outdate' => 0,
			'parent_page_id' => 0
		);
		$query = $this->db->get_where('page', $param);
		
		if($query->num_rows())
		{
			$page = new Page_model();
			$page->init($query->row()->id);
			
			return $page;
		}
		
		return false;
	}
}
