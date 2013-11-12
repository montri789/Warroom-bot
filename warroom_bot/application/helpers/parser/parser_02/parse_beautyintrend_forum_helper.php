<?php
	function parse_beautyintrend_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_beautyintrend_forum';

		
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
		
				$main_content = $html->find('.post_header',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
				$post_title = str_replace('&nbsp;&nbsp;&nbsp;หัวข้อ : ','',$post_title);
	
				$board_msg = $html->find('.post_text_style',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
	
				$author = $html->find('.post_footer_stat',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$post_author = explode('(',$post_author);
				$post_author = $post_author[0];
	
				$date_time = $html->find('td[width=15]',0);
				$date_time = $date_time->next_sibling();
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
				$date = explode('&nbsp;&nbsp;',$post_date);
				$date2 = explode(' ',$date[1]);
				$date3 = explode('/',$date2[1]);
				$d = $date3[0];
				$m = $date3[1];
				$y = $date3[2];
				$t = $date2[0];			
				$post_date = $y.'-'.$m.'-'.$d.' '.$t;
				
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
			$comments = $html->find('center table[width=100%]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($k==0 || $k==1) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
				$comment_title = '';
				
				$c_body = $c->find('.reply_text_style',0);
				$comment_body = str_replace('&nbsp;&nbsp;  		','',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext)));
				$c_author = $c->find('td[width=15]',0);
				$c_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->next_sibling()));

				$temp = explode('&nbsp;&nbsp;',$c_author);
				$comment_author = $temp[0];
				$comment_date = $temp[1];					

				$date = explode(' ',$comment_date);
				$date2 = explode('/',$date[1]);
				$d = $date2[0];
				$m = $date2[1];
				$y = str_replace('			<','',$date2[2]);
				$t = $date[0];
				$comment_date = $y.'-'.$m.'-'.$d.' '.$t;
				
				
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