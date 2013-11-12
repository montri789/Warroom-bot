<?php
	function parse_kobkid3($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_kobkid3';
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
		
				$main_content = $html->find('th[colspan=2] a');
				$post_title = trim($main_content[0]->plaintext);
				//$main_content = $html->find('div[class=maincontent] h1');
				//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				// Post Body at div.boardmsg
				$board_msg = $html->find('div[class=data]');
				$post_body = trim($board_msg[0]->plaintext);
				//$board_msg = $html->find('div[class=boardmsg]');
				//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				// Post Meta at ul#ownerdetail
				$author = $html->find('span[class=user]');
				$post_author = trim($author[0]->plaintext);
				//$author = $html->find('ul[id=ownerdetail] li b');
				//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('span[class=post_date]');
				$post_date = trim($date_time[0]->plaintext);
				$post_date  = str_replace("เมื่อ ","",$post_date);
					
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
			$comments = $html->find('.data');
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
				
					$n = $c->parent()->prev_sibling();
				
					$comment_title = "#".($i+1);
					
					
					$comment_body = trim($c->plaintext);
	
					$c_author = $n->find('.user');
					$comment_author = trim($c_author[0]->plaintext);
	
					$t = $c->prev_sibling();
					$c_date_time = $t->find('.post_date',0);
					$comment_date = trim($c_date_time->plaintext);
					$comment_date = str_replace("เมื่อ ","",$comment_date);
				
				
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