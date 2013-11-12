<?php
	function parse_thailady_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_soul4street_forum';

		
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
				$topic = $html->find('div[class^=windowbg]');
				
			foreach($topic as $p){ 
				$main_content = $p->find('h5[id^=subject_]',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $p->find('.post .inner',0);
				$post_body = trim($board_msg->plaintext);
	
				$author = $p->find('h4 a',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $p->find('.keyinfo .smalltext',0);
				$post_date = trim($date_time->plaintext);
				$post_date = explode("&#171; ??????? #1 ?????: ",$post_date);
				$post_date = $post_date[1];
				$post_date = explode(" &#187;",$post_date);
				$post_date = $post_date[0];
				$post_date = str_replace(",","",$post_date);
				
				$date = explode(" ",$post_date);	
				$time = @date("H:i", strtotime($date[3]." ".$date[4]));
				$post_date = $date[2]."-".thMonth_decoder($date[0],full)."-".$date[1]." ".$time;
			}
				
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
			$comments = $html->find('div[class^=windowbg]');
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
					$c_title = $c->find('h5[id^=subject_]',0);
					$comment_title = trim($c_title->plaintext);
				
					$c_body = $c->find('.post .inner');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('h4 a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('.keyinfo .smalltext',0);
					$comment_date = trim($c_date_time->plaintext);
					$cdate = explode(" ",$comment_date);
					$ctime = @date("H:i", strtotime($cdate[7]." ".$cdate[8]));
					$comment_date = str_replace(",","",$cdate[6])."-".thMonth_decoder($cdate[4],full)."-".str_replace(",","",$cdate[5])." ".$ctime;		
					
					if($post_date == $comment_date)
						return;

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