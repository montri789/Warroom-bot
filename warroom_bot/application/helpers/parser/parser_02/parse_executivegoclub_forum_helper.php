<?php
	function parse_executivegoclub_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_flashfly_forum';

		
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

		$dead_page = $html->find('div[class=panel] div[class=inner] p',0);
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				$main_content = $html->find('.msgtitle',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('.msgtext',0);
				$post_body = trim($board_msg->plaintext);
	
				$author = $html->find('.view-username a',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('.msgdate',0);
				$post_date = trim($date_time->title);
				$date = explode(' ',$post_date);
				
				$date2 = explode('.',$date[0]);
				$d = $date2[0];
				$m = $date2[1];
				$y = $date2[2];
				$t = $date[1];
	
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
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_body;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					$post->insert();
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('.fb_sth');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

					$next = $c->next_sibling();
					
					$c_title = $next->find('.msgtitle',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $next->find('td .msgtext',0);
					$comment_body = trim($c_body->plaintext);
		
					$c_author = $next->find('.view-username a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $next->find('.msgdate',0);
					$comment_date = trim($c_date_time->title);					
					
					$date = explode(' ',$comment_date);
					
					$date2 = explode('.',$date[0]);
					$d = $date2[0];
					$m = $date2[1];
					$y = $date2[2];
					$t = $date[1];
		
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
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "comment";
					$post->title = $comment_title;
					$post->body = trim($comment_body);
					$post->post_date = $comment_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($comment_author));
					$post->insert();
					unset($post);
				}
			//$i++;
		
			}
		}
		$html->clear();
		unset($html);
	}
?>