<?php
	function parse_exteen3_board($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_exteen3_board';
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
			//echo $html; exit;
		
				$main_content = $html->find('div[class=post] h3');
				
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('div[class=club-content postcontent]');
				$post_body = trim($board_msg[0]->plaintext);

				$post_body = explode("   ",$post_body);
				$post_body  = $post_body[0];

				$post_date = explode(".postmeta .namedate",$post_body);
				$post_date  =  trim($board_msg[0]->plaintext);

				$page_info = $html->find('.postmeta .commentno');
				$page_view = trim($page_info[0]->plaintext);
				$page_view = preg_replace("/[^0-9]/", '',$page_view);
				
				$p_author = $html->find(' .postmeta .bold',0);
				if($p_author == null) $post_author = null;
				$post_author = trim($p_author->plaintext);

				$str= explode(" on ",$post_date);
				$date = explode(" ",str_replace(array("(",")"),"",$str[1]));

				$post_date = $date[2]."-".enMonth_decoder($date[1],"cut")."-".$date[0]." ".$date[3];
					
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
			$comments = $html->find('div[class=post rep repeven]');
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
				
					$c_title = $c->find('div[class=postmeta] div[class=commentno]',0);
					$comment_title = trim($c_title->plaintext);
	
					$c_body = $c->find('div[class=club-content]',0);
					$comment_body = trim($c_body->plaintext);
	
					$c_date_time = $c->find('.postmeta .namedate',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$c_author = $c->find(' .postmeta .bold',0);
					if($c_author == null) $comment_author = null;
					$comment_author = trim($c_author->plaintext);
	
					$str= explode(" on ",$comment_date); 
					$date = explode(" ",str_replace(array("(",")"),"",$str[1]));
					$comment_date = $date[2]."-".enMonth_decoder($date[1],"cut")."-".$date[0]." ".$date[3];
				
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
				//$i++;
		}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>