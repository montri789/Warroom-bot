<?php
	function parse_zoneza($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_digital2home';

		
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
				$main_content = $html->find('div[id=content] h1',0);
			if($main_content != null){
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
	
				$board_msg = $html->find('div[id=content]',0);
				$post_body = strip_tags(trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));
				
				$author = $html->find('div[id=postContentDesc] span[id=postbyname]',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));	
				
				$post_author = explode(": ",$post_author);
				$post_author = $post_author[1];
				
				$view = $html->find('div[id=postContentDesc] span[id=PageViewShw]',0);
				$post_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$view->plaintext));
				
				$post_view = explode(": ",$post_view);
				
				$date = $html->find('div[id=postContentDesc] span[id=postdate]',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date->plaintext));
	
				$pd = explode(" ",trim($post_date));
				$pt = explode("/",$pd[1]);
				$month = explode(",",$pt[1]);
				$day = explode(",",$pt[0]);
				$year = explode(",",$pt[2]);
				$years = thYear_decoder($year[0]);
				
				$post_date = $years."-".$month[0]."-".$day[0]." ".$pd[2];
				
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
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('.commentTB');
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
				$c_title = $c->find('.LeftTDcommentTB',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					$c_body = $c->find('.RightTDcommentTB .CommentMessShw',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					$comment_body = trim(str_replace("&nbsp;"," ",$comment_body));
					
					
					
					$c_author = $c->find('.RightTDcommentTB .PosterNameShw',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					$comment_author = trim(str_replace("โดย :","",$comment_author));
					
					$c_date = $c->find('.RightTDcommentTB .CommentDTShw',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date->plaintext));
					$comment_date = trim(str_replace(array("วันที่ ","/")," ",$comment_date));
					
					$cdate = explode(" ",$comment_date);

					$comment_date = thYear_decoder($cdate[2])."-".$cdate[1]."-".$cdate[0]." ".$cdate[3];

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