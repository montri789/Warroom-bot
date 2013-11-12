<?php
	function parse_chicministry3_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_chicministry_board';
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
		
				$main_content = $html->find('.gensmall div[style^=float: left]',0);
				$post_title = trim($main_content->plaintext);
				
				$title = explode(": ",$post_title);
				$post_title = $title[1];
	
				$board_msg = $html->find('.postbody',0);
				$post_body = strip_tags(trim($board_msg->plaintext));
				
				$author = $html->find('.postauthor',0);
				$post_author = trim($author->plaintext);	
							
				$date = $html->find('.gensmall div[style^=float: right]',0);
				$post_date = trim($date->plaintext);
	
				$post_date = trim(str_replace(array("Posted:",",","&nbsp;"),"",$post_date));
				$pdate = explode(" ",$post_date);
	
				$ptime = @date("H:i", strtotime($pdate[4]." ".$pdate[5]));
							
				$post_date = $pdate[3]."-".enMonth_decoder($pdate[1],"cut")."-".$pdate[2]." ".$ptime ;
					
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
			$comments = $html->find('#pagecontent .tablebg .profile table[width=150]');
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
				
				$cb = $c->parent()->parent();
				$ct = $cb->prev_sibling();
			
				$c_title = $ct->find('div',0);
				$comment_title = trim($c_title->plaintext);
				$comment_title = str_replace("&nbsp;Post subject: ","",$comment_title);

				$c_title = $cb->find('.postbody',0);
				$comment_body = trim($cb->plaintext);
				
				$c_author = $ct->find('.postauthor',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date = $ct->find('div',1);
				$comment_date = trim($c_date->plaintext);
				$comment_date = trim(str_replace(array("Posted:",",","&nbsp;"),"",$comment_date));
				$cdate = explode(" ",$comment_date); 

				$ctime = @date("H:i", strtotime($cdate[4]." ".$cdate[5]));
						
				$comment_date = $cdate[3]."-".enMonth_decoder($cdate[1],"cut")."-".$cdate[2]." ".$ctime ;
				
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