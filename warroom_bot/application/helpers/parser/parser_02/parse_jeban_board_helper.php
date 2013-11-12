	<?php
	function parse_jeban_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_jeban_board';

		
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

		$dead_page = $html->find('table[id=Table_01]',0);
		if($dead_page == null) $dead_page = $html->find('div[class^=thankyou-txt]');
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
				$main_content = $html->find('div[class=topic-right] h1',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div[id=message]',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div[class=personal-post] p',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('div[class=topic-right] p',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);

				$yy = thYear_decoder($date[2]);
				$mm = thMonth_decoder2($date[1],'full');
				$post_date = $yy."-".$mm."-".$date[0]." ".$date[3];

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
			$comments = $html->find('div[class^=comment-show]');
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

				$c_title = $c->find('.com-detail p',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div[class=comment-post] div[id=msn-section]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('.com-detail p',1);
				$comment_author = trim(str_replace("by ","",trim($c_author->plaintext)));

				$c_date = $c->find('.com-detail p',3);
				$comment_date = trim($c_date->plaintext);

				$c_time = $c->find('.com-detail p',2);
				$comment_time = trim($c_time->plaintext);

				$date = explode (" ",$comment_date);
				$comment_date = thYear_decoder($date[2])."-".
								thMonth_decoder($date[1],"cut")."-".$date[0]." ".$comment_time; 
				
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