<?php
	function parse_siamha_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_siamha_board';

		
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
				$main_content = $html->find('div[id^=subject_]',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('.post',0);
				$post_body = htmlentities(trim($board_msg->plaintext),ENT_IGNORE,'utf-8');

				$author = $html->find('.windowbg td[width=16%] b',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('td[width=85%] .smalltext',0);
				$post_date = str_replace(array("เมื่อ:",","),"",trim($date_time->plaintext));

				$post_date = explode(" ",trim($post_date));

				if(trim($post_date[3]) == "เมื่อวานนี้" || trim($post_date[3]) == "วันนี้"){
					$post_date = dateThText($post_date[3])." ".$post_date[5];
				}else{		
					$dd = $post_date[4];
					$mm = thMonth_decoder($post_date[3],"full");
					$yy = $post_date[5];
					$tt = $post_date[6];	

					$post_date = $yy."-".$mm."-".$dd." ".$tt;
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
					$post->post_date = mdate('%Y-%m-%d %H:%i',time());
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
				$comment_title = trim($c_title->plaintext);
				
				$c_body = $c->find('.post',0);
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->find('td[width=16%] b',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('td[width=85%] .smalltext',0);
				$comment_date = str_replace(array("เมื่อ:",","),"",trim($c_date_time->plaintext));
				
				//echo $comment_date."<br>";
				
				$date = explode(" ",$comment_date);
	
				if(trim($date[4]) == "เมื่อวานนี้" || trim($date[4]) == "วันนี้"){
					$comment_date = dateThText($date[4])." ".$date[6];
				}else{		
					$dd = $date[5];
					$mm = thMonth_decoder($date[4],"full");
					$yy = $date[6];
					$tt = $date[7];	
							
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				}		
				
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