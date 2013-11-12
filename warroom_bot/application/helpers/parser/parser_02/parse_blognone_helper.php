<?php
	function parse_blognone($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_blognone';
		
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
		
				$post = $html->find('div[class=content-container] div[class=node]',0);
				$post_title = $post->find('h2',0)->plaintext;
				$post_body = $post->find('div[itemprop=description]',0)->plaintext;
				$post_meta = $post->find('div[class=meta] span',0)->plaintext;
				$meta = explode(" ",$post_meta);
	//			var_dump($meta);
				$date_index = 0;
				for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
				$date = explode("/",$meta[$date_index]);
				$yy = (int)$date[2] + 2000;
				$mm = $date[1];
				$dd = $date[0];
				$tt = $meta[$date_index+1];
				$post_author = $meta[1];
				for($i=2;$i<$date_index-2;$i++) { $author = $author.' '.$meta[$i]; }
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
					$post->insert_cache($memcache);
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('div[id=comments] div[class=comment]');
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
				
				$comment_title = null;
				
				$comment_meta = $c->find('div[class=comment-info]',0)->plaintext;
				$comment_body = $c->find('div[class=comment-content]',0)->plaintext;

				$meta = explode(" ",$comment_meta);
//				var_dump($meta);
				$date_index = 0;
				for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
				$date = explode("/",$meta[$date_index]);
				$yy = (int)$date[2] + 2000;
				$mm = $date[1];
				$dd = $date[0];
				$tt = $meta[$date_index+1];
				$comment_author = $meta[6];
				for($i=7;$i<$date_index-2;$i++) { $comment_author = $comment_author.' '.$meta[$i]; }
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
				$i++;
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>