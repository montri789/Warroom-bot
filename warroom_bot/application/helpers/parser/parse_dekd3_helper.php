<?php
	function parse_dekd3($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_dekd3';
		$result = array('parse_ok'=>false,'posts'=>array());
		
		$html = str_get_html($fetch);
		
		if($debug){
			$parsed_posts_count = 0;
		}else{
			$current_posts = $page->get_posts();
			if($current_posts) $parsed_posts_count = count($current_posts);
			else $parsed_posts_count = 0;
		}
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[class=blockrow restore]',0);
		if($dead_page != null){
			if($debug){
				echo "Page is dead.";
				echo "<br/>";
			}else{
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}else{
			if($parsed_posts_count == 0 && $page->sub_comment == 0){
		
				$main_content = $html->find('div[class=maincontent] h1');
				$post_title = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('div[class=boardmsg]');
				$post_body = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				$author = $html->find('ul[id=ownerdetail] li b');
				$post_author = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('ul[id=ownerdetail] li',2);
				$post_date = htmlspecialchars(trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext)));

				$date = explode(" ",$post_date);
				$yy = thYear_decoder($date[4]);
				$mm = thMonth_decoder($date[3],'full');
				$dd = $date[2];
				$tt = $date[6];
				
				$post_date = $yy."-".$mm."-".$dd." ".$tt;
					
				if($debug){
					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";
				}else{
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

		
			$comments = $html->find('ul[id=listComment] li[class=bd1soCCC]');
			
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
		
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if(!$result['parse_ok']) break;
				
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1){
					$i++;
					continue;
				}
				
					$c_title = $c->find('div[class=listCommentHead]',0);
					$comment_title = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$c_body = $c->find('div[class=commentBox] div[class=boardmsg]',0);
					$comment_body = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$c_author = $c->find('#ownerdetail li b',0);
					
					if(!empty($c_author)){
					
						$comment_author = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
						
						$c_date_time = $c->find('ul[id=ownerdetail] li',2);
						$comment_date = trim(@iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
						
						$date = explode(" ",$comment_date);
						$yy = thYear_decoder($date[4]);
						$mm = thMonth_decoder($date[3],'full');
						$dd = $date[2];
						$tt = $date[6];
						
						$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				
				if($debug){
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<hr>";
				}else{
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
				}
				$i++;
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>