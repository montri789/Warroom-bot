﻿<?php
	function parse_showded_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_showded_board';

		
		$html = str_get_html($fetch);
		
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
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

		$dead_page = $html->find('div[class=thankyou-txt box-round5] p',0);
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
				$main_content = $html->find('div[class=pl bm] h1 a',0);
				$post_title = trim($main_content->plaintext);

				// Post Body at div.lyriccontent
				$board_msg = $html->find('div[class=pcb] td[class=t_f] ');
				$post_body = trim($board_msg[0]->plaintext);;

				// Post Meta at 
				$author = $html->find('div[class=authi] a',0);
				$post_author = trim($author->plaintext);;

				$date_time = $html->find('div[class=authi] em',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
				$pdate = explode("-",$date[1]);

				// View Count
				$page_info = $html->find('div[class=hm] span[class=xi1]',0);
				$page_view = trim($page_info->plaintext);
				
				$post_date = $pdate[2]."-".$pdate[1]."-".$pdate[0]." ".$date[2];
				
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
					$post->post_date = mdate('%Y-%m-%d %H:%i',time());
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					$post->insert();
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[id^=pid]');
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

				//Comment Title as div.listCommentHead
				$c_title = $c->find('a[class=xnum]',0);
				$comment_title = trim($c_title->plaintext);

				//Comment Body as div.commentBox div.boardmsg
				$c_body = $c->find('td[class=t_f]');
				$comment_body = trim($c_body[0]->plaintext);

				//Comment Author as ui#ownerdetail li b
				$c_author = $c->find('div[class=authi] a',0);
				$comment_author = trim($c_author->plaintext);

				//Comment Date ul#ownerdetail li
				$c_date_time = $c->find('div[class=authi] em',0);
				$comment_date = trim($c_date_time->plaintext);

				$date = explode(" ",$comment_date);
				$cdate = explode("-",$date[1]);

				//$date = explode(" ",$comment_date);
				$yy = thYear_decoder($cdate[5]);
				$mm = thMonth_decoder($cdate[4],'full');
				
				$comment_date = $cdate[2]."-".$cdate[1]."-".$cdate[0]." ".$date[2];
				
				if(!empty($comment_author)){
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
					//$post->insert();
		
                                        // add obj to memcache
                                        $key = rand(1000,9999).'-'.microtime(true);
                                        $memcache->add($key, $post, false, 12*60*60) or die ("Failed to save OBJECT at the server");
                                        echo '.';
                                        unset($post);
				}
			//$i++;
		}
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>