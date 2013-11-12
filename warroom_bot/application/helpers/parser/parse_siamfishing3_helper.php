<?php
	function parse_siamfishing3($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_siamfishing3';
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
		
				$main_content = $html->find('div[style=float: left; padding-left:5px;] h2',0);
				$post_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
	
				$board_msg = $html->find('tbody tr td[width=100%]',7);
				$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
	
				$author = $html->find('div[class=poster_col] strong',0);
				$post_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	
				$date_time = $html->find('div[style=float:left; padding:4px 5px 0px 0px;]',0);
				$post_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
						
				$str = explode(" ",$post_date);
				$date = $str[1];
				$tt = $str[2];
				$str2 = explode("-",$date);
				$yy = $str2[2];
				$mm = $str2[1];
				$dd = $str2[0];
				
				$post_date = thYear_decoder($yy)."-"."$mm"."-".$dd." ".$tt;
					
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
			$comments = $html->find('div[class=poster_col]');
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
				
					$c_title = $c->find('font[size=-1]',0);
					$comment_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$p = $c->parent()->next_sibling()->next_sibling();
					$c_body = $p->find('tbody tr td[width=100%]',0);
					$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$c_author = $c->find('strong',0);
					$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $p->find('div[style=float:left; padding:4px 5px 0px 0px;]',0);
					$comment_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
					
					
					$str = explode(" ",$comment_date);
					$date = $str[1];
					$tt = $str[2];
					$str2 = explode("-",$date);
					$yy = $str2[2];
					$mm = $str2[1];
					$dd = $str2[0];
					
					$comment_date = thYear_decoder($yy)."-"."$mm"."-".$dd." ".$tt;
				
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
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>