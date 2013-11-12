<?php
	function parse_thaiproperty($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaiproperty';

		
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
				$main_content = $html->find('table[width=100%] h3',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $main_content->parent();
				$post = trim($board_msg->plaintext);			

				$post = explode(" จากคุณ ",$post);
				$post_body = $post[0];

				$post = explode(" วันที่ ",$post[1]);

				$post_author = trim($post[0]);

				$date = explode(" ",trim($post[1]));			
				$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1],"full")."-".$date[0];

				
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
			$comments = $html->find('table[width=100%] table[width=100%] td[align=left] table[cellpadding=4]');
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
				$comment_title = "RE : ".$post_title;
						
				$c_body = $c->find('td',0);
				$comment_body = trim($c_body->plaintext);
				
				$comment = $c_body->parent()->next_sibling()->plaintext;
				$comment = str_replace("จากคุณ ","",$comment);
				$comment = explode(" - ",$comment);
				
				$comment_author  = trim($comment[0]);
									
				$date = str_replace("/"," ",trim($comment[1]));
				
				$date = explode(" ",$date);			
				$comment_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
				
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