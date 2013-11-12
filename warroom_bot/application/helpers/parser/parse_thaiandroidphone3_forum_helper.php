<?php
	function parse_thaiandroidphone3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaiandroidphone3_forum';
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

		$dead_page = $html->find('div[class=thankyou-txt box-round5] p',0);
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
		
				$main_content = $html->find('div[id=body_02] div[id=postlist] h1[class=ts] a',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div[id=body_02] div[id=ct] td[class=plc] td[class=t_f]',0);
				$post_body = trim($board_msg->plaintext);
				htmlentities($post_body,ENT_IGNORE);
				strip_tags($post_body);

				$author = $html->find('div[id=body_02] div[id=ct] td[class=pls] div[class=authi] a',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('div[id=body_02] div[id=ct] td[class=plc] div[class=authi] em',0);
				$post_date = trim($date_time->plaintext);
				$post_date = str_replace("โพสต์เมื่อ ","",$post_date);

				$date = explode("&nbsp;",$post_date);	

				if($date[1] == "ชั่วโมงก่อน" || $date[1] == "นาทีก่อน" || $date[1] == "วินาทีก่อน"){
					$post_date = dateThText($date[1],$date[0]);
				}else if($date[1] == "วันก่อน"){
					$post_date = dateThText($date[0]);
				}else if($date[0] == "วันนี้" || $date[0] == "เมื่อวาน" || $date[0] == "เมื่อวานซืน"){
					$post_date = dateThText($date[0])." ".$date[1];
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
			$comments = $html->find('table[id^=pid]');
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
				
					$c_title = $c->find('.pi strong em',0);
				if($c_title != NULL)
				$comment_title = trim($c_title->plaintext);
				else continue;
				
				$c_body = $c->find('div[class=pct] td[class=t_f]',0);
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->find('td[class=pls] div[class=authi] a',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('td[class=plc] div[class=pi] em',0);
				$comment_date = trim($c_date_time->plaintext);
	
				$comment_date = str_replace("โพสต์เมื่อ ","",$comment_date);
		
				$date = explode("&nbsp;",$comment_date);
			
				if($date[1] == "ชั่วโมงก่อน" || $date[1] == "นาทีก่อน" || $date[1] == "วินาทีก่อน"){
					$comment_date = dateThText($date[1],$date[0]);
				}else if($date[1] == "วันก่อน"){
					$comment_date = dateThText($date[0]);
				}else if($date[0] == "วันนี้" || $date[0] == "เมื่อวาน" || $date[0] == "เมื่อวานซืน"){
					$comment_date = dateThText($date[0])." ".$date[1];
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
						echo "validate comment failed . . .";
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