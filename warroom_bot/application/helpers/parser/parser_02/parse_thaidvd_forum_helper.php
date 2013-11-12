<?php
	function parse_thaidvd_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaidvd_forum';
		
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

		$dead_page = $html->find('div[class=errorwrap]',0);
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
				$main_content = $html->find('table[class=ipbtable] div[class=maintitle] div',0);
				if(is_null($main_content)) $post_title = null;
				else $post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
				//$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('table[class=ipbtable] div[class=postcolor]',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
				//$post_body = trim($board_msg->plaintext);

				$author = $html->find('table[class=ipbtable] span[class=normalname] a',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				//$post_author = trim($author->plaintext);
				
				// Remove '(...)' from author
				// $found_pos = strpos($post_author,'(');
				// if($found_pos !== false) $post_author = trim(substr($post_author,0,$found_pos));

				$date_time = $html->find('table[class=ipbtable] td[class=row2] span[class=postdetails]',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				//$post_date = trim($date_time->plaintext);

				//echo $post_date."<br>";
				$post_date = str_replace('  ',' ',$post_date);
				$post_date = str_replace(',',' ',$post_date);
				$pd = explode(' ',$post_date);
				
				if(count($pd) == 4)
				{
					$date = dateEnText($pd[0]);
					$post_date = $date.' '.$pd[2];
				}
				else
				{
					//$p_view = $html->find('td[width=400] div',0);
					//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));
					//$page_view = trim($p_view->plaintext);
					//$page_view = onlyNum($page_view);

					//$page_view = explode("(",$page_view);
					//$view = explode (")",$page_view[1]);
					//$page_view = $view[0];

	//				$yy = thYear_decoder($pd[2]);
					$mm = enMonth_decoder($pd[0],'cut');

					$post_date = trim($pd[2])."-".$mm."-".trim($pd[1])." ".trim($pd[4]);
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
			$comments = $html->find('div[class=borderwrap] table[class=ipbtable]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($c->cellpadding == 4) continue;
				if($k==0) continue; // skip 
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				//$c_title = $c->find('b',0);
				//$comment_title = trim(iconv("CP874","utf-8",$c_title->plaintext));
				$comment_title = null;
				
				$c_body = $c->find('div[class=postcolor]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				//$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->find('span[class=normalname] a',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				//$comment_author = trim($c_author->plaintext);
				
				$c_date = $c->find('span[class=postdetails]',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date->plaintext));
				//$comment_date = trim($c_date->plaintext);
				
				
				// $str = explode("(",$comment_date);
				// $comment_date = $str[1];
				// $str = explode(")",$comment_date);
				// $comment_date = $str[0];
				
				//echo $comment_date."<br>";
				$comment_date = str_replace("  "," ",$comment_date);
				$comment_date = str_replace(',',' ',$comment_date);
				$cd = explode(' ',$comment_date);
				if(count($cd) == 4)
				{
					$date = dateEnText($pd[0]);
					$comment_date = $date.' '.$cd[2];
				}
				else
				{
					// $yy = thYear_decoder($cd[2]);
					$mm = enMonth_decoder($cd[0],'cut');

					$comment_date = trim($cd[2])."-".$mm."-".trim($cd[1])." ".trim($cd[4]);
				}
				
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