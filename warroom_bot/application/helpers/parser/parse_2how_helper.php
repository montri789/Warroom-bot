<?php
	function parse_2how($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_2how';

		
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				// Post Title at div.clear-list-mar, 1st h1 element
				$main_content = $html->find('h1[class=ipsType_pagetitle]');
				$post_title = trim($main_content[0]->plaintext);

				// Post Body at div.content_only
				$board_msg = $html->find('div[class=post entry-content]');
				$post_body = trim($board_msg[0]->plaintext);

				// Post Meta at [no have]
				$author = $html->find('span[class=author vcard] a');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('abbr[class=published]');
				$post_date = trim($date_time[1]->plaintext);


				// View Count
				$page_info = $html->find('li[class=statistics_counter last]');
				$page_view = trim($page_info[0]->plaintext);
				$page_view = preg_replace("/[^0-9]/", '',$page_view);
				$date = explode(" ",$post_date);
				$yy = $date[2];
				$mm = $date[1];
				$dd = $date[0];
				$tt = $date[4];
				$ampm =$date[5];
				
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
			$comments = $html->find('div[id^=post_id_]');
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
				$c_title = $c->find('a[title^=Link to post #]',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div[class=post entry-content]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('span[class=author vcard]',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('abbr[class=published]',0);
				$comment_date = trim($c_date_time->plaintext);

				$date = explode(" ",$comment_date);
				$yy = $date[2];
				$mm = $date[1];
				$dd = $date[0];
				$tt = $date[4];
				$ampm =$date[5];
				
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