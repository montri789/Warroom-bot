<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export extends CI_Controller {

	public function website()
	{
		$client_id = 13; //$this->input->get("client_id");
		$month = '07';//(!empty($_GET["month"])) ? $this->input->get("month") : null;
		
		//$client_id = 7;
		
		$config['hostname'] = "tools.thothmedia.com";
		$config['username'] = "tools";
		$config['password'] = "thtools+th";
		$config['database'] = "kpiology";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
			
		$db2 = $this->load->database($config,true);
		
		$sql = "SELECT 	po.id as 'post_id',po.post_date,po.title,po.body,po.type,a.id as 'author_id',
				a.username as 'author',p.id as 'page_id',d.id as 'website_id',d.name as 'website_name',
				s.cate_id as 'group_id' ,c.cate_name as 'group',
				dc.id as 'website_cate_id',dc.name as 'website_cate',m.sentiment ,
				s.id as 'subject_id',s.subject as 'subject_name' ,d.root_url,p.url,
				m.sentiment as 'mood',dt.id as 'website_type_id',dt.name as 'website_type',m.by as 'mood_by',
				m.system_correct,m.system_correct_date  
			FROM 	domain_type dt,domain_categories dc,page p,post po,matchs m,subject s,author a,categories c,domain d
			WHERE 	d.domain_type_id = dt.id 
				AND dc.id = d.domain_cate_id 
				ANd d.id = p.domain_id 
				AND p.id = po.page_id
				AND po.author_id = a.id 
				AND m.post_id = po.id 
				AND m.subject_id = s.id
				AND s.cate_id = c.cate_id 
				AND po.type IN('post','comment')
				AND s.client_id = $client_id AND matching_status = 'update' ";
		
		if(!empty($month)){		
			$sql .= " AND MONTH(po.post_date) = '".$month."'";
		}
		
		//client_7 => 12,01,02
		
		$query = $this->db->query($sql);
	
		foreach($query->result_array() as $val){
				
			$data = array();
			
			$data["post_id"] = $val["post_id"];
			$data["post_date"] = $val["post_date"];
			$data["title"] = $val["title"];
			$data["body"] = $val["body"];
			$data["type"] = $val["type"];
			$data["author_id"] = $val["author_id"];
			$data["author"] = $val["author"];
			$data["website_id"] = $val["website_id"];
			$data["website_name"] = $val["website_name"];
			$data["website_cate_id"] = $val["website_cate_id"];
			$data["website_cate"] = $val["website_cate"];
			$data["website_type_id"] = $val["website_type_id"];
			$data["website_type"] = $val["website_type"];
			$data["group_id"] = $val["group_id"];
			$data["group"] = $val["group"];
			$data["url"] = substr($val["root_url"],0,-1)."".$val["url"];
			$data["page_id"] = $val["page_id"];
			$data["subject_id"] = $val["subject_id"];
			$data["subject_name"] = $val["subject_name"];
			$data["mood"] = $val["mood"];
			$data["mood_by"] = $val["mood_by"];
			$data["system_correct"] = $val["system_correct"];
			$data["system_correct_date"] = $val["system_correct_date"];
		
			$db2->insert("website_c".$client_id,$data);
		}
			
		echo "Export Success";
	}
	public function website_parsedate(){
		
		$client_id = 13;
			
		$config['hostname'] = "tools.thothmedia.com";
		$config['username'] = "tools";
		$config['password'] = "thtools+th";
		$config['database'] = "kpiology";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$db2 = $this->load->database($config,true);  
		
		$sql = "SELECT 	po.id as 'post_id',po.parse_date as 'post_date',po.title,po.body,po.type,a.id as 'author_id',
				a.username as 'author',p.id as 'page_id',d.id as 'website_id',d.name as 'website_name',
				s.cate_id as 'group_id' ,c.cate_name as 'group',
				dc.id as 'website_cate_id',dc.name as 'website_cate',m.sentiment ,
				s.id as 'subject_id',s.subject as 'subject_name' ,d.root_url,p.url,
				m.sentiment as 'mood',dt.id as 'website_type_id',dt.name as 'website_type',m.by as 'mood_by',
				m.system_correct,m.system_correct_date 
			FROM 	domain_type dt,domain_categories dc,page p,post po,matchs m,subject s,author a,categories c,domain d
			WHERE 	d.domain_type_id = dt.id 
				AND dc.id = d.domain_cate_id 
				ANd d.id = p.domain_id 
				AND p.id = po.page_id
				AND po.author_id = a.id 
				AND m.post_id = po.id 
				AND m.subject_id = s.id
				AND s.cate_id = c.cate_id 
				AND po.type IN('post','comment')
				AND s.client_id = $client_id 
				AND matching_status = 'update'
				AND post_date = '0000-00-00 00:00:00' 
				AND MONTH(po.parse_date) = '08' "; //AND MONTH(m.matching_date) = '07'
				
				//07,06,05,04,03,02,01,12  
			
		$query = $this->db->query($sql);
	
		foreach($query->result_array() as $val){
				
			$data = array();
			
			$data["post_id"] = $val["post_id"];
			$data["post_date"] = $val["post_date"];
			$data["title"] = $val["title"];
			$data["body"] = $val["body"];
			$data["type"] = $val["type"];
			$data["author_id"] = $val["author_id"];
			$data["author"] = $val["author"];
			$data["website_id"] = $val["website_id"];
			$data["website_name"] = $val["website_name"];
			$data["website_cate_id"] = $val["website_cate_id"];
			$data["website_cate"] = $val["website_cate"];
			$data["website_type_id"] = $val["website_type_id"];
			$data["website_type"] = $val["website_type"];
			$data["group_id"] = $val["group_id"];
			$data["group"] = $val["group"];
			$data["url"] = substr($val["root_url"],0,-1)."".$val["url"];
			$data["page_id"] = $val["page_id"];
			$data["subject_id"] = $val["subject_id"];
			$data["subject_name"] = $val["subject_name"];
			$data["mood"] = $val["mood"];
			$data["mood_by"] = $val["mood_by"];
			$data["system_correct"] = $val["system_correct"];
			$data["system_correct_date"] = $val["system_correct_date"];
		
			$db2->insert("website_c".$client_id,$data);
		}
		
		echo "Export Success";
	}
	
	public function website_post_comment(){
			
		$client_id = 13;//$this->input->get("client_id");
		$month = '08';//(!empty($_GET["month"])) ? $this->input->get("month") : null;
		
		//$client_id = 7;
		
		$config['hostname'] = "tools.thothmedia.com";
		$config['username'] = "tools";
		$config['password'] = "thtools+th";
		$config['database'] = "kpiology";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$db2 = $this->load->database($config,true);  
				
		$sql = "SELECT 	page_id
			FROM 	website_c".$client_id ." ";
		
		if(!empty($month)){
			$sql .="WHERE 	MONTH(post_date) = '".$month ."' ";
		}
		
		$sql .="GROUP BY page_id ";
			
			//WHERE 	MONTH(post_date) = '07' 
			
		//client_11 =>  04,05,06,	
			
		//m.subject_id = 593
		//client_3 => 07,06,05,04,03,02,01,12
		//client_7 =>12, 
		
		$query = $db2->query($sql);
		
		foreach($query->result_array() as $row){
		
			$sql = "SELECT 	po.id as 'post_id',po.post_date,po.title,po.body,po.type,a.id as 'author_id',
					a.username as 'author',p.id as 'page_id',d.id as 'website_id',d.name as 'website_name',
					dc.id as 'website_cate_id',dc.name as 'website_cate',d.root_url,p.url,
					dt.id as 'website_type_id',dt.name as 'website_type'
				FROM 	domain_type dt,domain_categories dc,page p,post po,author a,categories c,domain d
				WHERE 	d.domain_type_id = dt.id 
					AND dc.id = d.domain_cate_id 
					ANd d.id = p.domain_id 
					AND p.id = po.page_id
					AND po.author_id = a.id 
					AND po.type IN('post','comment')
					AND po.page_id = ".$row["page_id"]."  ";
			
			$query_post = $this->db->query($sql);
			
			foreach($query_post->result_array() as $val){
				
				$sql = "SELECT post_id FROM website_post_comment_c".$client_id ." WHERE post_id = ".$val["post_id"]." ";
				$query2 = $db2->query($sql);
				
				
				if($query2->num_rows() <= 0 ){  
							
					$data = array();
					
					$data["post_id"] = $val["post_id"];
					$data["post_date"] = $val["post_date"];
					$data["title"] = $val["title"];
					$data["body"] = $val["body"];
					$data["type"] = $val["type"];
					$data["author_id"] = $val["author_id"];
					$data["author"] = $val["author"];
					$data["website_id"] = $val["website_id"];
					$data["website_name"] = $val["website_name"];
					$data["website_cate_id"] = $val["website_cate_id"];
					$data["website_cate"] = $val["website_cate"];
					$data["website_type_id"] = $val["website_type_id"];
					$data["website_type"] = $val["website_type"];
					$data["url"] = substr($val["root_url"],0,-1)."".$val["url"];
					$data["page_id"] = $val["page_id"];
					
		
					$db2->insert("website_post_comment_c".$client_id ."",$data);
				}
			}
		}
	}
	
	public function twitter(){
		$client_id = 13;
		
		//$client_id = $this->input->get("client_id");
		
		$config['hostname'] = "tools.thothmedia.com";
		$config['username'] = "tools";
		$config['password'] = "thtools+th";
		$config['database'] = "kpiology";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$db2 = $this->load->database($config,true);
		
		$sql = "SELECT 	po.id as 'post_id',po.post_date,po.body,po.type,tweet_id,
				a.id as 'author_id',a.username as 'author',s.id as 'subject_id',s.subject as 'subject_name',
				m.sentiment as 'mood',c.cate_name as 'group',c.cate_id as 'group_id',m.by as 'mood_by',
				m.system_correct,m.system_correct_date 
			FROM 	post po,author a,matchs m,subject s,categories c   
			WHERE 	po.author_id = a.id 
				AND po.id = m.post_id 
				AND m.subject_id = s.id 
				AND s.cate_id = c.cate_id 
				AND type IN('tweet','retweet') AND MONTH(po.post_date) = '07'  
				AND s.client_id = ".$client_id; 
				
				//AND m.subject_id = 593 
				//client_7 => 07,06,05,04,03,02,01,12 
				
		$query = $this->db->query($sql);
		
		foreach($query->result_array() as $val){
			
			$data = array();
			
			$data["post_id"] = $val["post_id"];
			$data["post_date"] = $val["post_date"];
			$data["body"] = $val["body"];
			$data["type"] = $val["type"];
			$data["author_id"] = $val["author_id"];
			$data["author"] = $val["author"];
			$data["group_id"] = $val["group_id"];
			$data["group"] = $val["group"];
			$data["tweet_id"] = $val["tweet_id"];
			$data["subject_id"] = $val["subject_id"];
			$data["subject_name"] = $val["subject_name"];
			$data["mood"] = $val["mood"];
			$data["mood_by"] = $val["mood_by"];
			$data["system_correct"] = $val["system_correct"];
			$data["system_correct_date"] = $val["system_correct_date"];
			
			$db2->insert("twitter_c".$client_id,$data);
		}	
	}
	function facebook(){
		
		$client_id = 13;
		//$client_id = $this->input->get("client_id");
		
		$config['hostname'] = "tools.thothmedia.com";
		$config['username'] = "tools";
		$config['password'] = "thtools+th";
		$config['database'] = "kpiology";
		$config['dbdriver'] = "mysql";
		$config['dbprefix'] = "";
		$config['pconnect'] = FALSE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";
		
		$db2 = $this->load->database($config,true);  
			
		$sql = "SELECT 	po.id as 'post_id',po.post_date,po.type,a.id as 'author_id',
				a.username as 'author',body,m.sentiment as 'mood',
				s.id as 'subject_id',s.subject as 'subject_name',po.facebook_id as 'facebook_id',
				pof.parent_post_id,pf.facebook_id as 'facebook_page_id',pf.name as 'facebook_page_name',
				c.cate_name as 'group',c.cate_id as  'group_id',pof.likes,pof.shares,m.by as 'mood_by',
				m.system_correct,m.system_correct_date 
			FROM 	post po,author a,post_facebook pof,page_facebook pf,matchs m ,subject s ,categories c
			WHERE 	po.id = m.post_id 
				AND m.subject_id = s.id 
				AND po.author_id = a.id 
				ANd po.id = pof.post_id 
				AND pof.page_id = pf.facebook_id 
				AND s.cate_id = c.cate_id 
				AND po.type IN ('fb_post','fb_comment') 
				AND s.client_id = ".$client_id." AND MONTH(po.post_date) = '08' "; 
				
				//client_7 => 07,06,05,04,03,02,01,12
				//	AND m.subject_id = 593 
			
		$query = $this->db->query($sql);
		
		foreach($query->result_array() as $val){
				
			$data = array();
			
			$data["post_id"] = $val["post_id"];
			$data["post_date"] = $val["post_date"];
			$data["body"] = $val["body"];
			$data["type"] = $val["type"];
			$data["author_id"] = $val["author_id"];
			$data["author"] = $val["author"];
			$data["group_id"] = $val["group_id"];
			$data["group"] = $val["group"];
			$data["facebook_page_id"] = $val["facebook_page_id"];
			$data["facebook_page_name"] = $val["facebook_page_name"];
			$data["subject_id"] = $val["subject_id"];
			$data["subject_name"] = $val["subject_name"];
			$data["facebook_id"] = $val["facebook_id"];
			$data["parent_post_id"] = $val["parent_post_id"];
			$data["likes"] = $val["likes"];
			$data["shares"] = $val["shares"];
			$data["mood"] = $val["mood"];
			$data["mood_by"] = $val["mood_by"];
			$data["system_correct"] = $val["system_correct"];
			$data["system_correct_date"] = $val["system_correct_date"];
	
			$db2->insert("facebook_c".$client_id,$data);
		}		
	}
	
	
	function sql(){
		
		//$client_id = 11;
		$client_id = $this->input->get("client_id");
			
		$sql = "CREATE TABLE IF NOT EXISTS `website_c".$client_id."` (
			   `id` bigint(20) NOT NULL AUTO_INCREMENT,
			   `post_id` bigint(20) DEFAULT NULL,
			   `post_date` datetime DEFAULT NULL,
			   `title` text,
			   `body` mediumtext,
			   `type` enum('post','comment','tweet','retweet','fb_post','fb_comment') DEFAULT NULL,
			   `author_id` int(10) DEFAULT NULL,
			   `author` varchar(255) DEFAULT NULL,
			   `website_id` int(11) DEFAULT NULL,
			   `website_name` varchar(255) DEFAULT NULL,
			   `website_cate_id` int(11) DEFAULT NULL,
			   `website_cate` varchar(100) DEFAULT NULL,
			   `website_type_id` int(11) DEFAULT NULL,
			   `website_type` varchar(100) DEFAULT NULL,
			   `group_id` int(11) DEFAULT NULL,
			   `group` varchar(100) DEFAULT NULL,
			   `url` text,
			   `page_id` bigint(20) DEFAULT NULL,
			   `subject_id` int(11) DEFAULT NULL,
			   `subject_name` varchar(100) DEFAULT NULL,
			   `mood` smallint(6) DEFAULT NULL,
			   `mood_by` enum('system','staff','admin') DEFAULT 'system',
			   PRIMARY KEY (`id`),
			   KEY `post_id` (`post_id`),
			   KEY `page_id` (`page_id`),
			   KEY `subject_id` (`subject_id`),
			   KEY `author_id` (`author_id`),
			   KEY `website_cate_id` (`website_cate_id`),
			   KEY `post_date` (`post_date`),
			   KEY `group_id` (`group_id`),
			   KEY `mood` (`mood`),
			   KEY `mood_by` (`mood_by`),
			   KEY `type` (`type`),
			   KEY `website_type_id` (`website_type_id`)
			 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"; 
		
		if($this->db->query($sql)){ echo "Create Table : website_c".$client_id." Seccess ";  }
		
		//---------------------------------
			
		      
		$sql = "CREATE TABLE IF NOT EXISTS `website_post_comment_c".$client_id."` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`post_id` bigint(20) DEFAULT NULL,
			`post_date` datetime DEFAULT NULL,
			`title` varchar(512) DEFAULT NULL,
			`body` mediumtext,
			`type` enum('post','comment','tweet','retweet','fb_post','fb_comment') DEFAULT NULL,
			`author_id` int(10) DEFAULT NULL,
			`author` varchar(255) DEFAULT NULL,
			`website_id` int(11) DEFAULT NULL,
			`website_name` varchar(255) DEFAULT NULL,
			`website_cate_id` int(11) DEFAULT NULL,
			`website_cate` varchar(100) DEFAULT NULL,
			`website_type_id` int(11) DEFAULT NULL,
			`website_type` varchar(100) DEFAULT NULL,
			`url` text,
			`page_id` bigint(20) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `post_id` (`post_id`),
			KEY `post_date` (`post_date`),
			KEY `type` (`type`),
			KEY `author_id` (`author_id`),
			KEY `website_id` (`website_id`),
			KEY `website_cate_id` (`website_cate_id`),
			KEY `page_id` (`page_id`),
			KEY `website_type_id` (`website_type_id`)
		      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		
		if($this->db->query($sql)){ echo "Create Table : website_post_comment_c".$client_id." Seccess ";  }
		
		//---------------------------------
		
			$sql = "CREATE TABLE IF NOT EXISTS `twitter_c".$client_id."` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `post_id` bigint(20) DEFAULT NULL,
			  `post_date` datetime DEFAULT NULL,
			  `body` mediumtext,
			  `type` enum('post','comment','tweet','retweet','fb_post','fb_comment') DEFAULT NULL,
			  `author_id` int(10) DEFAULT NULL,
			  `author` varchar(255) DEFAULT NULL,
			  `group_id` int(11) DEFAULT NULL,
			  `group` varchar(255) DEFAULT NULL,
			  `tweet_id` varchar(53) DEFAULT NULL,
			  `subject_id` int(10) DEFAULT NULL,
			  `subject_name` varchar(255) DEFAULT NULL,
			  `mood` smallint(6) DEFAULT NULL,
			  `mood_by` enum('system','staff','admin') DEFAULT 'system',
			  PRIMARY KEY (`id`),
			  KEY `post_id` (`post_id`),
			  KEY `post_date` (`post_date`),
			  KEY `author_id` (`author_id`),
			  KEY `type` (`type`),
			  KEY `tweet_id` (`tweet_id`),
			  KEY `group_id` (`group_id`),
			  KEY `subject_id` (`subject_id`),
			  KEY `mood` (`mood`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		
		if($this->db->query($sql)){ echo "Create Table : twitter_c".$client_id." Seccess ";  }
		
		//---------------------------------
		
		$sql = "CREATE TABLE IF NOT EXISTS `facebook_c".$client_id."` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`post_id` bigint(20) DEFAULT NULL,
			`post_date` datetime DEFAULT NULL,
			`body` mediumtext,
			`type` enum('post','comment','tweet','retweet','fb_post','fb_comment') DEFAULT NULL,
			`author_id` int(10) DEFAULT NULL,
			`author` varchar(255) DEFAULT NULL,
			`group_id` int(11) DEFAULT NULL,
			`group` varchar(255) DEFAULT NULL,
			`facebook_page_id` varchar(255) DEFAULT NULL,
			`facebook_page_name` varchar(255) DEFAULT NULL,
			`subject_id` int(10) DEFAULT NULL,
			`subject_name` varchar(255) DEFAULT NULL,
			`facebook_id` varchar(255) DEFAULT NULL,
			`parent_post_id` varchar(255) DEFAULT NULL,
			`mood` smallint(6) DEFAULT NULL,
			`mood_by` enum('system','staff','admin') DEFAULT 'system',
			`likes` int(11) DEFAULT NULL,
			`shares` int(11) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `post_id` (`post_id`),
			KEY `post_date` (`post_date`),
			KEY `type` (`type`),
			KEY `author_id` (`author_id`),
			KEY `group_id` (`group_id`),
			KEY `facebook_page_id` (`facebook_page_id`),
			KEY `subject_id` (`subject_id`),
			KEY `facebook_id` (`facebook_id`),
			KEY `parent_post_id` (`parent_post_id`),
			KEY `mood` (`mood`)
		      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		
		if($this->db->query($sql)){ echo "Create Table : facebook_c".$client_id." Seccess ";  }
		
		
	}
}