<?php
	function parse_sanook3($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_sanook3';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$type = 0;
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
		
				$main_content = $html->find('.webboard_read_post1 h2[style=margin: 0pt; padding: 0pt;]',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div[class=post]',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('table[class=webboard_read_post1] div',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('div[style=margin-bottom:5px;]',0);
				$post_date = trim($date_time->plaintext);

				$post_date = str_replace(array("ผู้ตั้งกระทู้: ",",","&nbsp;"),"",$post_date);
				$date = explode(" ",trim($post_date)); 

				if(trim($date[0]) == "เมื่อวานนี้" || trim($date[0]) == "วันนี้"){		
					$post_date = dateThText($date[0])." ".$date[1];
				}else{
					$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1])."-".trim($date[0])." ".$date[3];
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
					$post = new Post_model2();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_body;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					if($post->validate()){
						$result['posts'] []= $post;
						$result['parse_ok'] = true;
					}
					else{
						$result['parse_ok'] = false;
						echo "validate post failed";
					}
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[class^=webboard_read_post]');
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
				
					$c_title = $c->find('div.date-post span strong',0);
					if(!empty($c_title)){
					$comment_title = trim($c_title->plaintext);
					
						
						$comment_title = str_replace(":","",$comment_title);
						
						$c_body = $c->find('div.post',0);
						$comment_body = trim($c_body->plaintext);
						
						$c_author = $c->find('div.smalltext',0);
						$comment_author = trim($c_author->plaintext);
						
						$c_date_time = $c->find('div.date-post',0);
						$comment_date = trim($c_date_time->plaintext);
						
						$comment_date = str_replace(array(",","&nbsp;"),"",$comment_date);
						$comment_date = str_replace("  "," ",$comment_date);
						
						
						
						$date = explode(" ",trim($comment_date));
						
						if(trim($date[2]) == "เมื่อวานนี้" || trim($date[2]) == "วันนี้"){		
							$comment_date = dateThText($date[2])." ".$date[3];
						}else{
							$comment_date = thYear_decoder($date[4])."-".thMonth_decoder($date[3])."-".$date[2]." ".$date[5];
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
						$post = new Post_model2();
						$post->init();
						$post->page_id = $page->id;
						$post->type = "comment";
						$post->title = $comment_title;
						$post->body = trim($comment_body);
						$post->post_date = $comment_date;
						$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
						$post->author_id = $post->get_author_id(trim($comment_author));
						if($post->validate())
						{
							$result['posts'] []= $post;
							$result['parse_ok'] = true;
						}
						else{
							$result['parse_ok'] = false;
							echo "validate comment failed . . . <br>";
						}
						//$post->insert();
						
						unset($post);
					}
				//$i++;
				}
				}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>