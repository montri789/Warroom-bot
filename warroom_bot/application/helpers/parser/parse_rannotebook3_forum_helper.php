<?php
	function parse_rannotebook3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_rannotebook3_forum';
		$result = array('parse_ok'=>false,'posts'=>array());
		
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
		
				$main_content = $html->find('div[class=FontCaption]',0);
				//$post_title = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
				$post_title = trim($main_content->plaintext);
				$str = explode("[",$post_title);
				$post_title = trim($str[0]);

				$board_msg = $main_content->next_sibling();
				//$post_body = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
				$post_body = trim($board_msg->plaintext);

				$author = $board_msg->next_sibling()->find('b',0);
				//$post_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$post_author = trim($author->plaintext);
				
				// strip "By :"
				$str = explode(":",$post_author);
				$post_author = trim($str[1]);
				
				// Remove '(...)' from author
				// $found_pos = strpos($post_author,'(');
				// if($found_pos !== false) $post_author = trim(substr($post_author,0,$found_pos));

				$date_time = $board_msg->next_sibling();
				//$post_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$post_date = trim($date_time->plaintext);
				$str = explode("(",$post_date);
				$post_date = $str[2];
				$str = explode(")",$post_date);
				$post_date = $str[0];

				//echo $post_date."<br>";
				$post_date = str_replace("  "," ",$post_date);
				$post_date = str_replace('/',' ',$post_date);
				$pd = explode(' ',$post_date);

				//$p_view = $html->find('td[width=400] div',0);
				//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));
				//$page_view = trim($p_view->plaintext);
				//$page_view = onlyNum($page_view);

				//$page_view = explode("(",$page_view);
				//$view = explode (")",$page_view[1]);
				//$page_view = $view[0];

				$yy = thYear_decoder($pd[2]);
//				$mm = thMonth_decoder($pd[2]);

				$post_date = $yy."-".trim($pd[1])."-".trim($pd[0])." ".trim($pd[3]);
					
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
			$comments = $html->find('td[class=TabCenter] div[class=FontCaption] b');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if(!$result['parse_ok']) break;
				
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
				
					$c_title = $c->find('b',0);
				//$comment_title = trim(iconv("CP874","utf-8",$c_title->plaintext));
				$comment_title = trim($c_title->plaintext);
				
				$c_body = $c->next_sibling();
				//$comment_body = trim(iconv("CP874","utf-8",$c_body->plaintext));
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c_body->next_sibling()->find('b',0);
				//$comment_author = trim(iconv("CP874","utf-8",$c_author->plaintext));
				$comment_author = trim($c_author->plaintext);
				
				// strip "By :"
				$str = explode(":",$comment_author);
				$comment_author = trim($str[1]);
				
				$c_date = $c_body->next_sibling();
				//$comment_date = trim(iconv("CP874","utf-8",$c_date->plaintext));
				$comment_date = trim($c_date->plaintext);
				
				$str = explode("(",$comment_date);
				$comment_date = $str[1];
				$str = explode(")",$comment_date);
				$comment_date = $str[0];
				
				//echo $comment_date."<br>";
				$comment_date = str_replace("  "," ",$comment_date);
				$comment_date = str_replace('/',' ',$comment_date);
				$cd = explode(' ',$comment_date);

				$yy = thYear_decoder($cd[2]);
				// $mm = thMonth_decoder($cd[2]);
				
				$comment_date = $yy."-".trim($cd[1])."-".trim($cd[0])." ".trim($cd[3]);
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
						echo "validate comment failed . . .";
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