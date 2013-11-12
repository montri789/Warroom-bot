<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Post_model2 extends CI_Model {
	
	var $id;
	var $post_date;
	var $parse_date;
	var $page_id;
	var $type;
	var $title;
	var $body;
	var $author;
	var $website_id;
	var $website_name;
	var $url;
	
	function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
	
	function init($id=null)
	{    echo "sdsdsd";
		$this->id = null;
		$this->post_date = null;
		$this->parse_date = null;
		$this->page_id = null;
		$this->type = null;
		$this->title = null;
		$this->body = null;
		$this->author = null;
		$this->website_id = null;
		$this->website_name = null;
		$this->url = null;
		
		if($id!=null)
		{
			$query = $this->db->get_where('post',array('id'=>$id));
			
			if($query->num_rows())
			{
				$this->id = $query->row()->id;
				$this->post_date = $query->row()->post_date;
				$this->parse_date = $query->row()->parse_date;
				$this->page_id = $query->row()->page_id;
				$this->type = $query->row()->type;
				$this->title = $query->row()->title;
				$this->body = $query->row()->body;
				$this->author = $query->row()->author;
				$this->website_id = $query->row()->website_id;
				$this->website_name = $query->row()->website_name;
				$this->url = $query->row()->url;
				
			}
			return $this;
		}
	}
	
	function insert()
	{
		$this->db->insert('post',$this);
		log_message('info',"post model : inserted.");
		return $this->db->insert_id();
	}
	

	
	function update()
	{
		$res = $this->db->update('post',$this,array('id'=>$this->id));
		log_message('info',"post model [".$this->id."]: updated.");
		return $res;
	}
	
	function delete()
	{
		$res = $this->db->delete('post',array('id'=>$this->id));
		log_message('info',"post model [".$this->id."]: deleted.");
		return $res;
	}
	function get_page()
	{
		$page = new Page_model();
		$page->init($this->page_id);
		return $page;
	}
	
	function validate(){
		
		$pattern = '/(\d{4})-(\d{1,2})-(\d{1,2})[ (\d{2}):(\d{2})[:(\d{2})]*]*/';
		
		
		if(preg_match($pattern,$this->post_date)){
			return true;
		}
		else{
			return false;
		}

	}
}