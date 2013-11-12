<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fetch3 extends CI_Controller {
	
	var $sub_process = "-- ";
	
	public function outdate($active_score = -10)
	{
		$option = array(
			'outdate' => 0,
			'active_score <=' => -10
		);
		
		echo "SET OUTDATE :";
		
		$query = $this->db->get_where('page',$option);
		if($query->num_rows() > 0)
		{
			$page = new Page_model();
			
			foreach($query->result() as $row)
			{
				echo $row->id.',';
				
				$page->init($row->id);
				$page->outdate = 1;
				$page->update();
			}
			
			unset($page);
			
		}
	}
	
	public function purge_all($domain_id = null)
	{
		$option = array(
			'domain_id' => $domain_id,
		);
		
		$query = $this->db->get_where('page',$option);
		
		if($query->num_rows() > 0)
		{
			if($query->num_rows() > 0)
			{
				log_message('info','FETCH : PURGE : Records found '.$query->num_rows());
				echo 'FETCH : PURGE : Records found '.$query->num_rows();

				$page = new Page_model();
				foreach($query->result() as $row)
				{
					$page->init($row->id);
					$page->purge();
				}
				unset($page);
			}
		}
	}
	
	public function purge($days=3)
	{
		log_message('info','FETCH : PURGE : Start purging');
		
		$past_date = $days;
		
		$option = array(
			'outdate' => 1,
			'latest_fetch <' => mdate('%Y-%m-%d %H:%i',time()-($past_date*24*60*60)),
			'size !=' => 0
		);
		
		$query = $this->db->get_where('page',$option);
		
		if($query->num_rows() > 0)
		{
			log_message('info','FETCH : PURGE : Records found '.$query->num_rows());
			echo 'FETCH : PURGE : Records found '.$query->num_rows();
			
			$page = new Page_model();
			foreach($query->result() as $row)
			{
				//echo ','.$row->id;
				$page->init($row->id);
				$page->purge();
			}
			unset($page);
		}
	}
	
	public function run()
	{
		while(true)
		{
			// Activate Gabage Collection
			gc_enable();
			$gc_cycles = gc_collect_cycles();
			log_message('info','FETCH : GC : '.$gc_cycles);
			
			log_message('info',"FETCH : ALL");
			$this->all();
			
			log_message('info',"FETCH : SLEEP 20 sec");
			sleep(20);
			
			log_message('info',"FETCH : UPDATE CHILD");
			$this->update_child();
			
			log_message('info',"FETCH : SLEEP 1 HOUR");
			
			// Reset PHP Timeout to 2hours
			set_time_limit(7200);
			sleep(3600);
		}
	}
	
	public function root($domain_id = null, $debug=false)
	{
		// check root page of ALL DOMAIN EVERY 1hr and update_child right away
		
		if($debug) echo 'FETCH:ROOT'; else log_message('info','FETCH:ROOT');
		
		$option = array(
			'parent_page_id' => 0
		);
		if($domain_id != null) $this->db->where('domain_id',$domain_id);
		$query = $this->db->get_where('page',$option);
		if($debug) echo 'FETCH:ROOT: Found pages : '.$query->num_rows() ; else log_message('info','FETCH:ROOT: Found pages : '.$query->num_rows());
		
		$new_fetch_pages = array();
		
		foreach ($query->result() as $row)
		{
			// every domain find latest page_id of root_url
			$page = new Page_model();
			$page->init($row->id);
			while($page->outdate != 0) $page->init($page->new_id);
			
			if($page->id == null) continue; // skip if page is blank
			
			// check latest_fetch > 60 min
			$latest_fetch = strtotime($page->latest_fetch);
			$difference = time()-$latest_fetch;
			$hours = intval(floor($difference / 3600));
			
			if($debug) echo PHP_EOL.'page:'.$page->id.' latest_fetch:'.$page->latest_fetch.' diff:'.$hours;
			
			// if found, fetch page and put in array
			if($hours > 0)
			{
				$fetch = $page->fetch();
				if($fetch != null)
				{
					$new_id = $page->update_new_page($fetch);
					$new_fetch_pages[] = $new_id;
					if($debug) echo ' FETCH';
				}
				else
				{
					$page->less_active();
				}
			}
			unset($page);
		}
		
		unset($new_fetch_pages);
	}
	
	public function domain($domain_id=null)
	{
		log_message('info','FETCH : DOMAIN');
		$domains = $this->domain_model->list_domain("idle");
		
		if(count($domains) > 0)
		{
			foreach($domains as $d)
			{
				if($domain_id != null && $d->id != $domain_id) continue;
				
				// if have group_pattern
				if($d->group_pattern != null)
				{
					$page = new Page_model();
					$page->init();
					$page->outdate = 0;
					$page->domain_id = $d->id;
					$page->parent_page_id = 0;
					$page->parse_child = 0;
					$page->parse_post = 0;
					
					$root_url = $d->root_url;
					$page->url = str_replace($root_url,'/',$d->url);
					$page->active_score = 0;
					$page->view = 0;
					$page->sub_comment = 0;
					$page->insert_date = mdate('%Y-%m-%d %H:%i',time());
					$page->root_page = 1;
					
					$fetch = $page->fetch();
					if($fetch['content'] == null)
					{
						echo "NULL RESULT".PHP_EOL;
						return false;
					}
					$html = str_get_html($fetch['content']);
					$links = $html->find('a');
					$html->clear();
					unset($html);
					
					echo PHP_EOL.'links = '.count($links);
					foreach($links as $element)
					{
						$href = html_entity_decode($element->href);
						$href = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$href);
						
						if($domain_id == 14 || $domain_id == 36 || $domain_id == 39 || $domain_id == 46 || $domain_id == 51 || $domain_id == 53 || $domain_id == 70 || $domain_id == 127) // cut &amp;sid=....... out
						{
							$str = explode('&',$href);
							$href=$str[0];
						}
						if($domain_id == 15 || $domain_id == 29 || $domain_id == 30) // cut ?sid=.... out
						{
							$str = explode('?',$href);
							$href=$str[0];
						}
						if($domain_id == 85)
						{
							$str = explode('/hometh',$href);
							$href=$str[count($str)-1];
							$str = explode('&key',$href);
							$href=$str[0];
						}
						if($domain_id == 95 || $domain_id == 99 || $domain_id == 103)
						{
							//http://www.dvdgameonline.com/forums/index.php?s=25838fd9ce52ecb94f68daaf47d8a2a0&showforum=29
							$str_a = explode('?',$href);
							if(count($str_a) > 1)
							{
								$str_b = explode('&',$str_a[1]);
								if(count($str_b) > 1)
								{
									$href=$str_a[0].'?'.$str_b[1];
								}
							}
						}

						// search "#" and truncate from url
						if(strpos($href,"#") > 0) $href = substr($href,0,strpos($href,"#"));

						// search root_url and truncate
						$root_url = $this->custom_model->get_value('domain','root_url',$d->id);
						if(is_int(strpos($href,$root_url)))
						{
							$href = str_replace($root_url,'/',$href);
						}

						// if href not start with '/' or '.' add '/'
						if((mb_substr($href,0,1) != '/') && (mb_substr($href,0,1) != '.')) $href = '/'.$href;
						
						echo PHP_EOL.'url = '.$href;

						$res = $page->check_url($href,$page);
						if ($res == "group") 
						{
							echo "(group)";
							
							$url_id = $this->is_exist($href,$d->id);
							log_message('info',' domain '.$d->id.' : found group page : '.$url_id);
							if($url_id == 0)
							{
								log_message('info',' domain : update_from_file : new '.$res.':'.$href);

								$p = new Page_model();
								$p->init();
								$p->outdate=0;
								$p->domain_id = $d->id;
								$p->parent_page_id = 0;
								$p->url = $href;
								$p->parse_child = 0;
								$p->sub_comment = 0;
								$p->insert_date = mdate('%Y-%m-%d %h:%i',time());
								$p->insert();
								unset($p);
							}
						}
					}
					unset($page);
					
				}
				else
				{
					$len = strlen($d->root_url);
					$url = substr($d->url,$len-1);
					
					// find existing pages
					$option = array("url"=>$url, "domain_id"=>$d->id);
					$pages = $this->page_model->find($option);
					if(count($pages) == 0) // page does not exist
					{
						$page = new Page_model();
						$page->init();
						$page->outdate = 0;
						$page->domain_id = $d->id;
						$page->parent_page_id = 0;
						$page->parse_child = 0;
						$page->parse_post = 0;
						$page->url = $url;
						$page->active_score = 0;
						$page->view = 0;
						$page->sub_comment = 0;
						$page->insert_date = mdate('%Y-%m-%d %H:%i',time());
						$page->root_page = 1;
						$id = $page->insert();
						log_message('info','new page domain created : '.$id);
						unset($page);
					}
				}
			}
		}
	}
	
	public function all($domain_id=null,$limit=null,$offset=null,$new=true)
	{
		log_message('info','Fetch : ALL');
		$option = array(
			'outdate' => 0,
			'active_score >' => -10
		);
		if($new) $option['parse_post'] = 0;
		if($domain_id != null)
		{
			$option['domain_id'] = $domain_id;
		}
		
		if($offset!=null && $limit!=null)
		{
			echo "LIMITE = $limit, OFFSET = $offset\n";
			$this->db->limit($limit,$offset);
		} 
		$this->db->order_by('active_score','desc');
		$query = $this->db->get_where('page',$option);
		
		echo "FETCH ($query->num_rows) : Page ";
		
		$write_file = false;
		
		if($query->num_rows() > 0)
		{
			log_message('info', 'Fetch : found : '.$query->num_rows()." rows.");
			foreach($query->result() as $row)
			{
				// Reset PHP Timeout to 1min
				set_time_limit(600);
				
				$page = new Page_model();
				$page->init($row->id);
				echo ','.$page->id;
				$fetch = $page->fetch();
				
				$res = null;
				
				if($fetch != null)
				{
					if($page->size == null)
					{
						echo "(same)";
						$res = $page->parse($fetch['content']);
					}
					else if($page->parent_page_id == 0 || $page->root_page == 1)
					{ // has update
						echo "(root)";
					}
					else if($page->parent_page_id != 0 && $this->compare_size($page,$fetch) > 500)
					{ // has update
						echo "(+new)";
						$res = $page->parse($fetch['content']);
					}
					else
					{
						echo "(noch)";
						$page->less_active();
					}
				}
				else
				{
					echo "(-err)";
					$page->less_active();
				}
				
				if($res != null)
				{
					if(!$res['parse_ok'])
						$page->parse_post = -1;
					else{
						// batch insert posts
						$list = array();
						foreach($res['posts'] as $post)
						{
							$list[] = (array)$post;
						}
						//var_dump($list);
						
						
						$insert_res = $this->db->insert_batch('post',$list);
						
						
						// if batch insert success
						// $page->parse_post = 1
						// update page latest_fetch
						
						// or failed
						// $page->parse_post = -2
						if($insert_res == true)
						{
							$page->parse_post = 1;
							$page->latest_fetch = mdate('%Y-%m-%d %H:%i',time());
							$page->size = $fetch['size'];
							// $page->latest_fetch = ??
							// $page->size = ??
						}
						else{
							$page->parse_post = -2;
						}
						//unset($page);
					}
				}
				
				if($page->parse_post < 0)
				{
					// write file
					
					// ถ้า page มีไฟล์อยู่แล้ว
					// ลบไฟล์เก่า ก่อน
					
					// เขียนไฟล์ใหม่
					//filename = latest_fetch + id
					$filename = mdate('%Y%m%d%H%i',time())."_".$page->id;
		
					//file_folder = 8 digits Year+Month+Day
					$folder = mdate('%Y%m%d',time()).'/';
		
					//$path = $this->config->item('fetch_file_path');
					$path = $this->config->item('fetch_file_path');
					if(file_exists($path.$folder.$filename)){
						unlink ($path.$folder.$filename);
					}
					write_file($path.$folder.$filename, $fetch['content']);
			
				}
				
				$page->update();
				unset($page);
			}
		}
	}
	
	public function update_root($domain_id=null,$debug=false)
	{	
		if($domain_id == null)
		{
			if($debug) echo "No domain_id";
			exit();
		}
		
		$option = array(
			'domain_id' => $domain_id,
			'parent_page_id' => 0,
		);
		
		$this->db->order_by('latest_fetch','asc');
		$query = $this->db->get_where('page',$option);
		
		if($debug) echo "FETCH : Update Root : Page ";
		
		if($query->num_rows() > 0)
		{
			log_message('info', 'Fetch : found : '.$query->num_rows()." rows.");
			foreach($query->result() as $row)
			{
				$page = new Page_model();
				$page->init($row->id);
				
				// Reset PHP Timeout to 1min
				set_time_limit(60);
				
				while($page->outdate){
					//echo $page->id.'->'.$page->new_id.',';
					$page->init($page->new_id);
				}
			
				if($debug) echo ','.$page->id;
				
				// if not fetch, do fetch
				if($page->size == null)
				{
					$fetch = $page->fetch();
					$page->update_same_page($fetch,true);
					echo "THIS SAD<br>";
					if($debug) echo '(fetched)';
				}
				else if($page->parse_child == 1) // if fetched and parsed, do new fetch
				{
					$fetch = $page->fetch();
					$page->update_new_page($fetch,true);
					$page->init($page->new_id);
					if($debug) echo '->'.$page->id.'(fetched)';
				}
				
				// and parse
				$page->update_child_from_file();
				if($debug) echo '(parsed)';
				
				unset($page);
			}
		}
		
		unset($query);
	}
	
	
	public function update_child($domain_id = null)
	{
		log_message('info','Fetch : UPDATE_CHILD from ROOT');
		
		if($domain_id == null)
		{
			$this->db->where('parent_page_id',0);
			$this->db->or_where('domain_id',4);
			$this->db->or_where('domain_id',6);
			$this->db->or_where('domain_id',7);
			$this->db->or_where('domain_id',10);
			$this->db->or_where('domain_id',11);
		}
		else
		{
			$this->db->where('domain_id',$domain_id);
		}
		$this->db->order_by('latest_fetch','asc');
		$query = $this->db->get_where('page');
		
		
		echo "FETCH : Update child : Page ";
		
		if($query->num_rows() > 0)
		{
			log_message('info', 'Fetch : found : '.$query->num_rows()." rows.");
			foreach($query->result() as $row)
			{
				$page = new Page_model();
				$page->init($row->id);
				
				// Reset PHP Timeout to 1min
				set_time_limit(60);
				
				while($page->outdate) $page->init($page->new_id);
				
				if($page->size == null) log_message('info','page : '.$page->id.' : bad file');
				else
				{
					echo ','.$page->id;
					$page->update_child_from_file();
				} 
				
				unset($page);
			}
		}
		
		unset($query);
	}
	
	function compare_size($page,$fetch)
	{
		$old = $page->size;
		$new = $fetch['meta']['size_download'];
		$dif = abs($new-$old);
		log_message('info','Fetch : compare_size : '.$dif);
		return $dif;
	}
	
	function is_exist($url,$domain)
	{
		$options = array (
			'url' => $url,
			'domain_id' => $domain
			);
		$query = $this->db->get_where('page',$options);
		return $query->num_rows();		
	}
	
	function test_purge($id)
	{
		$p = new Page_model();
		$p->init($id);
		$p->purge();
		unset($p);
	}
	
	function test()
	{
		$p = new Page_model();
		$p->init(4208);
		$p->update_child_from_file();
	}
	
	function test_check_url()
	{
		$url = "/forum/?topic=3105631.45";
		$id = 84369;
		$page = new Page_model();
		$page->init($id);
		
		echo $this->page_model->check_url($url,$page);
		
		unset($page);
	}
	
	function test_pattern()
	{
		// blognone
		//$url = "/news/28710/กไอซีที-เริ่มโครงการ-wi-fi-ฟรี-28-ธค-นี้-ที่สยามดิสคัฟเวอรี่";
		//$url = "/news/28710/กไอซีที-เริ่มโครงการ-wi-fi-ฟรี-28-ธค-นี้-ที่สยามดิสคัฟเวอรี่#comment-368466";
		
		// dek-d
		//$url = "/board/view.php?id=2340585";
		//$url = "/board/view.php?pno=6&id=2340585";
		
		// lcdtv
		//$url = "./detail.asp?param_id=850";
		$url = "/mainblog.php?id=goirish2011&month=01-02-2012&group=24&gblog=54";
		$domain_id = 22;
		
		$sub_comment = $this->custom_model->get_value('domain','sub_comment_pattern',$domain_id);
		$child = $this->custom_model->get_value('domain','child_pattern',$domain_id);
		
		echo $url;
		echo "<br>";
		if (preg_match($child, $url)) echo "child";
		else if ($sub_comment!=null && preg_match($sub_comment, $url)) echo "sub_comment";
		else echo "bad";
		echo "<hr>";
		
		$parent_page_id = 67045;
		// if url start with "?", get parent page (which is not also a sub_comment page) and entail
		if($url[0] == "?")
		{
			$parent = new Page_model();
			$parent->init($parent_page_id);
			while($parent->sub_comment) $parent->init($parent->parent_page_id);
			
			$str = explode("?",$parent->url);
			$url = $str[0].$url;
		}
		
		// if url start with ".", trim it out
		if($url[0] == ".")
		{
			$url = substr($url,1);
		}
		echo "url:".$url;
		
	}
}