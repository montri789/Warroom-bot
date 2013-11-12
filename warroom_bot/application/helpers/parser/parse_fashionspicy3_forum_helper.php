<?php
	function parse_fashionspicy3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_fashionspicy3_forum';
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
		
				$main_content = $html->find('.subject',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('.subject',0);
				$board_msg = $board_msg->parent();
				$board_msg = $board_msg->next_sibling();
				$post_body = str_replace('รายละเอียด :                         &nbsp;','',trim($board_msg->plaintext));
	
				$author = $html->find('.poster span',0);
				$post_author = explode('(',trim($author->plaintext));
				$post_author = $post_author[0];
	
				$date_time = $html->find('td[id^=itemid-] span',0);
				$date = trim($date_time->plaintext);
				
				$date_time = $date_time->next_sibling();
				$time = trim($date_time->plaintext);
				
				$date = explode('/',$date);
				$d = $date[0];
				$m = $date[1];
				$y = $date[2];
				$t = substr($time,strlen($time)-5,5);	
				$post_date = $y.'-'.$m.'-'.$d.' '.$t;
				
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
			$comments = $html->find('#webboard-comment div[id^=itemid-]');
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
				
				$comment_title = '';
					
				$c_body = $c->find('table tbody tr',0);
				$c_body = $c_body->next_sibling();
				$comment_body = str_replace('รายละเอียด :                         &nbsp;','',trim($c_body->plaintext));

				$c_author = $c->find('.poster span',0);
				$comment_author = explode('(',trim($c_author->plaintext));
				$comment_author = $comment_author[0];

				$c_date_time = $c->find('table tbody tr span',0);
				$date = $c_date_time->next_sibling();
				$time = $date->next_sibling();
				$date = trim($date->plaintext);	
				$time = trim($time->plaintext);	
							
				$date = explode('/',$date);
				$d = $date[0];
				$m = $date[1];
				$y = thYear_decoder($date[2]);
				$t = substr($time,strlen($time)-5,5);
				$comment_date = $y.'-'.$m.'-'.$d.' '.$t;
				
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
						echo "validate comment failed . . .";
					}
					//$post->insert();
					
					unset($post);
				}
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>