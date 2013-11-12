<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Page_model extends CI_Model {
	
	var $id;
	var $outdate;
	var $new_id;
	var $domain_id;
	var $parent_page_id;
	var $url;
	var $parse_child;
	var $parse_post;
	var $latest_fetch;
	var $insert_date;
	var $size;
	var $filename;
	var $active_score;
	var $view;
	var $sub_comment;
	
	function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
	function init($id=null)
	{
		$this->id=null;
		$this->outdate=0;
		$this->new_id=null;
		$this->root_page=0;
		$this->domain_id=null;
		$this->parent_page_id=null;
		$this->url=null;
		$this->parse_child=0;
		$this->parse_post=0;
		$this->latest_fetch=null;
		$this->insert_date=null;
		$this->size=null;
		$this->filename=null;
		$this->active_score=0;
		$this->view=0;
		$this->sub_comment=0;
		
		if($id!=null)
		{
			$query = $this->db->get_where('page',array('id'=>$id));
			
			if($query->num_rows())
			{
				$this->id=$query->row()->id;
				$this->outdate=$query->row()->outdate;
				$this->new_id=$query->row()->new_id;
				$this->root_page=$query->row()->root_page;
				$this->domain_id=$query->row()->domain_id;
				$this->parent_page_id=$query->row()->parent_page_id;
				$this->url=$query->row()->url;
				$this->parse_child=$query->row()->parse_child;
				$this->parse_post=$query->row()->parse_post;
				$this->latest_fetch=$query->row()->latest_fetch;
				$this->insert_date=$query->row()->insert_date;
				$this->size=$query->row()->size;
				$this->filename=$query->row()->filename;
				$this->active_score=$query->row()->active_score;
				$this->view=$query->row()->view;
				$this->sub_comment=$query->row()->sub_comment;
			}
			return $this;
		}
	}
	
	function insert()
	{
		$this->db->insert('page', $this);
		log_message('info',"page_model : inserted");
		return $this->db->insert_id();
	}
	
	function update()
	{
		$res = $this->db->update('page',$this,array('id'=>$this->id));
		log_message('info',"page_model : updated");
		return $res;
	}
	
	function update_same_page($fetch,$write_file = false)
	{
		log_message('info',"page_model : update_same_page : ".$this->id);
		$this->latest_fetch = date('Y-m-d H:i:s');
		$this->size = $fetch['meta']['size_download'];
		$this->update();
		
		return true;
	}
	
	function update_new_page($fetch,$write_file = false)
	{
		log_message('info',"page_model : update_new_page : ".$this->id);
		
		$new = new Page_model();
		$new->init();
		$new->domain_id = $this->domain_id;
		$new->outdate = 0;
		$new->parent_page_id = $this->id;
		$new->url = $this->url;
		$new->parse_child = 0;
		$new->latest_fetch = mdate('%Y-%m-%d %H:%i',time());
		$new->insert_date = mdate('%Y-%m-%d %H:%i',time());
		$new->size = $fetch['meta']['size_download'];
		$new_id = $new->insert();
		
		$this->outdate = 1;
		$this->new_id = $new_id;
		$this->update();
		
		if($write_file)
		{
			$new->init($new_id);
			//filename = latest_fetch + id
			$filename = mdate('%Y%m%d%H%i',time())."_".$new->id;
			$path = $this->config->item('fetch_file_path');

			//file_folder = 8 digits Year+Month+Day
			$file_folder = mdate('%Y%m%d',time()).'/';

			//Create folder if not exists
			$folder = substr($file_folder,0,-1);
			//echo $path.$floder;
			if(!file_exists($path.$folder)) mkdir($path.$folder);
			

			//write file
			if (!write_file($path.$file_folder.$filename, $fetch['content']))
			{
				log_message('info',"page_model : write_file error");
				echo PHP_EOL."page_model : write_file error";
				unset($new);

				return false;
			}
			else
			{
				log_message('info',"page_model : write_file success : ".$filename);
				$new->filename = $filename;
				$new->update();
				unset($new);

				return true;
			}
		}
		
		return true;
	}
	
	function delete()
	{
		$res = $this->db->delete('page',array('id'=>$this->id));
		log_message('info',"page_model : deleted : ".$this->id);
		return $res;
	}
	
	function purge()
	{
		log_message('info',"page_model : deleting files : ".$this->filename);
		echo PHP_EOL."page_model : deleting : ".$this->filename;
		
		if($this->filename == null)
		{
			log_message('info','page_model : deleting failed : file is null.');
			echo PHP_EOL.'page_model : deleting failed : file is null.';
		}
		else
		{
			$path = $this->config->item('fetch_file_path');
			
			// folder name = 8digit Year+Month+Day of filename
			$folder = substr($this->filename,0,8);
			
			$d = unlink($path.$folder.'/'.$this->filename);
			echo PHP_EOL.'page_model : deleting : '.$path.$folder.'/'.$this->filename;
			var_dump($d);
			
			if($d)
			{
				$this->filename = null;
				$this->size = 0;
				$this->insert_date = '0000-00-00 00:00:00';
				$this->update();
			}
		}
	}
	
	function get_domain_url()
	{
		return $this->custom_model->get_value('domain','root_url',$this->domain_id);
	}
	
	function fetch($root_url = NULL)
	{
		$root = $root_url;
		$trim_root = substr($root,0,-1);
		
		$url = $this->url;
		//echo ' =>'.$trim_root.''.$url;
		
		// if url start with "?", get parent page (which is not also a sub_comment page) and entail
		if($url[0] == "?")
		{
			$parent = new Page_model();
			$parent->init($this->parent_page_id);
			while($parent->sub_comment) $parent->init($parent->parent_page_id);
			
			$str = explode("?",$parent->url);
			$url = $str[0].$url;
			unset($parent);
		}
		
		// if url start with ".", trim it out
		if($url[0] == ".")
		{
			$url = substr($url,1);
		}
		
		log_message('info',"page_model : fetching : ".$trim_root.$this->url);
		
		$options = array( 
		        CURLOPT_RETURNTRANSFER => true,         // return web page 
		        CURLOPT_HEADER         => false,        // don't return headers 
		        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
		        CURLOPT_ENCODING       => "",           // handle all encodings 
		        CURLOPT_USERAGENT      => "Googlebot",     // who am i 
		        CURLOPT_AUTOREFERER    => true,         // set referer on redirect 
		        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect 
		        CURLOPT_TIMEOUT        => 120,          // timeout on response 
		        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
		        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl 
		        CURLOPT_SSL_VERIFYPEER => false,        // 
		        CURLOPT_VERBOSE        => false
		    ); 

		$ch      = curl_init($trim_root.$url); 
		curl_setopt_array($ch,$options); 
		$content = curl_exec($ch); 
		$err     = curl_errno($ch); 
		$errmsg  = curl_error($ch) ; 
		$header  = curl_getinfo($ch); 
		curl_close($ch);
		
		$size = $header["size_download"]/1024;
		log_message('info',"page_model : fetched : ".$size." KB");
		
		$fetch['content'] = $content;
		$fetch['meta'] = $header;
		$fetch['size'] = $size;
		
		//echo ' size='.$size;
		if($header["http_code"]!=200) return null;
		else return $fetch;
	}
	
	function read_file($debug=false)
	{
		log_message('info',"page_model : read_file : ".$this->id);
		
		$path = $this->config->item('fetch_file_path');
		
		// folder name = 8digit Year+Month+Day of filename
		$folder = substr($this->filename,0,8);
		
		if($debug) echo 'read:'.$path.$folder.'/'.$this->filename;
		$fetch = read_file($path.$folder.'/'.$this->filename);	
		return $fetch;
	}
	
	function update_child_from_fetch_test($fetch){
		
		// Reset PHP Timeout to 5min
		set_time_limit(60*5);

		$exist_url = array();
		$sql = "SELECT url FROM page WHERE domain_id = ".$this->domain_id."  ";
		$query = $this->db->query($sql);
		//$exist_url = $query->result_array();
		
		foreach($query->result_array() as $row){
			$exist_url[] = $row["url"];
		}

		$query->free_result();
			
		if($fetch == null){	
			echo "(-err)";	
			return false;
		}
		
		$html = str_get_html($fetch['content']);
		$links = $html->find('a');
		$html->clear();
		unset($html);
		
		$child = 0;
		
		$pattern = array();
		$sql = "SELECT root_url,child_pattern,sub_comment_pattern,group_pattern FROM domain WHERE id = ".$this->domain_id;
		$query = $this->db->query($sql);
		$result = $query->row_array();
		$query->free_result();
		
		$pattern['child'] = $result['child_pattern'];
		$pattern['sub_comment'] = $result['sub_comment_pattern'];
		$pattern['group_pattern'] = $result['group_pattern'];
			
		foreach($links as $element){
			$href = html_entity_decode($element->href);		

			if(strpos($href,"#") > 0) $href = substr($href,0,strpos($href,"#"));
			
			$root_url = $result["root_url"];
			if(is_int(strpos($href,$root_url))){
				$href = str_replace($root_url,'/',$href);
			}
			
			if((mb_substr($href,0,1) != '/') && (mb_substr($href,0,1) != '.')) $href = '/'.$href;
			
			echo PHP_EOL.$href;

			if($this->domain_id == 35){
				$href = explode("-",$href);
				$href = $href[0];
			}
			
			$res = $this->check_url($href,$pattern);
			if ($res!=false){
				echo '('.$res;
				$res = in_array($href,$exist_url);
				
				if(!$res){
					$exist_url[] = $href; 
					
					log_message('info','page_model : update_from_file : new '.$res.':'.$href);
							
					$p = new Page_model();
					$p->init();
					$p->outdate=0;
					$p->domain_id = $this->domain_id;
					$p->parent_page_id = $this->id;
					$p->url = $href;
					$p->parse_child = 0;
					if($res == "sub_comment") $p->sub_comment = 1;
					
					$p->insert_date = date('Y-m-d H:i:s');

					$p->insert();
					
					unset($p);
					$child++;
				}else{
					echo "-skip";
				}
				echo ')';
			}
		}
		
		unset($exist_url);
		$this->parse_child = 1;
		$this->update();
	}
	
	
	function update_child_from_fetch($fetch)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		// Reset PHP Timeout to 5min
		set_time_limit(60*5);
		
		//$fetch = $this->read_file();
	
		// Load page url list
		$exist_url = array();
		$this->db->select('url');
		$query = $this->db->get_where('page',array('domain_id'=>$this->domain_id)); //parent_page_id != 0   MOTE 26-10-2555
		
		foreach($query->result() as $row) { $exist_url[] = $row->url; }
		
		// Load page url from memcache
		$key_pattern = '/\Apage\-'.$this->domain_id.'\-\d+.\d+\z/';
		
		$allSlabs = $memcache->getExtendedStats('slabs');
		foreach($allSlabs as $server => $slabs)
		{  
			foreach($slabs as $slabId => $slabMeta)
			{
				if (!is_numeric($slabId)){continue;}

				$cdump = $memcache->getExtendedStats('cachedump',(int)$slabId,0);
				//echo count($cdump);

				foreach($cdump as $keys => $arrVal) 
				{
					if (!is_array($arrVal)) continue;

					//echo count($arrVal);

					foreach($arrVal as $k => $v) 
					{                   
//						echo $k .' - '.date('H:i d.m.Y',$v[1]).' , ';
						$res = preg_match($key_pattern,$k);
						if(!$res) continue;
						$obj = $memcache->get($k);
						if(!$obj) continue;
						$exist_url[] = $obj->url;
					}
				}
			}
		}
		
		if($fetch == null)
		{	
			echo "(-err)";	
			return false;
		}
				
		$html = str_get_html($fetch['content']);
		$links = $html->find('a');
		$html->clear();
		unset($html);
		
		$child = 0;
		
		$pattern = array();
		$pattern['child'] = $this->custom_model->get_value('domain','child_pattern',$this->domain_id);
		$pattern['sub_comment'] = $this->custom_model->get_value('domain','sub_comment_pattern',$this->domain_id);
		$pattern['group_pattern'] = $this->custom_model->get_value('domain','group_pattern',$this->domain_id);
		
		
		foreach($links as $element)
		{
			$href = html_entity_decode($element->href);
//			$href = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$href);
			
			if($this->domain_id == 85)
			{
				$str = explode('/hometh',$href);
				$href=$str[count($str)-1];
				$str = explode('&key',$href);
				$href=$str[0];
			}
			
			if($this->domain_id == 207){
				$str = explode('&',$href);
				$href=$str[0];
			}
			if($this->domain_id == 33){
				$str = explode('-',$href);
				$href=$str[0];
			}
			
			// search "#" and truncate from url
			if(strpos($href,"#") > 0) $href = substr($href,0,strpos($href,"#"));
			
			// search root_url and truncate
			$root_url = $this->custom_model->get_value('domain','root_url',$this->domain_id);
			if(is_int(strpos($href,$root_url)))
			{
				$href = str_replace($root_url,'/',$href);
			}
			
			// if href not start with '/' or '.' add '/'
			if((mb_substr($href,0,1) != '/') && (mb_substr($href,0,1) != '.')) $href = '/'.$href;
			
			echo PHP_EOL.$href;
			
			$res = $this->check_url($href,$pattern);
			if ($res!=false) 
			{
				echo '('.$res;
				$res = in_array($href,$exist_url);
				
//				$url_id = $this->is_exist($href,$this->domain_id);
//				log_message('info','page_model : found child : '.$url_id);
//				if($url_id == 0)
				if(!$res)
				{
					
					$exist_url[] = $href; //
					
					log_message('info','page_model : update_from_file : new '.$res.':'.$href);
							
					$p = new Page_model();
					$p->init();
					$p->outdate=0;
					$p->domain_id = $this->domain_id;
					$p->parent_page_id = $this->id;
					$p->url = $href;
					$p->parse_child = 0;
					if($res == "sub_comment") $p->sub_comment = 1;
					$p->insert_date = mdate('%Y-%m-%d %h:%i',time());
					//$p->insert();
					
					$key = 'page-'.$this->domain_id.'-'.microtime(true);
					$memcache->add($key, $p, false, 12*60*60) or die ("Failed to save OBJECT at the server");
					
					unset($p);
					$child++;
				}
				else
				{
					echo "-skip";
				}
				echo ')';
			}
		}
		
		//log_message('info',"page_model : update_child_from_file : new child:".$child);
		
		$memcache->close();
		unset($exist_url);
		$this->parse_child = 1;
		$this->update();
		
	}
	
	function update_child_from_file()
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		// Reset PHP Timeout to 5min
		set_time_limit(60*5);
		
		log_message('info',"page_model : update_child_from_file : ".$this->id." loaded");
		
		$fetch = $this->read_file();
	

		// Load page url list
		$exist_url = array();
		$this->db->select('url');
		$query = $this->db->get_where('page',array('domain_id'=>$this->domain_id));
		foreach($query->result() as $row) {$exist_url[] = $row->url;}
		
		
		if($fetch == false)
		
		{
			
			log_message('info',"page_model : ".$this->id." : read file error");
			echo "(-err)";
			
			return false;
		}
		
		
		$html = str_get_html($fetch);
		$links = $html->find('a');
		$html->clear();
		unset($html);
		
		$child = 0;
		
		$pattern = array();
		$pattern['child'] = $this->custom_model->get_value('domain','child_pattern',$this->domain_id);
		$pattern['sub_comment'] = $this->custom_model->get_value('domain','sub_comment_pattern',$this->domain_id);
		$pattern['group_pattern'] = $this->custom_model->get_value('domain','group_pattern',$this->domain_id);
		
		foreach($links as $element)
		{
			$href = html_entity_decode($element->href);
//			$href = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$href);
			
			if($this->domain_id == 85)
			{
				$str = explode('/hometh',$href);
				$href=$str[count($str)-1];
				$str = explode('&key',$href);
				$href=$str[0];
			}
			
			if($this->domain_id == 207){
				$str = explode('&',$href);
				$href=$str[0];
			}
			if($this->domain_id == 33){
				$str = explode('-',$href);
				$href=$str[0];
			}
			
			// search "#" and truncate from url
			if(strpos($href,"#") > 0) $href = substr($href,0,strpos($href,"#"));
			
			// search root_url and truncate
			$root_url = $this->custom_model->get_value('domain','root_url',$this->domain_id);
			if(is_int(strpos($href,$root_url)))
			{
				$href = str_replace($root_url,'/',$href);
			}
			
			// if href not start with '/' or '.' add '/'
			if((mb_substr($href,0,1) != '/') && (mb_substr($href,0,1) != '.')) $href = '/'.$href;
			
			echo PHP_EOL.$href;
			
			$res = $this->check_url($href,$pattern);
			if ($res!=false) 
			{
				echo '('.$res;
				$res = in_array($href,$exist_url);
//				$url_id = $this->is_exist($href,$this->domain_id);
//				log_message('info','page_model : found child : '.$url_id);
//				if($url_id == 0)
				if(!$res)
				{
					log_message('info','page_model : update_from_file : new '.$res.':'.$href);

					
					$p = new Page_model();
					$p->init();
					$p->outdate=0;
					$p->domain_id = $this->domain_id;
					$p->parent_page_id = $this->id;
					$p->url = $href;
					$p->parse_child = 0;
					if($res == "sub_comment") $p->sub_comment = 1;
					$p->insert_date = mdate('%Y-%m-%d %h:%i',time());
					//$p->insert();
					
					$key = 'page-'.$this->domain_id.'-'.microtime(true);
					$memcache->add($key, $p, false, 12*60*60) or die ("Failed to save OBJECT at the server");
					
					unset($p);
					$child++;
				}
				else
				{
					echo "-skip";
				}
				echo ')';
			}
		}
		
		log_message('info',"page_model : update_child_from_file : new child:".$child);
		
		$memcache->close();
		unset($exist_url);
		$this->parse_child = 1;
		$this->update();
		
	}
	
	function is_exist($url,$domain)
	{
		$options = array (
			'url' => $url,
			'domain_id' => $domain
			);
		$query = $this->db->get_where('page',$options);
		if($query->num_rows() > 0) return $query->row()->id;
		else return 0;
	}
	
	function check_url($url=null,$pattern)
	{
		$child = $pattern['child'];
		$sub_comment = $pattern['sub_comment'];
		$group_pattern = $pattern['group_pattern'];
		
		if (preg_match($child, $url))
		{
			//log_message('info','page_model : got child : '.$url);
			return "child";
		}
		else if($sub_comment!=null && preg_match($sub_comment, $url))
		//else if($sub_comment!=null)
		{
			//log_message('info','page_model : got sub_comment : '.$url);
			return "sub_comment";
		}
		else if($group_pattern!=null && preg_match($group_pattern, $url))
		{
			return "group";
		}
		else
		{
			//log_message('info','page_model : not child : '.$url);
			return false;
		}
	}
	
	function find($options)
	{
		$query = $this->db->get_where('page',$options);
		$pages = array();
		
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$page = new Page_model();
				$page->init($row->id);
				$pages[]= $page;
			}
		}
		
		return $pages;
	}
	
	function less_active()
	{
		if($this->root_page == 1) $root = true;
		else $root = false;
		$score = $this->active_score;
		if(!$root) $this->active_score = $score-1;
//		$this->update();
	}
	
	function more_active()
	{
		$score = $this->active_score;
		$this->active_score = $score+1;
//		$this->update();
	}
	
	function get_posts()
	{
		$posts = array();
		
		// get older posts
		$older = $this->get_older();  
		if($older)
		{ 
			//echo "get older : ".$older->id;
			//echo "<br>";
			$older_posts = $older->get_posts();
			if($older_posts)
			{
				//echo "older posts : ".count($older_posts);
				//echo "<br>";
				foreach($older_posts as $p)
				{
					$posts[] = $p;
				}
			}
		}
		
		
		//else {echo "get older : none";}
		
		//echo "<br>";
		
		
		
		$option = array('page_id'=>$this->id);
		$query = $this->db->get_where('post',$option);

		
		//$sql = "SELECT id FROM post WHERE page_id = ".$this->id;
		//$query = $this->db->query($sql);
		
	
		//echo "current posts : ".$query->num_rows();
		//echo "<br>";
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				//echo "retrieve posts : ".$row->id;
				$post = new Post_model();
				$post->init($row->id);
				$posts[] = $post;
			}
		}
		
		//echo "return posts : ".count($posts);
		return $posts;
		
	}
	
	function get_older()
	{
	  
	
		$option = array('new_id' => $this->id);
		$query = $this->db->get_where('page',$option);
		
		if($query->num_rows() > 0)
		{
		
			$page = new Page_model();
			$page->init($query->row()->id);
			return $page;
		}
		else return false;
		
		
	}
	
	function parse($fetch,$domain)
	{ 
		$helper_name = $domain["helper_name"];
		$result = array('parse_ok'=>false,'posts'=>array());
		
		//if ($this->domain_id == 15 || $this->domain_id == 29 || $this->domain_id == 16 || $this->domain_id == 21 || $this->domain_id == 18 || $this->domain_id == 10 || $this->domain_id == 13 || $this->domain_id == 4 || $this->domain_id == 25 || $this->domain_id == 26 || $this->domain_id == 27 || $this->domain_id == 20 || $this->domain_id == 28 || $this->domain_id >= 30 || $this->domain_id == 1 || $this->domain_id == 3 || $this->domain_id == 2 || $this->domain_id == 12)
		//{
			$this->load->helper('/parser/parse_'.$helper_name);
			
			log_message('info'," PARSER : domain=".$helper_name);
			$function = 'parse_'.$helper_name;
			
			$result = $function($fetch,$this,false,$domain);
		//}
		return $result;
	}
	
	function set_parse_child($value)
	{
		$this->parse_child = $value;
		$this->update();
	}
	
	function set_parse($value)
	{
		$this->parse_post = $value;
		$this->update();
	}
}