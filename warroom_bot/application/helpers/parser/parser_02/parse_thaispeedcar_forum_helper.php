<?php
	function parse_thaispeedcar_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaispeedcar_forum';

		
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

		$dead_page = $html->find('.msgfix .showmessage',0);
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
		
				$main_content = $html->find('#threadtitle h1',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('td[id^=postmessage_]',0);
				$post_body = strip_tags($board_msg);
	
				$author = $html->find('.postinfo a',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('.authorinfo em span',0);
				if(empty($date_time->title)){
					$date_time = $html->find('.authorinfo em',0);
					$post_date = trim($date_time->plaintext);
					
					$date = explode(' ',$post_date);
					$date2 = explode('-',$date[1]);
					
					$d = $date2[0];
					$m = $date2[1];
					$y = $date2[2];
					$t = $date[2];
				}
				else{
					$post_date = trim($date_time->title);
					
					$date = explode(' ',$post_date);
					$date2 = explode('-',$date[0]);
					
					$d = $date2[0];
					$m = $date2[1];
					$y = $date2[2];
					$t = $date[1];
				}		
	
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
			$comments = $html->find('div[id^=post_] table[id^=pid]');
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
					
				$c_body = $c->find('td[id^=postmessage_]',0);
				$comment_body = strip_tags($c_body);
	
				$c_author = $c->find('.postinfo a',0);
				$comment_author = trim(str_replace('&nbsp;','',$c_author->plaintext));
				
				$c_date_time = $c->find('.authorinfo em span',0);
				if(empty($c_date_time->title)){
					$c_date_time = $c->find('.authorinfo em',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$date = explode(' ',$comment_date);
					$date2 = explode('-',$date[1]);
					
					$d = $date2[0];
					$m = $date2[1];
					$y = $date2[2];
					$t = $date[2];
				}
				else{
					$comment_date = trim($c_date_time->title);
					
					$date = explode(' ',$comment_date);
					$date2 = explode('-',$date[0]);
					
					$d = $date2[0];
					$m = $date2[1];
					$y = $date2[2];
					$t = $date[1];
				}
				
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