<?php
	function parse_pinkycute($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_pinkycute';

		
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
			$main_content = $html->find('.subject',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('td.post-details',0);
			$board_msg = $board_msg->parent();
			$board_msg = $board_msg->next_sibling();
			$board_msg = $board_msg->next_sibling();
			$board_msg = $board_msg->find('td',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('.poster span',0);
			$post_author = trim($author->plaintext);
			$post_author = explode('(',$post_author);
			$post_author = $post_author[0];

			$date_time = $html->find('td[id^=itemid]',0);
			$post_date = trim($date_time->plaintext);

			$d = substr($post_date,0,2);
			$m = substr($post_date,3,2);
			$y = substr($post_date,6,4);
			$date = explode('??????:',$post_date);
			$t = substr($date[0],strlen($date[0])-5,5);

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
			$comments = $html->find('div[id^=itemid]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
					$comment_title = '';
					$next = $c->find('table tbody tr',0);
					$next = $next->next_sibling();
					
					$c_body = $next->find('td',0);
					$comment_body = trim($c_body->plaintext);
		
					$c_author = $c->find('.poster span',0);
					$comment_author = trim($c_author->plaintext);
					$comment_author = trim($c_author->plaintext);
					$comment_author = explode('(',$comment_author);
					$comment_author = $comment_author[0];
					
					$c_date_time = $c->find('tr .post-details span',0);
					$c_date = $c_date_time->next_sibling();
					$c_time = $c_date->next_sibling();
					$comment_date = trim($c_date_time->plaintext);					
					
					$date = explode('/',$c_date);
					$d = $date[0];
					$m = $date[1];
					$y = thYear_decoder($date[2]);
					
					$t = substr($c_time->plaintext,strlen($c_time->plaintext)-5,5);
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
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
?>