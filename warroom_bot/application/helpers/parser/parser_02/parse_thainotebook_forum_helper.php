<?php
	function parse_thainotebook_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thainotebook_forum';

		
		$html = str_get_html($fetch);
		
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		‘
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
				if(empty($c->parent()->prev_sibling()->children(0)->class)) continue;
				$main_content = $html->find('div[id=bodyarea] div.nav a');
				$post_title = trim($main_content[3]->plaintext);

				$board_msg = $html->find('div[id=bodyarea] div.post');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('div[id=bodyarea] td.windowbg b');
				$post_author = trim($author[1]->plaintext);

				$date_time = $html->find('div[id=bodyarea] td.windowbg div.smalltext',1);
				$post_date = trim($date_time->plaintext);

				$pdate = explode(" ",$post_date);

				if(trim($pdate[3]) == "เมื่อวานนี้" || trim($pdate[3]) == "วันนี้"){
					$post_date = dateThText($pdate[3])." ".$pdate[5];	
				}else{	
					$year = explode(",",$pdate[5]);
					$day = explode(",",$pdate[4]);

					$mm = thMonth_decoder($pdate[3],'full');
					$post_date = $year[0]."-".$mm."-".$day[0]." ".$pdate[6];
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
			$comments = $html->find('table[cellpadding=3] td[class^=windowbg]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				//echo $k.':'.$c->outertext."<hr/>";
				//$c->parent()->prev_sibling()->children(0)->class;
				//if(empty($c->parent()->prev_sibling()->children(0)->class)) continue;
				
				if($k < 3) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('table[width=100%] div.smalltext b',0);
				if(empty($c_title)) continue;
				$comment_title = trim($c_title->plaintext);
				

				//$ctitle = explode(" ",$comment_title);
				//$comment_title = $ctitle[1];
		
				$c_body = $c->find('div.post',0);
				$comment_body = trim($c_body->plaintext);
		
				$c_author = $c->find('b',0);
				$comment_author = trim($c_author->plaintext);
		
				$c_date_time = $c->find('div.smalltext',1);
				$comment_date = trim($c_date_time->plaintext);

				$cdate = explode(" ",$comment_date);
				
				if(trim($cdate[4]) == "เมื่อวานนี้" || trim($cdate[4]) == "วันนี้"){
					$comment_date = dateThText($cdate[4])." ".$cdate[6];	
				}else{	
					$year = explode(",",$cdate[6]);
					$day = explode(",",$cdate[5]);
					$cmm = thMonth_decoder($cdate[4],'full');
					$comment_date = $year[0]."-".$cmm."-".$day[0]." ".$cdate[7];
				}

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