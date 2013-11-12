<?php
	function parse_ladyissue($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_ladyissue';

		
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
				$main_content = $html->find('.topic-title',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($main_content->plaintext)));
	
			$board_msg = $html->find('div.post p',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($board_msg->plaintext)));

			$author = $html->find('#post_name',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($author->plaintext)));

			$date_time = $html->find('.post-date',0);
			$post_date = trim($date_time->plaintext);
			$post_date = explode("/",$post_date);
			$post_date = $post_date[2].'-'.$post_date[1].'-'.$post_date[0];
			
			$time = $html->find('.post-time',0);
			$post_time = trim($time->plaintext);
			$post_time = explode(", ",$post_time);
			$post_time = $post_time[1];
				
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
			$comments = $html->find('.member-info');
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
				$par = $c->parent();
					$c_title = $par->find('');
					$comment_title = trim($c_title->plaintext);
				
					$c_body = $par->find('.webboard-content');
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_body[0]->plaintext)));
					
					$c_author = $par->find('.member-name span[id^=reply_]',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_author->plaintext)));
					
					$c_date_time = $par->find('.post-date',0);
					$comment_date = trim($c_date_time->plaintext);
					$comment_date = explode("/",$comment_date);
					$comment_date = $comment_date[2].'-'.$comment_date[1].'-'.$comment_date[0];
			
					$ctime = $par->find('.post-time',0);
					$comment_time = trim($ctime->plaintext);
					$comment_time = explode(", ",$comment_time);
					$comment_time = $comment_time[1];

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