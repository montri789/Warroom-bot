<?php
	function parse_it24hrs($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_it24hrs';

		
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
				$main_content = $html->find('.title');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('.entry');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('.view-username');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('.meta',0);
				$post_date = trim($date_time->plaintext);

				//echo $post_date."<br>";
				$post_date = str_replace("  "," ",$post_date);

				$pdate = explode(" ",$post_date);

				//$page_view = explode(":",$page_view);
				//$view = explode (" ",$page_view[1]);
				//$year = explode(",",$pdate[6]);
				//$pday = explode(",",$pdate[5]);


				//$yy = thYear_decoder($year[0]);
				$mm = thMonth_decoder($pdate[2],'full');

				$post_date = $pdate[3]."-".$mm."-".$pdate[1];
				
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
			$comments = $html->find('li[class^=fbFeedbackPost fbFirstPartPost uiListItem]');
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
				$c_title = $c->find('.msgtitle',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('.msgtext',0);
				$comment_body = trim($c_body->plaintext);
		
				$c_author = $c->find('.view-username',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date = $c->find('.msgdate',0);
				$comment_date = trim($c_date->title);
				
				$comment_date = str_replace("  "," ",$comment_date);
				$cd = explode(" ",$comment_date);
				$cdate = explode("/",$cd[0]);

				$comment_date = $cdate[0]."-".$cdate[1]."-".$cdate[2]." ".$cd[1];
				
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