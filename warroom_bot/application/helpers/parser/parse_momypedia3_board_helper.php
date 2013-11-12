<?php
	function parse_momypedia3_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_momypedia3_board';
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
		
				$main_content = $html->find('h1.txt16',0);
				$post_title = trim($main_content->plaintext);

				// $ptitle = explode("(",$post_title);
				// $pview = explode(")",$ptitle[1]);

				$board_msg = $html->find('.txt14',1);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('span[title^=IP:]',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('table[width=644] .divLayout',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);

				$time = @date("H:i", strtotime($date[4]." ".$date[5]));
				$post_date = thYear_decoder($date[3])."-". thMonth_decoder($date[2],"cut")."-".str_replace(",","",$date[1])." ".$time;
					
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
			$comments = $html->find('div.divLayout td[width=130]');
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
				
					$next_date = $c->next_sibling()->next_sibling();
					$next_body = $c->parent()->next_sibling()->next_sibling();
					
			
					$comment_title = "";
				
					$c_body = $next_body->find('.txt14');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('span[title^=IP:]',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $next_date->find('.divLayout',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$cdate = explode(" ",$comment_date);
				
					$ctime = @date("H:i", strtotime($cdate[4]." ".$cdate[5]));
					$comment_date = thYear_decoder($cdate[3])."-". thMonth_decoder($cdate[2],"cut")."-".str_replace(",","",$cdate[1])." ".$ctime;
				
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