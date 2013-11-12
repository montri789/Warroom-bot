<?php
	function parse_overclockzone3_forum($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_overclockzone3_forum';
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

		$dead_page = $html->find('div[class=blockrow restore]',0);
		if($dead_page != null) 
		{
			if($debug)
			{
				echo "Page is dead.";
				echo "<br/>";
			}
			else
			{
				//Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
		
				$main_content = $html->find('div.postrow .title',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div.postrow .content',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div.userinfo .username_container a',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('.postdate',0);
				$post_date = trim($date_time->plaintext);
				$post_date = str_replace(",","",$post_date);
				$post_date = explode("&nbsp;",$post_date);

				if(trim($post_date[0]) == "เมื่อวานนี้" || trim($post_date[0]) == "วันนี้"){
					$post_date = dateThText($post_date[0])." ".$post_date[1];
				}else{
					$tt = $post_date[1];
					$post_date = explode(" ",$post_date[0]);

					$post_date = $post_date[2]."-".enMonth_decoder($post_date[1],"cut")."-".$post_date[0]." ".$tt;
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
			$comments = $html->find('li[id^=post_]');
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
				
					$c_title = $c->find('a.postcounter',0);
					$comment_title = trim($c_title->plaintext);
		
					$c_body = $c->find('div.postrow .content');
					$comment_body = htmlentities(trim($c_body[0]->plaintext), ENT_IGNORE, 'utf-8');
					
					$c_author = $c->find('div.userinfo .username_container a',0);
					$comment_author = trim($c_author->plaintext);
		
					$c_date_time = $c->find('.postdate',0);
					$comment_date = trim($c_date_time->plaintext);
					//$comment_date = trim($date_time->plaintext);
					$comment_date = str_replace(",","",$comment_date);
					$comment_date = explode("&nbsp;",$comment_date);
					
					//echo $comment_date[0]."<br>";
					
					if(trim($comment_date[0]) == "Yesterday" || trim($comment_date[0]) == "Today"){
						$comment_date = dateEnText($comment_date[0])." ".$comment_date[1];
					}else if(trim($comment_date[0]) == "เมื่อวานนี้" || trim($comment_date[0]) == "วันนี้" ){
						$comment_date = dateThText($comment_date[0])." ".$comment_date[1];
					}else{
						$tt = $comment_date[1];
						$comment_date = explode(" ",$comment_date[0]);
						$comment_date = $comment_date[2]."-".enMonth_decoder($comment_date[1],"cut")."-".
										$comment_date[0]." ".$tt;
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
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>