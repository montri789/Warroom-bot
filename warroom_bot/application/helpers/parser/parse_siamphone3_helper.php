<?php
	function parse_siamphone3($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_siamphone3';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$html = str_get_html($fetch);
		
		$html = str_get_html($fetch);
		if(is_null($html))
		{
			echo 'NULL!!!';
			$result['posts'] []= null;
			$result['parse_ok'] = false;
			return $result;
		}
		
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

		/*$dead_page = $html->find('div[id=main_contentRed]',0);
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
		else*/
		
			if($parsed_posts_count == 0 && $page->sub_comment == 0) 
			{
		
				$main_content = $html->find('a[class=maintitle]',0);
				if($main_content == null) 
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
					$post_title = trim($main_content->plaintext);
		
					$board_msg = $html->find('.postbody',0);
					$post_body = trim($board_msg->plaintext);
				
					$author = $html->find('.name',0);
					$post_author = trim($author->plaintext);
					
					$date_time = $html->find('td[width=65%] font[size=1]',1);
					$post_date = trim($date_time->plaintext);
					
					
					$str = explode(" ",$post_date);
					$yy = $str[4];
					$mm = $str[3];
					$dd = $str[2];
					$tt = $str[5];
					
					$post_date = $yy."-".enMonth_decoder($mm,'cut')."-".$dd." ".$tt;
				
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
						$data = array();
						$data["page_id"] = $page->id;
						$data["type"] = "post";
						$data["title"] = $post_title;
						$data["body"] = $post_body;
						$data["post_date"] = $post_date;
						$data["parse_date"] = mdate('%Y-%m-%d %H:%i',time());
						$data["author"] = $post_author;
						$data["website_id"] = $domain["id"];
						$data["website_name"] = $domain["name"];
						$data["url"] = substr($domain["root_url"],0,-1)."".$page->url;
						
						if(validate($post_date)){
							$result['posts'] []= $data;
							$result['parse_ok'] = true;
						}else{
							$result['parse_ok'] = false;
							echo "validate post failed . . .";
						}
						unset($data);
					}
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('td[width=130] table[width=100%] td span.name');
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
				
					$p = $c->parent()->parent()->parent()->parent()->next_sibling();
					
					$comment_title = "Re: ".$post_title;
				
					$c_body = $p->find('span.postbody',0);
					$comment_body = trim($c_body->plaintext);
	
					$c_author = $c;//$c->find('.author-name',0);
					$comment_author = trim($c_author->plaintext);
	
					$c_date_time = $p->find('font[size=1]',1);
					$comment_date = trim($c_date_time->plaintext);
	
					$comment_date = str_replace(array(",","-"),"",$comment_date);
					$comment_date = str_replace("/"," ",$comment_date);
					
					$str = explode(" ",$comment_date);
					$yy = $str[4];
					$mm = $str[3];
					$dd = $str[2];
					$tt = $str[5];
					
					$comment_date = $yy."-".enMonth_decoder($mm,'cut')."-".$dd." ".$tt;
				
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
					$data = array();
					$data["page_id"] = $page->id;
					$data["type"] = "comment";
					$data["title"] = $comment_title;
					$data["body"] = $comment_body;
					$data["post_date"] = $comment_date;
					$data["parse_date"] = mdate('%Y-%m-%d %H:%i',time());
					$data["author"] = $comment_author;
					$data["website_id"] = $domain["id"];
					$data["website_name"] = $domain["name"];
					$data["url"] = substr($domain["root_url"],0,-1)."".$page->url;
					
					if(validate($comment_date)){
						$result['posts'] []= $data;
						$result['parse_ok'] = true;
					}else{
						$result['parse_ok'] = false;
						echo "validate comment failed . . . <br>";
					}
					unset($data);
				}
				$i++;
			}
		
		
		$html->clear();
		unset($html);
		return $result;
	}
?>