<?php
	function parse_108health_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_health_board';

		
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
				$main_content = $html->find('td b',0);
				$post_title = trim($main_content->plaintext);
	
				$ptitle = explode("(",$post_title);
				$pview = explode(")",$ptitle[1]);
	
				$board_msg = $html->find('.div_innerall td',0);
				$post_body = explode('<br />',$board_msg);
				$post_body = trim($post_body[1]);
	
				$author = $html->find('.font-02',0);
				$post_author = trim($author->plaintext);
				$post_author_ = explode(' ',$post_author);
				$post_author = str_replace('|','',$post_author_[2]);
	
				$post_date = $post_author_[5];
				
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
			$comments = $html->find('div[style=width:644px; margin:auto; margin-bottom:5px; border:#CCC solid 0px;]');
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
					$comment_title "";
				
				
					$c_body = $c->find('.bradius');
					$comment_body = trim($c_body[0]->plaintext);
					$comment_body1 = explode("โดย : ",$comment_body);
					$comment_body2 = str_replace("  ","",$comment_body1[0]);
					$comment_body3 = explode(" ",trim($comment_body2));
						unset($comment_body3[0]);		
					$comment_body = implode(" ",$comment_body3);
					$comment_body = substr($comment_body,2,strlen($comment_body));	
					
					$c_author = $c->find('.bradius',0);
					$comment_author = trim($c_author->plaintext);
					$comment_author = $comment_body1[1];
					$comment_author = explode("|",$comment_author);
					$comment_author = $comment_author[0];
					
					$c_date_time = $c->find('.bradius',0);
					$comment_date = trim($c_date_time->plaintext);
					$comment_date = explode("|",$comment_date);
					$cdate = str_replace("วันที่ : ","",$comment_date[1]);
					$ctime = str_replace("เวลา : ","",$comment_date[2]);
					$comment_date = $cdate.' '.$ctime;
					

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