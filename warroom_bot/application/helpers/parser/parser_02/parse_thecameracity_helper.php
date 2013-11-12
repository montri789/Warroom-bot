<?php
	function parse_thecameracity($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thecameracity';

		
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
				$main_content = $html->find('.first');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('.content');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('.author strong');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('.author',0);
				$post_date = trim($date_time->plaintext);

				$post_date = str_replace("  "," ",$post_date);
				$post_date = str_replace(",","",$post_date);
				$pdate = explode(" ",$post_date);

				$l = count($pdate);

				$mm = thMonth_decoder($pdate[$l-5],'cut');

				$post_date = $pdate[$l-3]."-".$mm."-". $pdate[$l-4]." ".$pdate[$l-2];
				
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
			$comments = $html->find('div[class^=post bg]');
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
				$c_title = $c->find('.postbody h3',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('.content',0);
				$comment_body = trim($c_body->plaintext);
		
				$c_author = $c->find('.author strong',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date = $c->find('.author',0);
				$comment_date = trim($c_date->plaintext);
				
				$comment_date = str_replace("  "," ",$comment_date);
				$comment_date = str_replace(",","",$comment_date);					
				$cdate = explode(" ",$comment_date);
				
				$l = count($cdate);

				$mm = thMonth_decoder($cdate[$l-5],'cut');

				$comment_date = $cdate[$l-3]."-".$mm."-". $cdate[$l-4]." ".$cdate[$l-2];
				
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