<?php
	function parse_postjung3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_postjung3_forum';
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
		
				$main_content = $html->find('div[class=mainbox] h1',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div[class=mainbox] div[class=sptopic]');
				$post_body = trim($board_msg[0]->plaintext);

				$pbody = explode("หัวข้อ:",$post_body);
				$post_body = $pbody[0];

				$author = $html->find('div[class=mainbox] div[class=sptopic] div[style^=padding:10px] a',0);
				$post_author = trim($author->plaintext);
				if(empty($post_author)){
					$author = $html->find('.spby td a',1);
					$post_author = trim($author->plaintext);
				}

				$date_time = $html->find('div[class=mainbox] div[class=sptopic] div[style^=padding:10px]',0);
				$post_date = trim($date_time->plaintext);
				if(empty($post_date)){
					$date_time = $html->find('.spby td',1);
					$post_date = trim($date_time->plaintext); 	
				}

				$post_date = str_replace(array("โพสท์โดย:"," -","&nbsp;"),"",$post_date);
				$post_date = str_replace("  "," ",$post_date);

				$date = preg_split("/[\s]+/",trim($post_date));		

				$post_date = array();
				$i = 0;
				foreach($date as $val){
					if(preg_match("/^[0-9]/",$val)) $i = 1;		
					if($i == 1) $post_date[] = $val;
				}

				if(trim($post_date[1]) == "เมื่อวาน" || trim($post_date[1]) == "วันนี้" || trim($post_date[1]) == "วันจันทร์" || trim($post_date[1]) == "วันอังคาร" || trim($post_date[1]) == "วันพุธ" || trim($post_date[1]) == "วันพฤหัส" || trim($post_date[1]) == "วันศุกร์" || trim($post_date[1]) == "วันเสาร์" || trim($post_date[1]) == "วันอาทิตย์"){ 
					$post_date = dateThText($post_date[1])." ".$post_date[0];
				}else{
					$yy = thYear_decoder($post_date[3]);
					$mm = thMonth_decoder($post_date[2],'full');
					$post_date = $yy."-".$mm."-".$post_date[1]." ".$post_date[0];
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
			$comments = $html->find('#cmncms div[userid]');
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
				
					$c_title = $c->find('a[class=xnum]',0);
					$comment_title = trim($c_title->plaintext);			
					
					$c_body = $c->find('div[class=xtext]');
					$comment_body = trim($c_body[0]->plaintext);
						
					$c_author = $c->find('td[align=left]',0);
					$comment_author = trim($c_author->plaintext);
					$c_author = explode(" ",$comment_author);
					$j = 0;
					$comment_author = "";
					foreach($c_author as $val){
						if($j > 0) $comment_author .= $val;
						$j++;
					}
						
					$c_date_time = $c->find('div[class=xtool] span',0);
					$comment_date = trim($c_date_time->plaintext);
			
					$cdate = explode(" ",$comment_date);
					
					if(trim($cdate[1]) == "เมื่อวาน" || trim($cdate[1]) == "วันนี้" || trim($cdate[1]) == "วันจันทร์" || trim($cdate[1]) == "วันอังคาร" || trim($cdate[1]) == "วันพุธ" || trim($cdate[1]) == "วันพฤหัส" || trim($cdate[1]) == "วันศุกร์" || trim($cdate[1]) == "วันเสาร์" || trim($cdate[1]) == "วันอาทิตย์"){ 
						$comment_date = dateThText($cdate[1])." ".$cdate[0];
					}else{
						$yy = thYear_decoder($cdate[5]);
						$mm = thMonth_decoder($cdate[4],'full');
							
						$comment_date = $yy."-".$mm."-".$cdate[3]." ".$cdate[0];
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
				}
				//$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>