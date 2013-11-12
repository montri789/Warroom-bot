<?php
	function parse_ladysquare3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_ladysquare3_forum';
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

		$dead_page = $html->find('table[class=errorTable]',0);
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
		
				$main_content = $html->find('table[class=basicTable] h1',0);
				$post_title = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('tr[class=msgEvenTableRow] div[class=msgBody]',0);
				$post_body = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

				$author = $html->find('table[class=tableBorder] td[class=msgEvenTableSide] span',0);
				$post_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$author->plaintext));

				$date_time = $html->find('table[class=tableBorder] td[class=msgEvenTableTop]',0);
				$post_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$date = explode("Posted:",$post_date);		
				$pdate = explode("&nbsp;",str_replace(" at ","&nbsp;",$date[1]));

				if(trim($pdate[0]) == "Yesterday" || trim($pdate[0]) == "Today")
				{
					$post_date = dateEnText($pdate[0])." ".$pdate[1];
				}
				else
				{
					$mm = thMonth_decoder($pdate[1],'cut');
					$post_date  = trim($pdate[2])."-".$mm."-".trim($pdate[0])." ".trim($pdate[3]);
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
			$comments = $html->find('.tableBorder .msgBody');
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
				
					$p = $c->parent()->parent();
					$s = $p->prev_sibling();
					
					$comment_title = "Re:".$post_title;
						
					$c_body = $p->find('.msgBody',0);
					$comment_body = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
						
					$c_author = $s->find('.msgSideProfile',0);
					$comment_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
						
					$c_date_time = $s->find('td',1);
					$comment_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
					
					$date = explode("Posted:",$comment_date);		
					$pdate = explode("&nbsp;",str_replace(" at ","&nbsp;",$date[1]));
					
					if(trim($pdate[0]) == "Yesterday" || trim($pdate[0]) == "Today")
					{
						$comment_date = dateEnText($pdate[0])." ".$pdate[1];
					}
					else
					{
						$mm = thMonth_decoder($pdate[1],'cut');
						$comment_date  = trim($pdate[2])."-".$mm."-".trim($pdate[0])." ".trim($pdate[3]);
					}
				
				if(!empty($comment_author)){
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
						echo "validate comment failed . . . <br>";
					}
					//$post->insert();
					
					unset($post);
				}
				//$i++;
		}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>