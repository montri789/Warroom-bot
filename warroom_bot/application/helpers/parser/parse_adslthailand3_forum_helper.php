<?php
	function parse_adslthailand3_forum($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_adslthailand3_forum';
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
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
		
				$main_content = $html->find('title');
				$post_title = trim($main_content[0]->plaintext);

				// Post Body at div.boardmsg
				$board_msg = $html->find('div[class=content]');
				$post_body = trim($board_msg[0]->plaintext);
				htmlentities($post_body,ENT_IGNORE);

				// Post Meta at ul#ownerdetail
				$author = $html->find('div[class=popupmenu memberaction] a');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('span[class=date]',0);
				$post_date = trim($date_time->plaintext);
				
				$pd = explode("-",$post_date);
				$pd[2] = str_replace(array("  ","AM","PM")," ",trim($pd[2]));
				$date = explode("&nbsp;",trim($pd[2]));
				
				$post_date = $date[0]."-".enMonth_decoder($pd[1],"cut")."-".$pd[0]." ".$date[1];
					
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
				
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
					$c_title = $c->find('span a[name^=post]',0);
					$comment_title = trim($c_title->plaintext);

					//Comment Body 
					$c_body = $c->find('div[id^=post_message_] blockquote',0);
					$comment_body = trim($c_body->plaintext);
					
					htmlentities($comment_body);
					strip_tags($comment_body);
						
					//Comment Author 
					$c_author = $c->find('div[class=popupmenu memberaction] a',0);
					$comment_author = trim($c_author->plaintext);

					//Comment Date 
					$c_date_time = $c->find('div span span',0);
					$comment_date = trim($c_date_time->plaintext);

					$str0 = explode("&nbsp;",$comment_date);
					$date = $str0[0];
					$tt = substr($str0[1],0,5).':00';
					$str = explode("-",$date);
					$yy = $str[2];
					//$mm = $str[1];
					$month = $str[1]; //or whatever 

					for($i=1;$i<=12;$i++)
					{ 
						if(date("M", mktime(0, 0, 0, $i, 1, 0)) == $month)
						{ 
							if($i<10) $mm = '0'.$i;
							else $mm = $i;
							break; 
						} 
					}

					$dd = $str[0];
					
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				
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