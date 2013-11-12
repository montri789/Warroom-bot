<?php
	function parse_coffeemade_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_coffeemade_forum';

		
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
		
				$main_content = $html->find('.content .h1',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
	
	
	
				$board_msg = $html->find('td[width=650]',0);
				$post_body = str_replace('&nbsp;','',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));
	
				$author = $html->find('.content div[align=right] .h3',0);
				
				if(empty($author)){ return; }
					
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	             
				$date_time = $author->parent();
				
				if(empty($date_time)){ return; }
				
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
			
				$date_time = $board_msg->parent();
				$date_time = $date_time->next_sibling();
				$date_time = explode('วันที่ลงประกาศ ',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext)));
				$date_time = explode(' ', $date_time[1]);
				$post_date = $date_time[0].' '.str_replace('&nbspIP','',$date_time[1]);
				
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
			$comments = $html->find('table[style=padding:10px;border:1px solid #18B400;]');
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
				
				$comment_title = '';
					
				$c_body = $c->find('tr td[colspan=2]',0);
				$comment_body = str_replace('&nbsp;','',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext)));
	
				$c_author = $c->find('div[align=right] .h3',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $c_body->parent();
				$c_date_time = $c_date_time->next_sibling();
	
				$c_date_time = explode('วันที่ตอบ ',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext)));
				$c_date_time = explode(' ', $c_date_time[1]);
				$comment_date = $c_date_time[0].' '.str_replace('&nbspIP','',$c_date_time[1]);
				
				
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