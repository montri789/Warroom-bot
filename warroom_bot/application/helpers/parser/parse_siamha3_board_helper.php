<?php
	function parse_siamha3_board($fetch,$page,$debug=false,$domain = null)
	{
		$log_unit_name = 'parse_siamha3_board';
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

		$dead_page = $html->find('div[class=thankyou-txt box-round5] p',0);
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
		
				$main_content = $html->find('div[id^=subject_]',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('.post',0);
				$post_body = htmlentities(trim($board_msg->plaintext),ENT_IGNORE,'utf-8');

				$author = $html->find('.windowbg td[width=16%] b',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('td[width=85%] .smalltext',0);
				$post_date = str_replace(array("เมื่อ:",","),"",trim($date_time->plaintext));

				$post_date = explode(" ",trim($post_date));

				if(trim($post_date[3]) == "เมื่อวานนี้" || trim($post_date[3]) == "วันนี้"){
					$post_date = dateThText($post_date[3])." ".$post_date[5];
				}else{		
					$dd = $post_date[4];
					$mm = thMonth_decoder($post_date[3],"full");
					$yy = $post_date[5];
					$tt = $post_date[6];	

					$post_date = $yy."-".$mm."-".$dd." ".$tt;
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
			$comments = $html->find('td[class^=windowbg]');
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
				
					$c_title = $c->find('div[id^=subject_]',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('.post',0);
					$comment_body = trim($c_body->plaintext);
					
					$c_author = $c->find('td[width=16%] b',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('td[width=85%] .smalltext',0);
					$comment_date = str_replace(array("เมื่อ:",","),"",trim($c_date_time->plaintext));
					
					//echo $comment_date."<br>";
					
					$date = explode(" ",$comment_date);
		
					if(trim($date[4]) == "เมื่อวานนี้" || trim($date[4]) == "วันนี้"){
						$comment_date = dateThText($date[4])." ".$date[6];
					}else{		
						$dd = $date[5];
						$mm = thMonth_decoder($date[4],"full");
						$yy = $date[6];
						$tt = $date[7];	
								
						$comment_date = $yy."-".$mm."-".$dd." ".$tt;
					}
				
				if(!empty($comment_author)){
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
				//$i++;
		}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>