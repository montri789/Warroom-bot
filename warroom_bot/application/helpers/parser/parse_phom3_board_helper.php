<?php
	function parse_phom3_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_phom3_board';
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
		
				$main_content = $html->find('td h2',0);
				$post_title = trim($main_content->plaintext);
	
				$ptitle = explode("(",$post_title);
				$pview = explode(")",$ptitle[1]);
	
				$board_msg = $html->find('td div',0);
				$post_body = trim($board_msg->plaintext);
	
				$author = $html->find('td h4',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $html->find('.sample table[width=700] td',0);
				$post_date = trim($date_time->plaintext);
				
				$date = preg_split("/[\s,]+/",$post_date);
				$len = count($date);
				
				$day = substr($date[$len-5],-2,2);
				$day  = ereg_replace("[^0-9]", "",$day);
			
				$post_date = thYear_decoder($date[$len-3])."-". thMonth_decoder($date[$len-4],"cut")."-".$day." ".$date[$len-2];
					
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
			$comments = $html->find('.sample table[width=700] tbody');
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
				
					$c_title = $c->find('b',0);
					$comment_title = trim($c_title->plaintext);
					$comment_title = str_replace(array("(",")"),"",$comment_title);
								
					$c_body = $c->find('div[style=font-size:0.9em]',0);
					$comment_body = trim($c_body->plaintext);
					
					if(!empty($comment_body)) {
					
					$c_author = $c->find('h4 b',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('h4',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$c_date = preg_split("/[\s,]+/",$comment_date);
					
					$len = count($c_date);	
					$comment_date = thYear_decoder($c_date[$len-3])."-". thMonth_decoder($c_date[$len-4],"cut")."-".$c_date[$len-5]." ".$c_date[$len-2];
					
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
				}
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>