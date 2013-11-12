<?php
	function parse_clipmass_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_clipmass_forum';
		
		$html = str_get_html($fetch);
		
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
				$main_content = $html->find('div[class=big-text]',0);
				//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
				$post_title = trim($main_content->plaintext);
				$post_title = str_replace('กระทู้: ','',$post_title);

				$board_msg = $html->find('div[class=detail]',2);
				//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div[class=small] b');
				//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('div[class=small]',1);
				//$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$post_date = trim($date_time->plaintext);

				//echo $post_date."<br>";
				$post_date = str_replace("  "," ",$post_date);
				$pd = explode(" ",$post_date);

				//$p_view = $html->find('td[width=400] div',0);
				//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));
				//$page_view = trim($p_view->plaintext);
				//$page_view = onlyNum($page_view);

				//$page_view = explode("(",$page_view);
				//$view = explode (")",$page_view[1]);
				//$page_view = $view[0];

				$yy = thYear_decoder($pd[3]);
				$mm = thMonth_decoder($pd[2]);

				$post_date = $yy."-".$mm."-".trim($pd[1])." ".trim($pd[5]);
				
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
			$comments = $html->find('div[class=label]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				$c = $c->parent()->parent();
				
				//if($k==0) continue; // skip 
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				$c_title = $c->find('div[class=label]',0);
				//$comment_title = trim(iconv("tis-620","utf-8",$c_title->plaintext));
				$comment_title = trim($c_title->plaintext);
				
				$c_body = $c->find('div[class=detail]',0);
				//$comment_body = trim(iconv("tis-620","utf-8",$c_body->plaintext));
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->find('div[class=small] b',0);
				//$comment_author = trim(iconv("tis-620","utf-8",$c_author->plaintext));
				$comment_author = trim($c_author->plaintext);
				
				$c_date_post = count($c_body->parent()->children())-1;
				$c_date = $c_body->parent()->children($c_date_post);
				//$comment_date = trim(iconv("tis-620","utf-8",$c_date->plaintext));
				$comment_date = trim($c_date->plaintext);
				
				//echo $comment_date."<br>";
				$cd = explode(" ",$comment_date);

				$yy = thYear_decoder($cd[3]);
				$mm = thMonth_decoder($cd[2]);
				
				$comment_date = $yy."-".$mm."-".$cd[1]." ".$cd[5];


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
					$post->insert();
					unset($post);
				}
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
?>