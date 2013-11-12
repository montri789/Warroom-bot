<?php
	function parse_headlightmag3($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_headlightmag3';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$html = str_get_html($fetch);
		
		$html = str_get_html($fetch);
		if(is_null($html))
		{
			echo 'NULL!!!';
			$result['posts'] []= null;
			$result['parse_ok'] = false;
			return $result;
		}
		
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
		
				$main_content = $html->find('title',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('.post',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

				$author = $html->find('.windowbg td[width=16%] b',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				
				$date_time = $html->find('td[width=85%] .smalltext',0);
				$post_date = str_replace(array("เมื่อ:",","),"",trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext)));

				$post_date = explode(" ",$post_date);

					
					$dd = $post_date[4];
					$mm = thMonth_decoder($post_date[3],"full");
					$yy = $post_date[5];
					$tt = $post_date[6];	

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
					$post = new Post_model2();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_body;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					if($post->validate()){
						$result['posts'] []= $post;
						$result['parse_ok'] = true;
					}
					else{
						$result['parse_ok'] = false;
						echo "validate post failed";
					}
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('td[class=windowbg2]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if(!$result['parse_ok']) break;
				
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
					$c_title = $c->find('div[id^=subject_]',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					$c_body = $c->find('.post',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

					$c_author = $c->find('div[class=profile] a',0);
					if($c_author == null)
						$c_author = $c->find('div[class=profile] b',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

					$c_date_time = $c->find('td[width=85%] .smalltext',0);
					$comment_date = str_replace(array("เมื่อ:",","),"",trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext)));

					$date = explode(" ",$comment_date);


						$dd = $date[5];
						$mm = thMonth_decoder($date[4],"full");
						$yy = $date[6];
						$tt = $date[7];	

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
					$post = new Post_model2();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "comment";
					$post->title = $comment_title;
					$post->body = trim($comment_body);
					$post->post_date = $comment_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($comment_author));
					if($post->validate())
					{
						$result['posts'] []= $post;
						$result['parse_ok'] = true;
					}
					else{
						$result['parse_ok'] = false;
						echo "validate comment failed";
					}
					//$post->insert();
					
					unset($post);
				}
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>