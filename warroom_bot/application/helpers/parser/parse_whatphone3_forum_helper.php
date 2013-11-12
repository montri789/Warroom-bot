<?php
	function parse_whatphone3_forum($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_whatphone3_forum';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$html = str_get_html($fetch);
		
		if($debug)
		{
			$parsed_posts_count = 0;
		}
		else
		{
			$current_posts = $page->get_posts();
			if($current_posts) $parsed_posts_count = count($current_posts);
			else $parsed_posts_count = 0;
		}
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$deadpage = $html->find('div[id=message]',0);
		if($dead_page != null) 
		{
			if($debug)
			{
				echo "Page is dead.";
				echo "<br/>";
			}
			else
			{
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
		
				$main_content = $html->find('div[id=page-body] h2');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('div[class=content]');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('p[class=author] strong');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('div[class=postbody] p[class=author]',0);
				$post_date = trim($date_time->plaintext);

				//--------------
				$post_date = str_replace(",","",$post_date);
				$post_date = explode("&raquo;",$post_date);
				$post_date = preg_split("/[\s]+/",trim($post_date[1]));

				if(preg_match("/^[a-zA-Z]/",$post_date[0])){
					if(trim($post_date[0]) == "Yesterday" || trim($post_date[0]) == "Today"){
						$post_date = dateThText($post_date[0])." ".$post_date[4];	
					}else{ 
						$dd = $post_date[2];	
						$mm = enMonth_decoder($post_date[1],"cut");
						$yy = $post_date[3];
						$tt = $post_date[4];
						$post_date = $yy."-".$mm."-".$dd." ".$tt;
					}
				}else{   
					if(trim($post_date[0]) == "เมื่อวานนี้" || trim($post_date[0]) == "วันนี้"){
						$post_date = dateThText($post_date[0])." ".$post_date[4];	
					}else{  
						$dd = $post_date[2];	
						$mm = thMonth_decoder($post_date[1],"cut");
						$yy = $post_date[3];
						$tt = $post_date[4];
						$post_date = $yy."-".$mm."-".$dd." ".$tt;	
					}
				}			
					
				if($debug)
				{
					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";
				}
				else
				{
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
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('hr[class=divider] div[class=inner]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if(!$result['parse_ok']) break;
				
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
					$c_title = $c->find('div[class=postbody] h3',0);
					$comment_title = trim($c_title->plaintext);
	
					$c_body = $c->find('div[class=content]',0);
					$comment_body = trim($c_body->plaintext);
	
					$c_author = $c->find('p[class=author] strong',0);
					$comment_author = trim($c_author->plaintext);
	
					$c_date_time = $c->find('div[class=postbody] p[class=author]',0);
					$comment_date = trim($c_date_time->plaintext);
	
					//----------------
					$comment_date = str_replace(",","",$comment_date);
					$comment_date = explode("&raquo;",$comment_date);
					$comment_date = preg_split("/[\s]+/",trim($comment_date[1]));
	
					if(preg_match("/^[a-zA-Z]/",$comment_date[0])){
						if(trim($comment_date[0]) == "Yesterday" || trim($comment_date[0]) == "Today"){
							$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
						}else{ 
							$dd = $comment_date[2];	
							$mm = enMonth_decoder($comment_date[1],"cut");
							$yy = $comment_date[3];
							$tt = $comment_date[4];
							$comment_date = $yy."-".$mm."-".$dd." ".$tt;
						}
					}else{   
						if(trim($comment_date[0]) == "เมื่อวานนี้" || trim($comment_date[0]) == "วันนี้"){
							$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
						}else{  
							$dd = $comment_date[2];	
							$mm = thMonth_decoder($comment_date[1],"cut");
							$yy = $comment_date[3];
							$tt = $comment_date[4];
							$comment_date = $yy."-".$mm."-".$dd." ".$tt;	
						}
					}

				
				if($debug)
				{
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<hr>";
				}
				else
				{
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
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>