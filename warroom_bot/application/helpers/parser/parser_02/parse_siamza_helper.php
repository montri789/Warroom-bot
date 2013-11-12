<?php
	function parse_siamza($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_siamza';

		
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
				// Post Title at div[style=width....]
				$main_content = $html->find('div[id=content] th[colspan=2]',0);
				$post_title = trim($main_content->plaintext);

				// Post Body at div.lyriccontent
				$board_msg = $html->find('td[class=webboard_right]');
				$post_body = trim($board_msg[0]->plaintext);

				// Post Meta at 
				$author = $html->find('td[class=webboard_left] div[class=colleft] span',0);
				$post_author = trim($author->plaintext);;

				$date_time = $html->find('td[class=webboard_left] div[class=colleft]',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
				$pdate = explode("/",$date[4]);

				// View Count
				//$page_info = $html->find('',0);
				//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info->plaintext));

				$pview = explode(" ",$page_view);

				//$date = explode(" ",$post_date);
				//$yy = thYear_decoder($pdate[3]);
				$mm = enMonth_decoder($date[7],'cut');
				//$dd = $date[2];
				//$tt = $date[6];
				
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
			$comments = $html->find('table[width=100%]');
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
				//Comment Title as div.listCommentHead
				$c_title = $c->find('th[colspan=2]',0);
				$comment_title = trim($c_title->plaintext);

				//Comment Body as div.commentBox div.boardmsg
				$c_body = $c->find('table[class=webboard]');
				$comment_body = trim($c_body[0]->plaintext);

				//Comment Author as ui#ownerdetail li b
				$c_author = $c->find('td[class=webboard_left] div[class=colleft] span',0);
				$comment_author = trim($c_author->plaintext);

				//Comment Date ul#ownerdetail li
				$c_date_time = $c->find('td[class=webboard_left] div[class=colleft]',0);
				$comment_date = trim($c_date_time->plaintext);

				$adate = explode(" ",$post_date);
				$cdate = explode("/",$adate[4]);

				//$date = explode(" ",$comment_date);
				//$yy = thYear_decoder($cdate[3]);
				$mm = thMonth_decoder($cdate[2],'full');
				
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