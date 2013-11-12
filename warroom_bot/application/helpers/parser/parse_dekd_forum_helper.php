<?php
	function parse_dekd_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_dekd_forum';
		
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

		$dead_page = $html->find('img[src^=http://image.dek-d.com/1/underconstruction]',0);
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
				// Post Title at div.maincontent, 1st h1 element
				$main_content = $html->find('div[class=maincontent] h1');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
//				echo PHP_EOL.'post_title:'.$post_title;

				// Post Body at div.boardmsg
				$board_msg = $html->find('div[class=boardmsg]');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				// Post Meta at ul#ownerdetail
				$author = $html->find('ul[id=ownerdetail] li b');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('ul[id=ownerdetail] li',2);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				// View Count
				$page_info = $html->find('div[class=maincontent] p span strong');
				$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

				$date = explode(" ",$post_date);
				$yy = thYear_decoder($date[4]);
				$mm = thMonth_decoder($date[3],'full');
				$dd = $date[2];
				$tt = $date[6];
				
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

			// Comments at 
			$comments = $html->find('ul[id=listComment] li[class=bd1soCCC]');
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

				$c_title = $c->find('div[class=listCommentHead]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
				$c_body = $c->find('div[class=commentBox] div[class=boardmsg]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('#ownerdetail li b',0);
				
				if(!empty($c_author)){
				
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $c->find('ul[id=ownerdetail] li',2);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
					
					$date = explode(" ",$comment_date);
					$yy = thYear_decoder($date[4]);
					$mm = thMonth_decoder($date[3],'full');
					$dd = $date[2];
					$tt = $date[6];
					
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
					
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
				}
				$i++;
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>