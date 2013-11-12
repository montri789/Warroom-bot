<?php
	function parse_pooyingnaka3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_pooyingnaka3_forum';
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

		$dead_page = $html->find('.redbold',0);
		if(trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$dead_page->plaintext)) == 'กระทู้นี้ถูกปิด'){
			
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
		
				$main_content = $html->find('img[src=pic/nav_m.gif]',1);
				$main_content = $main_content->next_sibling();
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",str_replace(array("“","”"),'',$main_content->plaintext)));

				$board_msg = $html->find('table[cellpadding=3] tbody tr td .blackpost',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
	
				$author = $html->find('.pinkdarkbold',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	
				$date_time = $html->find('img[src=pic/dot.gif]',0);
				$date_time = $date_time->parent();
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
					
				$date = explode(' ',$post_date);
				$d = $date[1];
				$m = enMonth_decoder($date[0],'full');
				$y = str_replace(',','',$date[2]);
				$t = $date[3];
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
			$comments = $html->find('td[background=pic/topic_second_bg.gif]');
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
				
				$comment_title = '';
					
				$c_body = $c->parent();
				$c_body = $c_body->next_sibling();
				$c_body = $c_body->find('.blackpost',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
	
				$c_author = $c->find('.pinkdarkbold',0);
				if($c_author->plaintext == '')
					$c_author = $c->find('.graybold',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $c->find('.gray',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));					

				$date = explode(' ',$comment_date);
				$d = $date[1];
				$m = enMonth_decoder($date[0],'full');
				$y = str_replace(',','',$date[2]);
				$t = $date[3];
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