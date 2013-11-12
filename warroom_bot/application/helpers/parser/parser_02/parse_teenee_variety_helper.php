<?php
	function parse_teenee_variety($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_teenee_variety';

		
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0)
			{
				$main_content = $html->find('table[width=725] td[bgcolor=#ECFAE0]',0);
				if(empty($main_content)){
					$main_content = $html->find('table[width=670] td b',0);
				}
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('table[width=725] table[width=100%] table[width=95%] tr',0);
				if(empty($board_msg)){
					$board_msg = $html->find('table[width=670] .A2',0);
				}
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div[align=center] table[width=725] td[bgcolor=#ffffff]',0);
				$post_author = trim($author->plaintext);

				$author = explode("โดย :",$post_author);
				$pauthor = explode("โพสเมื่อ",$author[1]);
				$date = explode("[",$pauthor[1]);
				$pdate = explode("]",$date[1]);
				$fdate = explode(" ",$pdate[0]);

				$post_author = $pauthor[0];

				$yy = thYear_decoder($fdate[5]);
				$mm = thMonth_decoder($fdate[4],'full');
				$post_date = $yy."-".$mm."-".$fdate[3]." ".$fdate[7];

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
			$comments = $html->find('.three');
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

						$c_title = $c->find('td[bgcolor=#F5F5F5]',0);
						$comment_title = trim($c_title->plaintext);

						$ctitle = explode("[",$comment_title);
						$comment_title = $ctitle[0];

						$c_body = $c->find('td[bgcolor=#F5F5F5] table[width=95%]');
						$comment_body = trim($c_body[0]->plaintext);

						$c_author = $c->find('table[width=100%] td font',0);
						$comment_author = trim($c_author->plaintext);

						$c_date_time = $c->find('table[width=100%] td[width=150]',0);
						$s = $c_date_time->parent()->next_sibling();
						$c_date_time = $s->find('td font',0);

						$comment_date = trim($c_date_time->plaintext);

						$adate = explode("[",$comment_date);
						$time = explode("]",$adate[1]);
						$cdate = explode(" ",$time[0]);

						$yy = thYear_decoder($cdate[5]);
						$mm = thMonth_decoder($cdate[4],'full');
						$comment_date = $yy."-".$mm."-".$cdate[3]." ".$cdate[7];
				
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