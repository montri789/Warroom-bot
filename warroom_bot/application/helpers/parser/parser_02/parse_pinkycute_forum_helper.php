<?php
	function parse_pinkycute_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_pinkycute_forum';

		
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
				$main_content = $html->find('#board-poster-set',0);
				
				$p_title = $main_content->find('td.subject',0);
				$post_title = trim(str_replace('  ','',$p_title->plaintext));

				$p_body = $main_content->find('tr',2);
				$p_body = $p_body->find('td',0);
				$post_body = trim(str_replace('  ',' ',str_replace('&nbsp;','',$p_body->plaintext)));

				$author = $main_content->find('.poster span',0);
				$post_author = trim($author->plaintext);

				$p_date = $main_content->find('.post-details span',0);
				$p_time = $main_content->find('.post-details span',1);
				
				$date = explode('/',$p_date->plaintext);
				$y = $date[2];
				$m = $date[1];
				$d = $date[0];
				$t = mb_substr($p_time->plaintext,-5);

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
				
				$c_title = $c->find('.post-details .post-order',0);
				$comment_title = trim($c_title->plaintext);
				
				$c_body = $c->find('tr',1);
				$c_body = $c_body->find('td',0);
				$comment_body = trim(str_replace('  ',' ',str_replace('&nbsp;','',$c_body->plaintext)));
	
				$c_author = $c->find('.poster span',0);
				$comment_author = trim(str_replace('&nbsp;','',$c_author->plaintext));
				
				$c_date = $c->find('.post-details span',1);
				$c_time = $c->find('.post-details span',2);
				
				$date = explode('/',$c_date->plaintext);
				$d = $date[0];
				$m = $date[1];
				$y = thYear_decoder($date[2]);
				
				$t = substr($c_time->plaintext,-5);
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