<?php
	function parse_horoworld_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_horoworld_forum';

		
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
				$main_content = $html->find('title',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('div[class=t_fsz]',0);
				$post_body = trim($board_msg->plaintext);
	
				$author = $html->find('div[class=authi]',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('div[class=pi] div[class=authi]',1);
				$p_date = trim($date_time->plaintext);
				
				$str = explode(" ",$p_date);
				$date = $str[1];
				$tt = $str[2];
				$post_date = $date." ".$tt;
					
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
			$comments = $html->find('div[id^=post_] div[class=pct]');
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
					$c_title = $c->find('a[class=postcounter]',0);
					$comment_title = trim($c_title->plaintext);
					
					$comment_body = trim($c->plaintext);
					
					
					$p = $c->parent()->prev_sibling();
					$c_author = $p->find('div[class=authi]',0);
					$comment_author = trim($c_author->plaintext);
					
					$p = $c->prev_sibling();
					$c_date_time = $p->find('div[class=authi]',0);
					$c_date = trim($c_date_time->plaintext);
			
					$str = explode(" ",$p_date);
					$date = $str[1];
					$tt = $str[2];
					$comment_date = $date." ".$tt;

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