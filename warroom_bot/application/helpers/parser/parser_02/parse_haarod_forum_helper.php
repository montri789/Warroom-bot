<?php
	function parse_haarod_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_haarod_forum';

		
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
		
				$main_content = $html->find('table[width=774] a.t14',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('div[style=margin-top:8px; margin-left:8px;]',0);
			//	$parent_body = $board_msg->parent();
				$post_body = trim(str_replace('&nbsp;','',$board_msg->plaintext));
	
				$author = $html->find('div[style=margin-top:8px; margin-left:10px;] strong',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('div[style=margin-top:2px; margin-left:8px;]',0);
				$date_time = $date_time->next_sibling();
				$date = explode(" ",trim(str_replace(array(" ","เมื่อ",",","-","&gt;")," ",strip_tags($date_time->plaintext))));
				$post_date = $date[2]."-".$date[1]."-".$date[0]." ".$date[20];
				
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
			$comments = $html->find('table[width=774] td[width=426]');
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
				
				$c_title = $c->find('a.titleLink',0);
				$comment_title = trim($c_title->plaintext);
			
				$c_body = $c->parent()->next_sibling()->next_sibling();
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->prev_sibling();
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('div.t10',0);
				$comment_date = trim($c_date_time->plaintext);

				$cdate = str_replace(array("เมื่อ"," ","&nbps;",",")," ",$comment_date);
				$cdate = explode(" ",$cdate);
				$cdate2 = explode("-",$cdate[16]);
				$comment_date = $cdate2[2]."-".$cdate2[1]."-".$cdate2[0]." ".$cdate[46];
				
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