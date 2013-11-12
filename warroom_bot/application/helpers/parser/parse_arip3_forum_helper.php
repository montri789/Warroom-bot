<?php
	function parse_arip3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_arip3_forum';
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
		
				$main_content = $html->find('.box_in h1');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('.box_in p');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				$author = $html->find('.box_in font b');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[1]->plaintext));

				$post_date = $html->find('.box_in font');
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$post_date[0]->plaintext));

				$str= explode(" โดย ",$post_date);
				$date = explode(" ",$str[0]);                
				$time = explode(" เวลา ",$str[1]);
				$time = str_replace("น.","",$time[1]);				

				$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1],"full")."-".$date[0]." ".trim($time);
					
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
			$comments = $html->find('div[class=comment_box]');
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
				
					$comment_title = "#".++$i;

					$c_body = $c->find('div.comment_user_text',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					$str = explode(".",$comment_body);
					$comment_body = $str[1]; 
	
					$c_author = $c->find('div.comment_user_name',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
	
					$c_date_time = $c->find('div.text_small',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
	
					$str= explode(" ",$comment_date);
					$comment_author0 = $str[0];  
					$date = $str[3];
					$yy = $str[6];
					$mm = $str[5];
					$dd = $str[4];
					$tt = $str[8];
	
					$comment_date = $yy."-".thMonth_decoder($mm,"full")."-".$dd." ".$tt;
				
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