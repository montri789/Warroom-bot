<?php
	function parse_kapook_football($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_kapook_football';

		
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
				$main_content = $html->find('.tab_score');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('td[width=86%]',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));			
				
				
				$author = $html->find('table[width=670] table[width=100%] td',2);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$post_author = str_replace(array("::","&nbsp;"),"",$post_author);
				$str = explode(":",trim($post_author));
				
				if(!isset($str[1])){ 
					$author = $html->find('table[width=670] table[width=100%] td[width=86%]',0);
					$author  = $author->parent()->next_sibling();
					$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	
					$post_author = str_replace(array("::","&nbsp;"),"",$post_author);
					$str = explode(":",trim($post_author));	
				}
				
				$post_author = trim($str[1]);
				
				$date = explode(" ",trim($str[2]));
								
				$cd = COUNT(explode(".",$date[1]));		
				
				$month = ($cd > 2) ? thMonth_decoder($date[1],"cut") : thMonth_decoder3($date[1],"cut");
				
				$post_date = thYear_decoder($date[2])."-".$month."-".$date[0];
			
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
			$comments = $html->find('.table_00');
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
				$c_title = $c->find('.table_01',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));						
				$comment_title = trim(str_replace("&nbsp;"," ",$comment_title));
				
				$c_body = $c->find('td[width=86%]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				$comment_body = trim(str_replace("&nbsp;"," ",$comment_body));

				$c_author = $c->find('table[width=100%] td',2);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				$comment_author = str_replace(array("::","&nbsp;"),"",$comment_author);
				$str = explode(":",trim($comment_author));

				$comment_author = trim($str[1]);

				$date = explode(" ",trim($str[2]));
				
				$cd = COUNT(explode(".",$date[1]));
				$month = ($cd > 2) ? thMonth_decoder($date[1],"cut") : thMonth_decoder3($date[1],"cut");
				
				
				$comment_date = thYear_decoder($date[2])."-".$month."-".$date[0];

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