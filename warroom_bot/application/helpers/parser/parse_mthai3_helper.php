<?php
	function parse_mthai3($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_mthai3';
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

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
		
				$main_content = $html->find('#topic_title');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('#topic_body');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('#topic_author');
				$post_author = trim($author[0]->plaintext);
				$post_author = str_replace("โดย:","",$post_author);

				$date_time = $html->find('#topic_date',0);
				$post_date = trim($date_time->plaintext);
				
				$date = explode(" ",trim(str_replace("ตั้งเมื่อ:","",$post_date)));

				if($date[0] == "เมื่อวานนี้" || $date[0] == "วันนี้")
				{
					$post_date = dateThText($date[0]);
				}else if($date[1] == "ชั่วโมงที่แล้ว" || $date[1] == "นาทีที่แล้ว" || $date[1] == "วินาที" || $date[1] == "วันก่อน" ){
					$tn = $date[0];
					$date[0] = $date[1];
					$post_date = dateThText($date[0],$tn);
				}
				else
				{
					if(preg_match("/^[a-zA-Z]/",$date[1]))
					{
						$mm = enMonth_decoder($date[1],"cut");
					}
					else
					{
						$mm = thMonth_decoder($date[1],"cut");
					}
					$post_date = $date[2]."-".$mm."-".$date[0];	
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
			$comments = $html->find('div[id^=comment_entry]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if(!$result['parse_ok']) break;
				
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
					$c_title = $c->find('.comment_seq',0);
					$comment_title = trim($c_title->plaintext);
	
					$c_body = $c->find('.comment_body p',0);
					$comment_body = trim($c_body->plaintext);
	
					$c_author = $c->find('.comment_author',0);
					$comment_author = trim($c_author->plaintext);
	
					$c_date_time = $c->find('.comment_time',0);
					$comment_date = trim($c_date_time->plaintext);
	
					$date = explode(" ",trim(str_replace("เขียนเมื่อ","",$comment_date)));
	
					if($date[0] == "เมื่อวานนี้" || $date[0] == "วันนี้")
					{
						$comment_date = dateThText($date[0]);
					}
					else if($date[1] == "ชั่วโมงที่แล้ว" || $date[1] == "นาทีที่แล้ว" || $date[1] == "วินาที"  || $date[1] == "วันก่อน")
					{
						$tn = $date[0];
						$date[0] = $date[1];
						
						$comment_date = dateThText($date[0],$tn);
					}
					else
					{
						if(preg_match("/^[a-zA-Z]/",$date[1]))
						{
							$mm = enMonth_decoder($date[1],"cut");
						}
						else
						{
							$mm = thMonth_decoder($date[1],"cut");
						}
						$comment_date = $date[2]."-".$mm."-".$date[0];
						
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