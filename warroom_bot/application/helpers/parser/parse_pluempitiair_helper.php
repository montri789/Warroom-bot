<?php
	function parse_pluempitiair($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_pluempitiair';

		
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
				$main_content = $html->find('td[class=subject]');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('.block-middle td[align=left]');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('.poster span');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('.post-details span');
				$post_date = trim($date_time[0]->plaintext);

				$time_date = $html->find('.post-details span',1);
				$post_time = trim($time_date->plaintext);

				$post_time = preg_split("/[\s,]+/",$post_time);			
				$pd = explode("/",$post_date);
	
				$tt = $post_time[1];
				$dd = $pd[0];
				$mm = $pd[1];
				$yy = ($pd[2] < 2554) ? $pd[2] : thYear_decoder($pd[2]);
				
				$post_date = $yy."-".$mm."-".$dd." ".$tt;
				
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

			$comments = $html->find('td[class^=windowbg]');
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($k==0) continue;
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
				$c_title = $c->find('.post-order',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('td[align=left]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('.poster span',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('.post-details span',1);
				$comment_date = trim($c_date_time->plaintext);

				$c_time = $c->find('.post-details span',2);
				$comment_time = trim($c_time->plaintext);
				$comment_time = preg_split("/[\s,]+/",$comment_time);	
				
				$cd = explode("/",$comment_date);
				$time = $comment_time[1];
				$dd = $cd[0];
				$mm = $cd[1];		
				$yy = ($cd[2] < 2554) ? $cd[2] : thYear_decoder($cd[2]);
		
				$comment_date = $yy."-".$mm."-".$dd." ".$time;
				
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
				$i++;
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>