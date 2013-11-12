<?php
	function parse_thaiza($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaiza';

		
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
				$main_content = $html->find('table[width=925] td[bgcolor=#FFFFFF] table[cellpadding=8] strong',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				// Post Body at div.lyriccontent
				$board_msg = $html->find('table[width=925] td[bgcolor=#FFFFFF] table[cellpadding=8]');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				
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
			$comments = $html->find('td[bgcolor=#F5EFB1]');
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
				$c_title = $c->find('span[class=text-1]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

				//Comment Body as div.commentBox div.boardmsg
				$c_body = $c->find('td[class=BorderB1] td[class=text-0]');
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body[0]->plaintext));

				//Comment Author as ui#ownerdetail li b
				$c_author = $c->find('td[class=BorderB1] td[class=text-0]',1);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

				$cauthor = explode(" ",$comment_author);
				$day = explode("[",$cauthor[3]);
				$time = explode("]",$cauthor[6]);


				//Comment Date ul#ownerdetail li
				$c_date_time = $c->find('div[class=timeAgo]',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));

				$cdate = explode(" ",$comment_date);

				//$date = explode(" ",$comment_date);
				$yy = enYear_decoder($cauthor[5]);
				$mm = thMonth_decoder($cauthor[4],'cut');
				//$dd = $date[2];
				//$tt = $date[6];
				//$dd = onlyNum($cd[1]);
				
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