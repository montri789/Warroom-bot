<?PHP

	function parse_pantip_new($fetch,$page,$debug=false,$domain=null){
	
		$log_unit_name = 'parse_pantip_new';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		//echo 'log '.$log_unit_name;
		$html = str_get_html($fetch);
		
		if($debug){
			$parsed_posts_count = 0;
		}else{
			$current_posts = $page->get_posts();
			if($current_posts) $parsed_posts_count = count($current_posts);
			else $parsed_posts_count = 0;
		}
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('.display-post-wrapper',0);
		
		if($dead_page == null){
			if($debug){
				echo "Page is dead.";
				echo "<br/>";
			}else{
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}else{
			if($parsed_posts_count == 0 && $page->sub_comment == 0){
				$main_content = $html->find('h2.display-post-title',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div.display-post-story',0);
				$post_body = strip_tags(trim($board_msg->plaintext));
				
				$author = $html->find('a.display-post-name',0);
				$post_author = trim($author->plaintext);	
				
				$date = $html->find('.display-post-timestamp .timeago',0);
				$post_date = trim($date->getAttribute('data-utime'));
	
				$post_date = explode(" ",$post_date);
				$date = explode("/",trim($post_date[0]));
				$post_date = $date[2]."-".$date[0]."-".$date[1]." ".$post_date[1];
				
				if($debug){
					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";
				}else{
					$data = array();
					$data["page_id"] = $page->id;
					$data["type"] = "post";
					$data["title"] = $post_title;
					$data["body"] = $post_body;
					$data["post_date"] = $post_date;
					$data["parse_date"] = mdate('%Y-%m-%d %H:%i',time());
					$data["author"] = $post_author;
					$data["website_id"] = $domain["id"];
					$data["website_name"] = $domain["name"];
					$data["url"] = substr($domain["root_url"],0,-1)."".$page->url;
					
					if(validate($post_date)){
						$result['posts'] []= $data;
						$result['parse_ok'] = true;
					}else{
						$result['parse_ok'] = false;
						echo "validate post failed . . .";
					}
					unset($data);
				}
			}else { echo "(sub)"; }
		
			$id = $html->find('div[id^=topic-]',0);
			$id = explode("-",$id->getAttribute('id'));
			$id = $id[1];
			
			$comment_url = 'http://www.pantip.com/forum/topic/render_comments?tid='.$id;
			//echo $comment_url;
			//$data = json_decode($json, TRUE); 
			
			//$ch = curl_init( $comment_url );
			
			//$options = array(
			//CURLOPT_RETURNTRANSFER => true,
			//CURLOPT_HTTPHEADER => array('Content-type: application/json')
			//);
			//curl_setopt_array( $ch, $options );
			//$comment_result =  curl_exec($ch);
			$json = file_get_contents($comment_url);
						
			$comment_result = json_decode($json, TRUE);
			//$comment_result = $comment_result[0];
			
			if(isset($comment_result["comments"])){
			
				log_message('info', $log_unit_name.' : found elements : '.count($comment_result["comments"]));
				echo '(cm='.count($comment_result["comments"]).')';
				
				//print_r($comment_result); exit();
		
				foreach($comment_result["comments"] as $val){
					
					if(!$result['parse_ok']) break;
					
					$comment_title = $val["comment_no"];
					$comment_body = strip_tags($val["message"]);
					$comment_author = $val["user"]["name"];
					$comment_date = $val["data_utime"];
					
					$comment_date = explode(" ",$comment_date);
					$date = explode("/",trim($comment_date[0]));
					$comment_date = $date[2]."-".$date[0]."-".$date[1]." ".$comment_date[1];
					
					if($debug){
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<hr>";
					}else{
						$data = array();
						$data["page_id"] = $page->id;
						$data["type"] = "comment";
						$data["title"] = $comment_title;
						$data["body"] = $comment_body;
						$data["post_date"] = $comment_date;
						$data["parse_date"] = mdate('%Y-%m-%d %H:%i',time());
						$data["author"] = $comment_author;
						$data["website_id"] = $domain["id"];
						$data["website_name"] = $domain["name"];
						$data["url"] = substr($domain["root_url"],0,-1)."".$page->url;
						
						if(validate($comment_date)){
							$result['posts'] []= $data;
							$result['parse_ok'] = true;
						}else{
							$result['parse_ok'] = false;
							echo "validate comment failed . . . <br>";
						}
						unset($data);
					}
				}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>