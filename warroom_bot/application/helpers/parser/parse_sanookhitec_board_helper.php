<?php
	function parse_sanookhitec_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_sanookhitec_board';

		
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
				$main_content = $html->find('div[id^=subject_]',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('.post',0);
				$post_body = trim($board_msg->plaintext);
				
				$author = $html->find('.windowbg div div',1);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('.smalltext',1);
				$post_date = trim($date_time->plaintext);
				
				$post_date = str_replace(array("เมื่อ: ",",","&nbsp;"),"",$post_date);
				$post_date = str_replace(array("   ","  ")," ",$post_date);
				$date = explode(" ",trim($post_date)); 
	
				if(trim($date[1]) == "เมื่อวานนี้" || trim($date[1]) == "วันนี้"){		
					$post_date = dateThText($date[1])." ".$date[2];
				}else{
					$post_date = $date[3]."-".thMonth_decoder($date[2])."-".trim($date[1])." ".$date[4];
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
				}else
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
			$comments = $html->find('#quickModForm td[class^=windowbg]');
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

				$c_title = $c->find('div[id^=subject_]',0);
				$comment_title = trim($c_title->plaintext);
				
				if(!empty($comment_title)){
					$c_body = $c->find('.post',0);
					$comment_body = trim($c_body->plaintext);
					
					$c_author = $c->find('div div',1);
					$comment_author = trim($c_author->plaintext);
					
					if(empty($comment_author)){
						$c_author = $c->find('div.smalltext',0);
						$comment_author = trim($c_author->plaintext);
					}
					
					$c_date_time = $c->find('td.smalltext',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$comment_date = str_replace(array("ตอบ ",",","&nbsp;"),"",$comment_date);
					$comment_date = str_replace("  "," ",$comment_date);   			 
					 
					$date = explode(" ",trim($comment_date));
							
					if(trim($date[3]) == "เมื่อวานนี้" || trim($date[3]) == "วันนี้"){		
						$comment_date = dateThText($date[2])." ".$date[3];
					}else{
						$comment_date = $date[5]."-".thMonth_decoder($date[4])."-".$date[3]." ".$date[6];
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
						echo "<br>";
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
			//$i++;
				}
			}
		}
		$html->clear();
		unset($html);
	}
?>