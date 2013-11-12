<?php
	function parse_somboonair($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_somboonair';

		
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
				$main_content = $html->find('div[id^=subject_]');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				// Post Body at div.content_only
				$board_msg = $html->find('td[class=windowbg] div[class=post]');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				// Post Meta at [no have]
				$author = $html->find('td[class=windowbg] tbody tr td');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('td[class=windowbg] div[class=smalltext]');
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time[1]->plaintext));
				$str = explode(" ",$post_date);
				$yy = $str[5];
				$mm = thMonth_decoder($str[3],'full');
				$dd = $str[4];
				$tt = $str[6]; 

				// View Count
				$page_info = $html->find('ul li.right');
				$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
				
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
			$comments = $html->find('td[class^=windowbg]');
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
				$c_title = $c->find('div[id^=subject_]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

				$c_body = $c->find('div[class=post]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

				$c_author = $c->find('tbody tr td',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

				$c_date_time = $c->find('div[class=smalltext]',1);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				$str = explode(" ",$comment_date);
				$yy = $str[6];
				$mm = thMonth_decoder($str[4],'full');
				$dd = $str[5];
				$tt = $str[7];
				
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