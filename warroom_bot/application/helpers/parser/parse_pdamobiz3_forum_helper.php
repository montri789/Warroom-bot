<?php
	function parse_pdamobiz3_forum($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_pdamobiz3_forum';
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
		
				$main_content = $html->find('span[class=lgText]',0);
				$p_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
				$str = explode("ชื่อกระทู้:",$p_title);
				$post_title = $str[1];
	
				$board_msg = $html->find('td[class=text] font[size=2]',0);
				$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
	
				$author = $html->find('tbody td[class=bold]',1);
				$post_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	
				$date_time = $html->find('tbody tr td[class=smText]',2);
				$p_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				
				$str2 = explode(" ",$p_date);
				$date = $str2[1];
				$tt = $str2[3];
				$pd = explode("-",$date);
				$dd = $pd[0];
				$mm = $pd[1];
				$yy = thYear_decoder(12);
				$post_date = $yy."-".$mm."-".$dd." ".$tt;
					
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('td[class=smText] table[width=100%]');
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
				
					$p = $c->parent()->parent()->next_sibling();
					$c_body = $p->find('td[class=text]',0);
					$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$p = $c->parent()->prev_sibling();
					$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$p->plaintext));
					
					
					//$c_date_time = $p->find('.font_add .style5',1);
					$comment_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c->plaintext));
					$str2 = explode(" ",$comment_date);
			
					$date = $str2[1];
					$tt = $str2[3];
					$pd = explode("-",$date);
					$dd = $pd[0];
					$mm = $pd[1];
					$yy = thYear_decoder(12);
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
					
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
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>