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
	
	//##
	public function domain($domain_id=null){
		$this->load->model("page_model");
		
		log_message('info','FETCH : DOMAIN');
	
		$sql = "SELECT url,root_url,group_pattern FROM domain WHERE id = $domain_id AND status = 'idle'";
		
		$query = $this->db->query($sql);
		if($query->num_rows() > 0){
			$domain = $query->row_array();
			$query->free_result();
			
			if($domain["group_pattern"] != NULL){
						
				$this->page_model->parent_page_id = 0;
				$root_url = $domain["root_url"];
				$this->page_model->url = str_replace($root_url,'/',$domain["url"]);
			
				$fetch = $this->page_model->fetch($root_url);
				
				if($fetch['content'] == null){
					echo "NULL RESULT".PHP_EOL;
					return false;
				}
				$html = str_get_html($fetch['content']);
				$links = $html->find('a');
				$html->clear();
				unset($html);
				
				echo PHP_EOL.'links = '.count($links);
				foreach($links as $element){

					$href = rawurlencode($element->href);
					$href = rawurldecode($href);
					
					// search "#" and truncate from url
					if(strpos($href,"#") > 0) $href = substr($href,0,strpos($href,"#"));

					// search root_url and truncate
					$root_url = $this->custom_model->get_value('domain','root_url',$domain_id);
					if(is_int(strpos($href,$root_url))){
						$href = str_replace($root_url,'/',$href);
					}

					// if href not start with '/' or '.' add '/'
					if((mb_substr($href,0,1) != '/') && (mb_substr($href,0,1) != '.')) $href = '/'.$href;
					
					echo PHP_EOL.'url = '.$href;
							
					if (preg_match($domain['group_pattern'], $href)){
						echo "(group)";

						if($domain_id == 35){
						    $href = explode("-",$href);
							$href = $href[0];
						}
	
						$url_id = $this->is_exist($href,$domain_id);
						log_message('info',' domain '.$domain_id.' : found group page : '.$url_id);
						if($url_id == 0){

							log_message('info',' domain : update_from_file : new :'.$href);
							
							$data = array();
							$data["outdate"] = 0;
							$data["domain_id"] = $domain_id;
							$data["parent_page_id"] = 0;
							$data["url"] = $href;
							$data["parse_child"] = 0;
							$data["sub_comment"] = 0;
							$data["insert_date"]  = mdate('%Y-%m-%d %h:%i',time());
							
							$this->db->insert("page",$data);
							unset($data);
						}
					}
				}
				unset($page);
					
			}else{
				
				$len = strlen($domain["root_url"]);
				$url = substr($domain["url"],$len-1);
				
				$sql = "SELECT id FROM page WHERE url = '$url' AND domain_id = $domain_id ";
				$query = $this->db->query($sql);
				
				if($query->num_rows() == 0){
										
					$data = array();
					$data["outdate"] = 0;
					$data["domain_id"] = $domain_id;
					$data["parent_page_id"] = 0;
					$data["parse_child"] = 0;
					$data["parse_post"] = 0;
					$data["url"] = $url;
					$data["active_score"] = 0;
					$data["insert_date"] = mdate('%Y-%m-%d %H:%i',time());
					$data["root_page"] = 1;
					$insert = $this->db->insert("page",$data);
			
					log_message('info','new page domain created : '.$insert);
					unset($data);
				}
			}
		}
	}
	
	
	public function update_root($domain_id=null,$debug=false){
		if($domain_id == null){
			if($debug) echo "No domain_id";
			exit();
		}
		
		$sql = "SELECT id FROM page WHERE domain_id = $domain_id AND parent_page_id = 0 AND outdate = 0 
			ORDER BY latest_fetch ";
		$query = $this->db->query($sql);
		
		if($debug) echo "FETCH : Update Root : Page ";
		
		if($query->num_rows() > 0){
			
			log_message('info', 'Fetch : found : '.$query->num_rows()." rows.");
			foreach($query->result_array() as $row){
		
				$page = new Page_model();
				$page->init($row["id"]);
				
				// Reset PHP Timeout to 1min
				set_time_limit(60);
				
				//while($page->outdate){
					//echo $page->id.'->'.$page->new_id.',';
					//$page->init($page->new_id);
				//}

				if($debug) echo ','.$page->id;
				
				$sql = "SELECT root_url FROM domain WHERE id = $domain_id ";
				$query = $this->db->query($sql);
				$result = $query->row_array();
				$query->free_result();
				$fetch = $page->fetch($result["root_url"]);
				
			
				$page->update_same_page($fetch,false); 
				if($debug) echo '(fetched)';
				
				$page->update_child_from_fetch_test($fetch);
				
				if($debug) echo '(parsed)';
				
				unset($page);
			}
		}
		
		unset($query);
	}
	
	public function all($domain_id=null,$limit=null,$offset=null,$new='new'){
		
		/*
		$sql = "SELECT id,client_id,subject,query FROM subject WHERE status = 'enable' ";
		$query = $this->db->query($sql);
		$result_subject = $query->result_array();
		$query->free_result();
		*/
		
		$sql = "SELECT id,name,root_url,helper_name FROM domain WHERE id = $domain_id  ";
		$query = $this->db->query($sql);
		$domain = $query->row_array();
		$query->free_result();
		
		log_message('info','Fetch : ALL');
		
		$sql = "SELECT id  
			FROM page
			WHERE outdate = 0 AND active_score > -10 AND parent_page_id != 0 ";
		
		if($new=='new'){ $sql .= " AND parse_post = 0 "; }
		else if($new=='old'){ $sql .= " AND parse_post != 0 "; }
		else { die('invalid $new element'); }
		if($domain_id != null){
			$sql .= " AND domain_id = ".$domain_id;
		}
		
		$sql .= " ORDER BY active_score DESC,insert_date DESC ";
		
		if($offset!=null && $limit!=null){
			echo "LIMITE = $limit, OFFSET = $offset\n";
			$sql .= " LIMIT $limit,$offset ";
		}
		
		$query = $this->db->query($sql);
		
		echo "FETCH Total $query->num_rows Pages ";
		
		$write_file = false;
		
		$num_row = $query->num_rows();
		if($num_row > 0){
			log_message('info', 'Fetch : found : '.$num_row." rows.");
			
			foreach($query->result_array() as $row){
				// Reset PHP Timeout to 1min
				set_time_limit(600);
				
				$page = new Page_model();
				$page->init($row["id"]);
				echo ','.$page->id;
				$fetch = $page->fetch($domain["root_url"]); 
				
				$res = null;
							
				if($fetch != null){
					if($page->size == null){
						echo "(same)";
						$res = $page->parse($fetch['content'],$domain,$domain_id);
					}else if($page->parent_page_id == 0 || $page->root_page == 1){ // has update
						echo "(root)";
					}else if($page->parent_page_id != 0 && $this->compare_size($page,$fetch) > 500){ // has update
						echo "(+new)";
						//$res = $page->parse($fetch['content'],$domain,$domain_id);
						$res = $page->parse($fetch['content'],$domain);
					}else{
						echo "(noch)";
						$page->less_active();
					}
				}else{
					echo "(-err)";
					$page->less_active();
				}
				
				if($res != null){
					if(!$res['parse_ok']){
						$page->parse_post = -1;
					}else{

						$list = array();
						foreach($res['posts'] as $post){
							$list[] = (array)$post;
						}
						
						//$this->metcher_test($result_subject,$list);
						$insert_res = $this->db->insert_batch('post',$list);

						if($insert_res == true){
							$page->parse_post = 1;
							$page->latest_fetch = mdate('%Y-%m-%d %H:%i',time());
							$page->size = $fetch['size'];
						}else{
							$page->parse_post = -2;
						}
						unset($list);
					}
				}
				
				unset($res);
				
				if($page->parse_post < 0){

					$filename = mdate('%Y%m%d%H%i',time())."_".$page->id;

					$folder = mdate('%Y%m%d',time()).'/';
					
					$path = $this->config->item('fetch_file_path');
					if(file_exists($path.$folder.$filename)){
						unlink($path.$folder.$filename);
					}
					write_file($path.$folder.$filename, $fetch['content']);
				}
				
				unset($page);
			}
		}
	}

	public function get_post($inp_month=1,$date_type = 'month',$domain_id=null,$limit=null,$offset=null,$new='new'){
		
	//	echo "...all_month...=$inp_month"; //exit;

		$sql = "SELECT id,name,root_url,helper_name FROM domain WHERE id = $domain_id  ";
		$query = $this->db->query($sql);
		$domain = $query->row_array();
		$query->free_result();
		
		log_message('info','Fetch : ALL');
		
		$sql = "SELECT 	id  
			FROM 	page
			WHERE 	outdate = 0 AND active_score > -10 ";
		
		if($new=='new'){ $sql .= " AND parse_post = 0 "; }
		else if($new=='old'){ $sql .= " AND parse_post != 0 "; }
		else { die('invalid $new element'); }
		if($domain_id != null){
			$sql .= " AND domain_id = ".$domain_id;
		}

		$date_total =30*$inp_month;
		
		$sql .=" AND (DATE(insert_date) between '".date('Y-m-d',strtotime('-'.$inp_month.' '.$date_type))."' AND  '".date("Y-m-d")."') ";
	
 

		//$sql .=" AND date(insert_date) between DATE_SUB(CURDATE(),INTERVAL $date_total DAY) and NOW() ";
		//$sql .=" AND date(insert_date) between DATE_SUB(CURDATE(),INTERVAL 1 DAY) and NOW() ";
		
		$sql .= " ORDER BY active_score DESC,insert_date DESC ";


		
		if($offset!=null && $limit!=null){
			echo "LIMITE = $limit, OFFSET = $offset\n";
			$sql .= " LIMIT $limit,$offset ";
		}
		
		$query = $this->db->query($sql);
		
		echo "FETCH ($query->num_rows) : Page ";
		
		$write_file = false;
		
		$num_row = $query->num_rows();
		if($num_row > 0){
			log_message('info', 'Fetch : found : '.$num_row." rows.");
			
			foreach($query->result_array() as $row){
				// Reset PHP Timeout to 1min
				set_time_limit(600);
				
				$page = new Page_model();
				$page->init($row["id"]);
				echo "(insert date".$page->insert_date.") ";

				echo ','.$page->id;
				//echo ' root_url=>'.$domain["root_url"];
				$fetch = $page->fetch($domain["root_url"]);
				
				$res = null;
							
				if($fetch != null){
					if($page->size == null){
						echo "(same)";
						$res = $page->parse($fetch['content'],$domain,$domain_id);
					}else if($page->parent_page_id == 0 || $page->root_page == 1){ // has update
						echo "(root)";
					}else if($page->parent_page_id != 0 && $this->compare_size($page,$fetch) > 500){ // has update
						echo "(+new)";
						$res = $page->parse($fetch['content'],$domain,$domain_id);
					}else{
						echo "(noch)";
						$page->less_active();
					}
				}else{
					echo "(-err)";
					$page->less_active();
				}
				
				if($res != null){
					if(!$res['parse_ok']){
						$page->parse_post = -1;
					}else{

						$list = array();
						foreach($res['posts'] as $post){
							$list[] = (array)$post;
						}
						
						//$this->metcher_test($result_subject,$list);
						$insert_res = $this->db->insert_batch('post',$list);

						if($insert_res == true){
							$page->parse_post = 1;
							$page->latest_fetch = mdate('%Y-%m-%d %H:%i',time());
							$page->size = $fetch['size'];
						}else{
							$page->parse_post = -2;
						}
						unset($list);
					}
				}
				
				unset($res);
				
				if($page->parse_post < 0){

					$filename = mdate('%Y%m%d%H%i',time())."_".$page->id;

					$folder = mdate('%Y%m%d',time()).'/';
					
					$path = $this->config->item('fetch_file_path');
					if(file_exists($path.$folder.$filename)){
						unlink($path.$folder.$filename);
					}
					write_file($path.$folder.$filename, $fetch['content']);
				}
				
				unset($page);
			}
		}
	}
	
	public function pantipnew_insert_page($start = null,$end = null){	

	/*
		$config['hostname'] = "27.254.81.11";
		$config['username'] = "root";
		$config['password'] = "thtoolsth!";
		$config['database'] = "spider";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
	*/	
		$config_wr['hostname'] = "27.254.81.6";
		$config_wr['username'] = "root";
		$config_wr['password'] = "usrobotic";
		$config_wr['database'] = "warroom";
		$config_wr['dbdriver'] = "mysql";
		$config_wr['dbprefix'] = "";
		$config_wr['pconnect'] = FALSE;
		$config_wr['db_debug'] = TRUE;
		$config_wr['cache_on'] = FALSE;
		$config_wr['cachedir'] = "";
		$config_wr['char_set'] = "utf8";
		$config_wr['dbcollat'] = "utf8_general_ci";
		
		//$connect_kpio = $this->load->database($config,true);
		$connect_wr = $this->load->database($config_wr,true);
		
		$sql = "SELECT url,CAST(RIGHT(url,8)AS UNSIGNED)AS last_page FROM page WHERE domain_id =212 and outdate =0 ORDER BY ID DESC LIMIT 1";
		$query = $connect_wr->query($sql);
		$res = $query->row_array();
		//print_r($res); exit;
		
		$start =$res["last_page"]+1;
		$end =$res["last_page"]+20000;
		
		$url = explode("/",$res["url"]);
		$url = $url[2];
		
		//echo 'url=>'.$url;
		if($start > $url || empty($url)){
			
			$count_err =0;
			$insert = array();
			for($i = $start; $i<= $end; $i++){
				
				$data["domain_id"] 		= 212;
				$data["parent_page_id"] = 22598247;
				$data["url"] 			= '/topic/'.$i;
				$data["parse_post"] 	= 0;
				$data["insert_date"] 	= date("Y-m-d H:i:s");
				$data["active_score"] 	= 0;
				$data["view"] 			= 0;
				$data["sub_comment"] 	= 0;
				$data["root_page"] 		= 0;
				
				$site = 'http://pantip.com/topic/'.$i;
				
				$handle = curl_init($site);
				curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
			    
				/* Get the HTML or whatever is linked in $url. */
				$response = curl_exec($handle);
			    
				/* Check for 404 (file not found). */
				$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
				curl_close($handle);
				
				if ($httpCode >= 200 && $httpCode < 300) {
					//$insert[] =  $data;
					//$connect_kpio->insert('page',$data);	//insert to kpiology
					$connect_wr->insert('page',$data);		//insert to warroom
					echo "(".$i."=ok) ";
					
				}else{
					echo "(".$i."=404 err) ";
					$count_err++;
				}
				
				if($count_err == 100) $end=$i;
				set_time_limit(0); //no limit
				//echo $i."\n";
			}
			
			if($count_err !=0) $insert_to=$i-($count_err+1);
			else $insert_to=$i-1;
			
			echo "\n Update Pantip Page to ".$insert_to."\n";
		
			//$connect_kpio->close();
			$connect_wr->close();
		}
	}		

	function compare_size($page,$fetch){
		$old = $page->size;
		$new = $fetch['meta']['size_download'];
		$dif = abs($new-$old);
		log_message('info','Fetch : compare_size : '.$dif);
		return $dif;
	}
	function is_exist($url,$domain){
		$options = array (
			'url' => $url,
			'domain_id' => $domain
			);
		$query = $this->db->get_where('page',$options);
		return $query->num_rows();		
	}
}