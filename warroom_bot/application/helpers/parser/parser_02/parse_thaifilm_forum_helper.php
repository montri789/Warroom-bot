<?php
	function parse_thaifilm_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaifilm_forum';
		
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
		
				$main_content = $html->find('span.redH',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($main_content->plaintext)));
	
				$board_msg = $html->find('span.white',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($board_msg->plaintext)));
				$post_body = explode("จาก: ",$post_body);
				$post_body = $post_body[0];
	
				$author = $html->find('span.white',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($author->plaintext)));
				$post_author = explode("จาก: ",$post_author);
				$post_author = $post_author[1];
				$post_author = explode("วันที่",$post_author);
				$post_author = $post_author[0];
			
				$date_time = $html->find('span.white',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($date_time->plaintext)));
				$post_date = explode("วันที่: ",$post_date);
				$post_date = $post_date[1];
				$post_date = explode(" น.",$post_date);
				$post_date = $post_date[0];
				$post_date = str_replace(array("/","-")," ",$post_date);
				$date = explode(" ",$post_date);
				$post_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[5];
				
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
			$comments = $html->find('table[width=100%] td[colspan=2]');
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
				
				$c_title = $c->find('b.grey',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_title->plaintext)));
			
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c->plaintext)));
				$comment_body = explode(" ",$comment_body);
				unset($comment_body[0]);
				unset($comment_body[1]);
				$comment_body = implode(" ",$comment_body);
				$comment_body = explode(" จาก: ",$comment_body);
				$comment_body = $comment_body[0];
				
				$c_author = $c->find('a.Board2',0);
				if(empty($c_author)) {
					$c_author = $c->find('span.grey',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_author->plaintext)));
					$comment_author = explode(" ",$comment_author);
					$comment_author = $comment_author[1];
				} else 
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_author->plaintext)));
				
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c->plaintext)));
				$comment_date = explode("วันที่: ",$comment_date);
				$comment_date = $comment_date[1];
				$comment_date = explode(" น.",$comment_date);
				$comment_date = $comment_date[0];
				$comment_date = str_replace(array("/","-")," ",$comment_date);
				$cdate = explode(" ",$comment_date);
				$comment_date = thYear_decoder($cdate[2])."-".$cdate[1]."-".$cdate[0]." ".$cdate[5];
				
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