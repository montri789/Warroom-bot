<?PHP

	function parse_autozeed($fetch,$page,$debug=false,$domain=null)
	{
		
		$log_unit_name = 'parse_autozeed';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		
		$html = str_get_html($fetch);
		
		if($debug){
			$parsed_posts_count = 0;
		}else{
			$current_posts = $page->get_posts();
			if($current_posts) $parsed_posts_count = count($current_posts);
			else $parsed_posts_count = 0;
		}
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[class=post]',0);
		if($dead_page == null){
			if($debug){
				echo "Page is dead.";
				echo "<br/>";
			}else{
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}
		else
		{
		
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
				
				$main_content = $html->find('.title',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('div[class=post]',0);
				$post_body = trim($board_msg->plaintext);
	
				
				$post_author = "Autozeed Admin";
	
				$date_time = $html->find('.meta .clock',0);
				$post_date = trim($date_time->plaintext);
		    
					
	
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
		
		}		
		
		$html->clear();
		unset($html);
		return $result;
		
	}
?>