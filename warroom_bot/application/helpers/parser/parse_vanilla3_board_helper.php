<?php
	function parse_vanilla3_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_vanilla3_board';
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
		
				$main_content = $html->find('.mypage-box9-bbs h1',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $html->find('.p8bbsbox2',0);
				$post_body = strip_tags(trim($board_msg->plaintext));
				
				$author = $html->find('.mypage-box9-bbs .l-white a',0);
				$post_author = trim($author->plaintext);	
				
				$date = $html->find('.pbbstitle2',0);
				$post_date = trim($date->plaintext);
				
				$post_date = str_replace("   "," ",$post_date);
				$pd = explode(" ",$post_date);
				$pt = explode("/",$pd[2]);
				$month = explode(",",$pt[1]);
				$day = explode(",",$pt[0]);
				$year = explode(",",$pt[2]);
				//$mm = thMonth_decoder($month[0],'cut');
				//$years = thYear_decoder($year[0]);
				
				$post_date = $year[0]."-".$month[0]."-".$day[0]." ".$pd[3];
					
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
			$comments = $html->find('table[align=center] .p8bbsbox-back');
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
				
				$comment_title = null; 
					
					$c_body = $c->find('.pkuchi',0);
					$comment_body = trim($c_body->plaintext);
			
					$c_author = $c->find('.l-normal a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.bbsres',0);
					$comment_date = trim($c_date->plaintext);
					
					$comment_date = str_replace("   "," ",$comment_date);
					$cdate = explode(" ",$comment_date);
					$cd = explode("/",$cdate[2]);
					$month = explode(",",$cd[1]);
					$day = explode(",",$cd[0]);
					$year = explode(",",$cd[2]);
					$cday = explode(",",$cdate[1]);

					$mm = enMonth_decoder($cdate[0],'full');

					$comment_date = $year[0]."-".$month[0]."-".$day[0]." ".$cdate[3];
					
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