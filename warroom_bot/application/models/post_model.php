<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Post_model extends CI_Model {
	
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
	{
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
		
			$config['hostname'] = "27.254.81.15";
			$config['username'] = "root";
			$config['password'] = "Cg3qkJsV";
						
			$config['database'] = "spider";
			$config['dbdriver'] = "mysql";
			$config['dbprefix'] = "";
			$config['pconnect'] = FALSE;
			$config['db_debug'] = TRUE;
			$config['cache_on'] = FALSE;
			$config['cachedir'] = "";
			$config['char_set'] = "utf8";
			$config['dbcollat'] = "utf8_general_ci";
			
			$kpiology = $this->load->database($config,true);
		
		
			$query = $kpiology->get_where('post',array('id'=>$id));
			
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
	function get_post_website($post_id){
	  
		$config['hostname'] = "27.254.81.15";
		$config['username'] = "root";
		$config['password'] = "Cg3qkJsV";
					
		$config['database'] = "spider";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$kpiology = $this->load->database($config,true);
		
		//$sql = "SELECT * FROM post WHERE id = ".$post_id." ";
		
		$sql ="SELECT post.id,post_date,title,`body`,`type`, 
				author.username as 'author',domain.id as 'website_id',
				domain.name as 'website_name',domain.root_url,page.url,page.id as 'page_id' 
				FROM post,author,page,domain 
				WHERE post.id = ".$post_id."  
				AND post.author_id = author.id 
				AND post.page_id = page.id 
				AND page.domain_id = domain.id ";
		
		$query = $kpiology->query($sql);
		if($query->num_rows() == 0){
				$sql  ="SELECT post.id,post_date,title,`body`,`type`, 
						author.username as 'author_name',post.facebook_id,post.tweet_id    
						FROM post,author 
						WHERE post.id = $post_id   
						AND post.author_id = author.id ";
				$query = $kpiology->query($sql);
		}
		
		
		
		return $query->row();	
	}
	function getRetweeted($tweet_id){
	
	
		$config['hostname'] = "27.254.81.15";
		$config['username'] = "root";
		$config['password'] = "Cg3qkJsV";
					
		$config['database'] = "spider";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$kpiology = $this->load->database($config,true);
		
		$sql = "SELECT retweet_count,retweeted_status_id FROM retweeted WHERE tweet_id = '$tweet_id' ";
		$query = $kpiology->query($sql);
		if($query->num_rows() > 0){
			$res = $query->row();
			return $res;
		}else{
			return null;
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
}