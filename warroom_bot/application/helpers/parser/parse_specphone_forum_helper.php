<?php
	function parse_specphone_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_specphone_forum';

		
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

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('.title',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('.content',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

				$author = $html->find('div.userinfo a',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));

				$date_time = $html->find('div.posthead span.date',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$date = explode("&nbsp;",str_replace(array("AM","PM"),"",$post_date));

				if(trim($date[0]) == "วันนี้" || trim($date[0]) == "เมื่อวานนี้"){
					$date[1] = str_replace(" ","",$date[1]);
					$post_date = dateThText($date[0])." ".$date[1];	
				}else{
					$day = explode("-",$date[0]);
					$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
					$date[1] = str_replace(" ","",$date[1]);
					$post_date = $y."-".$day[0]."-".$day[1]." ".$date[1];
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
			$comments = $html->find('li[id^=post_]');
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

					$c_title = $c->find('div.posthead span.nodecontrols a',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$c_body = $c->find('div.content',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
		
					$c_author = $c->find('div.userinfo a',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $c->find('div.posthead span.date',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));					
					
					$date = explode("&nbsp;",str_replace(array("AM","PM"),"",$comment_date));
				
					if(trim($date[0]) == "วันนี้" || trim($date[0]) == "เมื่อวานนี้"){
						$date[1] = str_replace(" ","",$date[1]);
						$comment_date = dateThText($date[0])." ".$date[1];	
					}else{ 	
						$day = explode("-",$date[0]);
						$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
						$date[1] = str_replace(" ","",$date[1]);
						$comment_date = $y."-".$day[0]."-".$day[1]." ".$date[1];
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
			$i++;
		}
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>