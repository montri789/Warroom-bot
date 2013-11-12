<?PHP
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Matcher_custom extends CI_Controller{

	public function connect_db(){
	
		/*
		$config['hostname'] = "27.254.81.11";
		$config['username'] = "root";
		$config['password'] = "thtoolsth!";
		*/			
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
		
		$db = $this->load->database($config,true);
		
		return $db;
	}
	
	public function bot_auto($sphinx_port=9313,$client_id=null){
	
		//echo "sphinx_port=".$sphinx_port; exit;
		$db_spider = $this->connect_db();		
		
		$IP_Port ='27.254.81.6.'.$sphinx_port;
		$query_index = $db_spider->get_where('sphinx_index',array('name'=>$IP_Port));
		
		$match_port =$sphinx_port;
		$match_index =$query_index->row()->sphinx_index;
		$match_to_id =$query_index->row()->match_1;
		
		$bot_id = rand(0,10000);
		echo 'bot id:'.$bot_id.PHP_EOL;
		
		$bot_query = $this->db->get_where('subject',array('bot_id'=>$bot_id));
		$found_bot = $bot_query->num_rows();
		echo 'found same bot:'.$found_bot.PHP_EOL;
		
		/*
		$option = array(
			'query IS NOT NULL'=> null, 
			'matching_status' => 'update',
			'client_id' => $client_id,
			'to < ' => $to_date,
			'bot_id' => 0 
		);*/
		$option = array(
			'query IS NOT NULL'=> null, 
			'matching_status' => 'update',
			'status' => 'enable',
			'client_id' => $client_id,
			'bot_id' => 0,
			'match_id <  '=>$match_to_id
		);
		
		$query = $this->db->get_where('subject',$option,1,0);			
			
		$available_subjects = $query->num_rows();
		echo 'Available Subject : '.$available_subjects.PHP_EOL;
		$err = false;
			
		while(!$err && $available_subjects > 0){
			
			$subject_id = $query->row()->id;
			echo "Found Subjects:".$subject_id.PHP_EOL;

			$matching_failed = true;
			$subject_taken = false;

			// select subject, change bot_id
			$subject = new Subject_model();
			$subject->init($subject_id);
			if($subject->bot_id != 0){
				echo 'FAILED, Subject Taken by other bot :'.$subject->bot_id.PHP_EOL;
				$subject_taken = true;
			}else{
				$subject->bot_id = $bot_id;
				$subject->update();
			}
			
			if(!$subject_taken){
				$subject->init($subject_id);
				if($subject->bot_id != $bot_id) { echo 'FAILED, Subject Taken by other bot :'.$subject->bot_id.PHP_EOL; continue; }
				
				echo 'subject name: '.$subject->subject.PHP_EOL;
				
				 $from_date = $subject->to;
				//$from_date = '2012-11-01 00:00:00';
				//echo $to_date = $to_date;
				
				//$res = $this->run_auto($subject_id,0,$from_date,$to_date);
				$res = $this->run_auto($subject_id,0,$match_port,$match_index,$match_to_id);
				
				if(!$res){
					echo '(-err)';
					$subject->init($subject_id);
					$subject->bot_id = -1;
					$subject->update();
				}
			}
			
			$query = $this->db->get_where('subject',$option,1,0);
			$available_subjects = $query->num_rows();
			echo 'Available Subject : '.$available_subjects.PHP_EOL;
		}
	}
	
	public function run_auto($subject_id,$query_offset=0,$match_port,$match_index,$match_to_id){
		
		$this->load->helper('sphinxapi');
		$this->load->helper('mood');
		
		$subject_data = $this->custom_model->get_multi_value('subject','id,client_id,subject,query,matching_status',$subject_id);
		$matching_status = $subject_data->matching_status;
		if($matching_status == 'matching'){
			echo "subject is matching";
			return false;
		}
		
		// flag subject as matching.. do other bot runs this queue.
		$this->db->update('subject',array('matching_status'=>'matching','run_matching'=>date('Y-m-d H:i:s')),array('id'=>$subject_id));
		
		$client_id = $subject_data->client_id;
		
		// get search string from subject_id
		$query = $subject_data->query;
		
		echo 'match_port:'.$match_port .PHP_EOL;
		echo 'match_index:'.$match_index .PHP_EOL;
		echo 'match_to_id:'.$match_to_id .PHP_EOL; //exit;
		
		// sphinx init		
		$cl = new SphinxClient ();
		$q = $query;
		$sql = "";
		$mode = SPH_MATCH_EXTENDED;
		$host = "27.254.81.6";
		//$host = "localhost";		
		//$port = 9312;
		$port = (int)$match_port;
		//$index = "src1_facebook_index";
		$index = $match_index;
		
		$groupby = "";
		$groupsort = "@group desc";
		$filter = "group_id";
		$filtervals = array();
		$distinct = "";
		$sortby = "@id ASC";
		$sortexpr = "";
		$offset = $query_offset;
		$limit = 10000;
		$ranker = SPH_RANK_PROXIMITY_BM25;
		$select = "";
		
		echo 'limit='.$limit.' offset='.$offset.PHP_EOL;
		
		$keywords = get_keywords($q);
		
		$cl->SetServer ( $host, $port );
		$cl->SetConnectTimeout ( 1 );
		$cl->SetArrayResult ( true );
		$cl->SetWeights ( array ( 100, 1 ) );
		$cl->SetMatchMode ( $mode );
		// if ( count($filtervals) )	$cl->SetFilter ( $filter, $filtervals );
		// if ( $groupby )				$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, $groupsort );
		if ( $sortby )				$cl->SetSortMode ( SPH_SORT_EXTENDED, $sortby );
		// if ( $sortexpr )			$cl->SetSortMode ( SPH_SORT_EXPR, $sortexpr );
		if ( $distinct )			$cl->SetGroupDistinct ( $distinct );
		if ( $select )				$cl->SetSelect ( $select );
		if ( $limit )				$cl->SetLimits ( 0, $limit, ( $limit>10000 ) ? $limit : 10000 );
		
		//$cl->SetFilterRange('parse_date',strtotime($from),strtotime($to));
		
		$cl->SetRankingMode ( $ranker );
		echo "Starting Query Index...\n";
		$res = $cl->Query ($q, $index );
		echo "Query Indexing\n";
	
	
			//2013-04-26 =================
			//$thothconnect_db->reconnect();				
			$data = array();
			
			//echo '=>'.$date_to; exit;
			
			$data["client_id"] 			= $client_id;
			$data["subject_id"] 		= $subject_id;
			$data["is_matching"] 		= 'Y';
			$data["start_datetime"] 	= date("Y-m-d H:i:s");
			$data["match_from"] 		= date("Y-m-d");
			$data["match_to"] 			= date("Y-m-d");
			$data["match_all"] 			= $res["total"];
			$data["match_insert"] 		= 0;
			$data["last_post_id"] 		= 0;
			$data["wpc_all"] 			= 0;
			$data["wpc_insert"] 		= 0;
			$data["wpc_last_post_id"] 	= 0;
			
			$insert_query = $this->db->insert_string("status_match",$data);
			$this->db->query($insert_query);
			$match_id =$this->db->insert_id();
			
			//echo "match_id=".$match_id."\n";
			//2013-04-26 ================= end
			
		//print_r($res); exit; 	
			
		// Search and Update
		if ( $res===false ){
			echo "Query failed: " . $cl->GetLastError() . ".\n";
			return $res;
		}else{
			if ( $cl->GetLastWarning() ) echo "WARNING: " . $cl->GetLastWarning() . "\n\n";
			echo "Query '$q' \nretrieved $res[total] of $res[total_found] matches in $res[time] sec.\n";
			
			if($res['total'] == 0) echo "no result<br/>\n";
			else if($res['total'] > $limit+$offset) $this->run_auto($subject_id,$limit+$offset,$match_port,$match_index,$match_to_id);
			else
			{
				echo "Updating...";
				$count_all =0;
				
				foreach ( $res["matches"] as $k=>$docinfo ){
					
					// else insert new match
					set_time_limit(60);

					$post = new Post_model();
					//$post->init($docinfo["id"]);
											
					//-----------------------------------------------------
	          
					$postData = $post->get_post_website($docinfo["id"]);					
					//$mood = get_mood($postData->body,$keywords);
					
					//print_r($postData); exit; 	
												
					if($postData != null){
						
						$data = array();
						
						$data["post_date"] = $postData->post_date;
						$data["title"] = $postData->title;
						$data["body"] = $postData->body;
						$data["type"] = $postData->type;
						
						//$data["author"] = $postData->author;
						//$data["website_id"] = $postData->website_id;
						//$data["website_name"] = $postData->website_name;					
						//$data["url"] = $postData->url;
						
						$data["subject_id"] = $subject_data->id;
						$data["subject_name"] = $subject_data->subject;
						//$data["mood"] = $mood;
						$data["mood"] = 0;
						
						$data["insert_date"] = date("Y-m-d H:i:s");
						$data["post_id"] = $postData->id;
						//$data["page_id"] = $postData->page_id;
						
							if($postData->type == 'post' || $postData->type == 'comment'){
								$data["author"] = $postData->author;
								$data["website_id"] = $postData->website_id;
								$data["website_name"] = $postData->website_name;
								$data["url"] = substr($postData->root_url,0,-1)."".$postData->url;
								$data["page_id"] = $postData->page_id;
								echo 'w';
							}else if($postData->type == 'fb_post' || $postData->type == 'fb_comment'){
								$data["author"] = $postData->author_name;
								$data["website_id"] = 217;
								$data["website_name"] = "facebook";
								$url = explode("_",$postData->facebook_id);
								$data["url"] = "https://www.facebook.com/".$url[0]."/posts/".$url[1];
								$data["facebook_id"] = $postData->facebook_id;
								echo 'f';
							}else if($postData->type == 'tweet' || $postData->type == 'retweet'){
																
								$tweet = $post->getRetweeted($postData->tweet_id);
								
								$data["author"] = $postData->author_name;
								$data["website_id"] = 218;
								$data["website_name"] = "twitter";
								$data["url"] = "https://twitter.com/".$postData->author_name."/status/".$postData->tweet_id;
								$data["tweet_id"] = $postData->tweet_id;
								
								if(!empty($tweet)){
									$data["retweet_count"] = $tweet->retweet_count;
									$data["retweeted_status_id"] = $tweet->retweeted_status_id;
								}else{
									$data["retweet_count"] = 0;
									$data["retweeted_status_id"] = 0;
								}								
								echo 't';
							}
					
						$this->db->reconnect();
						
						
						//$match_post_diff =(strtotime(date("Y-m-d")) - strtotime($data["post_date"]))/( 60 * 60 * 24 );
						//if($match_post_diff < 20){
							$insert_query = $this->db->insert_string("message_client_".$subject_data->client_id,$data);
							$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
							$this->db->query($insert_query);

							//2013-04-26 =================
							$count_all++;

							$data_l = array();
							$data_l["stop_datetime"] 	= date("Y-m-d H:i:s");
							$data_l["match_insert"] 	= $count_all;
							$data_l["last_post_id"] 	= $postData->id;

							//$thothconnect_db->reconnect();
							$this->db->update('status_match',$data_l,array('id'=>$match_id));
							//2013-04-26 ================= end								
						//}

						unset($data);
					}
																															
					unset($post);
				}
			}
		}
		
		$check = true;
		if($check == true){
			$data = array(
				'matching_status'=>'update',
				'latest_matching'=> mdate('%Y-%m-%d %H:%i:%s',time()),				
				'match_id'=> $match_to_id,
				'bot_id'=>0
				);
				
				//'to'=> $to,
				
			$this->db->update('subject',$data,array('id'=>$subject_id));
		}
		
		//2013-04-26 =================
		$data_l = array();
		$data_l["stop_datetime"] 	= date("Y-m-d H:i:s");
		$data_l["is_matching"] 	= ($check == true) ? 'N' : 'F';	//N=Complete,F=Fail
		
		//$thothconnect_db->reconnect();
		$this->db->update('status_match',$data_l,array('id'=>$match_id));
		//2013-04-26 ================= end	
		
		unset($res);
		
		return $check;	
	}
		
	public function run_all($type='queue',$client_id=null,$from=null,$to=null)
	{
		$from = '2012-06-01';
		$to = '2012-07-01';
		
		$option = array(
			'query IS NOT NULL'=> null, 
			'matching_status' => $type,
			'client_id' => $client_id,
			'from !=' => $from,
			'to !=' => $to
		);
		
		$query = $this->db->get_where('subject',$option);
		
		echo "Found Subjects:".$query->num_rows().PHP_EOL;
		
		if($type == 'queue') $clean = true;
		else $clean = false;
		
		foreach($query->result() as $row)
		{
			// Reset PHP Timeout to 5min
			set_time_limit(5*60);
			
			$this->run($row->id,$clean,0,$from,$to);
		}
	}
	
	public function search()
	{
		$this->load->helper('sphinxapi');
		$this->load->helper('mood');
		
		// Reset PHP Timeout to 5min
		set_time_limit(0);
		
		$cl = new SphinxClient ();

		$q = $this->input->post('query');
//		$q = "samsung & (\"สินค้า\" | \"ของ\"|\"ที่นี่\") & (\"หมด\" | \"ขาด\"|\"ไม่\"|\"ขาย\"|\"ต้องการ\"|\"เยอะ\"|\"ครบ\"|\"ตลาด\"|\"สต๊อก\" | \"มี\") | (\"ซื้อ\" |\"หา\" ) & (\"ได้\"|\"ไม่\"|\"เจอ\")";
		$sql = "";
		$mode = SPH_MATCH_EXTENDED;
		$host = "27.254.81.6";			
		$port = 9312;
		$index = "*";
		$groupby = "";
		$groupsort = "@group desc";
		$filter = "group_id";
		$filtervals = array();
		$distinct = "";
		$sortby = "@id ASC";
		$sortexpr = "";
		$offset = 0;
		$limit = 1000;
		$ranker = SPH_RANK_PROXIMITY_BM25;
		$select = "";
		
		//Extract subject keyword from search string
		$keywords = get_keywords($q);
		
		////////////
		// do query
		////////////
		
		$cl->SetServer ( $host, $port );
		$cl->SetConnectTimeout ( 1000 );
		$cl->SetMaxQueryTime( 0 );
		$cl->SetArrayResult ( true );
		$cl->SetWeights ( array ( 100, 1 ) );
		$cl->SetMatchMode ( $mode );
		// if ( count($filtervals) )	$cl->SetFilter ( $filter, $filtervals );
		// if ( $groupby )				$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, $groupsort );
		if ( $sortby )				$cl->SetSortMode ( SPH_SORT_EXTENDED, $sortby );
		// if ( $sortexpr )			$cl->SetSortMode ( SPH_SORT_EXPR, $sortexpr );
		// if ( $distinct )			$cl->SetGroupDistinct ( $distinct );
		if ( $select )				$cl->SetSelect ( $select );
		if ( $limit )				$cl->SetLimits ( $offset, $limit, ( $limit>1000 ) ? $limit : 1000 );
		$cl->SetRankingMode ( $ranker );
		$res = $cl->Query ( $q, $index );

		////////////////
		// print me out
		////////////////
		
		$search = array(
			'name' => 'query',
			'id' => 'query',
			'value' => $q,
			'size' => '50',
			'style' => 'width:50%',
			    );
		
		$this->load->helper('form');
		echo "<div>";
		echo form_open('test/search');
		echo "Search [Complex] :";
		echo form_input($search);
		echo form_submit('submit','GO!');
		echo form_close();
		echo "</div>";

		if ( $res===false )
		{
			echo "Query failed: " . $cl->GetLastError() . ".\n";

		} else
		{
			if ( $cl->GetLastWarning() )
				echo "WARNING: " . $cl->GetLastWarning() . "\n\n";

			echo "Query '$q' retrieved $res[total] of $res[total_found] matches in $res[time] sec.\n";
			// echo PHP_EOL.'Query stats:'.PHP_EOL;
			// if ( is_array($res["words"]) )
			// {
			// 	foreach ( $res["words"] as $word => $info )
			// 	{
			// 		echo "    '$word' found $info[hits] times in $info[docs] documents\n";
			// 	}
			// }
			echo PHP_EOL;

			if ( is_array($res["matches"]) )
			{
				$n = 1;
				echo 'Matches:'.count($res["matches"]);
				
				echo "<table border=1 width=960><thead><tr><td width=50>ID</td><td width=20>Mood</td><td width=100>Date</td><td width=200>Title</td><td>Body</td></tr></thead><tbody>";
				foreach ( $res["matches"] as $docinfo )
				{
//					var_dump($docinfo);
					//echo "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
					
					$post = new Post_model();
					$post->init($docinfo["id"]);
					
					echo "<tr><td>($n)$post->id<br/>weight=$docinfo[weight]</td>";
					
//					echo '<td>mood</td>';
					echo '<td>';
					printf("%2.2f",get_mood($post->body,$keywords));
					echo '</td>';
					// foreach ( $res["attrs"] as $attrname => $attrtype )
					// {
					// 	$value = $docinfo["attrs"][$attrname];
					// 	if ( $attrtype==SPH_ATTR_MULTI || $attrtype==SPH_ATTR_MULTI64 )
					// 	{
					// 		$value = "(" . join ( ",", $value ) .")";
					// 	} else
					// 	{
					// 		if ( $attrtype==SPH_ATTR_TIMESTAMP )
					// 			$value = date ( "Y-m-d H:i:s", $value );
					// 	}
					// 	echo ", $attrname=$value";
					// }
					echo "<td>$post->post_date</td><td>$post->title</td><td>$post->body</td></tr>";
					echo PHP_EOL;
					$n++;
				}
				echo "</tbody></table>";
				
			}
		}
	}
	
	public function clear($client_id)
	{		
		$sql = "UPDATE subject SET matching_status = 'update',bot_id=0 WHERE client_id='".$client_id."' and bot_id != 0 and matching_status !='disable'";
		$this->db->query($sql);		
		
		echo "Clear = ".$this->db->affected_rows()." Row".PHP_EOL;

	}
	
}
?>