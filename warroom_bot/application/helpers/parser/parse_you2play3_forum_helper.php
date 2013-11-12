<?php
	function parse_you2play3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_you2play3_forum';
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
		
				// Post Title at div.clear-list-mar, 1st h1 element
				$main_content = $html->find('div[id^=post_topic_box_]');
				$post_title = trim($main_content[0]->plaintext);

				// Post Body at div.content_only
				$board_msg = $html->find('div[id^=post_box_]');
				$post_body = trim($board_msg[0]->plaintext);

				// Post Meta at [no have]
				$author = $html->find('td a');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('td[class=windowbg] div[class=smalltext]');
				$post_date = trim($date_time[1]->plaintext);
				$str = explode(" ",$post_date);
				$yy = $str[5];
				$mm = $str[3];
				$dd = $str[4];
				$tt = $str[6]; 
				// View Count
				$page_info = $html->find('ul li.right');
				$page_view = trim($page_info[1]->plaintext);
					
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
				
				$c_title = $c->find('div[id^=subject_] a',0);
				$comment_title = trim($c_title->plaintext);



				$c_body = $c->find('div[class=post]',0);
				$comment_body = trim($c_body->plaintext);


				$c_author = $c->find('table tr td b a',0);
				$comment_author = trim($c_author->plaintext);


				$c_date_time = $c->find('div[class=smalltext]',1);
				$comment_date = trim($c_date_time->plaintext);
				$str = explode(" ",$comment_date);
				$yy = $str[6];
				$mm = $str[4];
				$dd = $str[5];
				$tt = $str[7];
				
				
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