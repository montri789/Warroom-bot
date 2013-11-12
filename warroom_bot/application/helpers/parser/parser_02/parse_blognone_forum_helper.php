<?php
	function parse_blognone_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_blognone_forum';

		
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
				$main_content = $html->find('div[class=content-title-box] h2');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('div[class=content] div[itemprop=description]');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('div[class=meta] span[class=submitted]');
				$post_author = trim($author[0]->plaintext);

				$page_info = $html->find('ul[class=links inline]');
				$page_view0 = trim($page_info[1]->plaintext);

				$page_view = preg_replace("/[^0-9]/", '',$page_view0);

				$str= explode(" on ",$post_author);  
				echo "count=".nl2br(count($str));
				$pauthor = str_replace("By:",NULL,$str[0]);
				$post_author = $pauthor;  
				$date = $str[1];
				//$tt = $str[2];

				$str2 = explode("/",$date);
				$pyear = explode(" ",$str2[2]);
				$pday = explode(" ",$str2[0]);
				$yy = $pyear[0];
				$mm = $str2[1];
				$dd = $pday[0];
				$post_date = $yy."-".$mm."-".$dd." ".$pyear[1];
				
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
			$comments = $html->find('div[class=comment]');
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

				$c_title = $c->find('a.comment-target',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div[class=comment-content]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('div[class=comment-info]',0);
				$comment_author = trim($c_author->plaintext);
				$str= explode(" ",$comment_author);
				$comment_author = $str[1];  

				$c_date_time = $c->find('div[class=comment-info]',0);
				$comment_date = trim($c_date_time->plaintext);

				$str = explode(" on ",$comment_date);
				$str = explode(" ",$str[1]);

				$comment_title = $str[2];
				$date = explode("/",$str[0]);

				$comment_date = $date[2]."-".$date[1]."-".$date[0]." ".$str[1];

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