<?php
	function parse_overclockzone3_review($fetch,$page,$debug=false,$domain=null)
	{
		$log_unit_name = 'parse_overclockzone3_review';
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

		$dead_page = $html->find('',0);
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
		
				$main_content = $html->find('#AutoNumber24 table[width=95%]',1);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('#AutoNumber24',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
				
				$pb = explode("$post_title",$post_body);
				$post_body = $pb[1];

				$author = $html->find('td.style21 .style204',2);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				if($post_author == null){
					$author = $html->find('td.style21 .style204',0);
					$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				}

				$at	= $html->find('#AutoNumber24 .style21',1);
				$at = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$at->plaintext));
				if($at == null){
					$at	= $html->find('#AutoNumber24 .style21',2);
					$at = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$at->plaintext));
				}
				$at = str_replace("Date: ","",$at);

				$at = explode(" Author: ",trim($at));
				$post_date = explode("-",trim($at[0]));
				if(!is_numeric($post_date[1]))
				$post_date = $post_date[2]."-".enMonth_decoder($post_date[1],"cut")."-".$post_date[0];
				else
				$post_date = $post_date[2]."-".$post_date[1]."-".$post_date[0];
					
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
			/*$comments = $html->find('td[class^=windowbg]');
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
				
					$c_title = $c->find('div[id^=subject_]',0);
					$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.post',0);
					$comment_body = htmlentities(trim($c_body->plaintext),ENT_IGNORE,'utf-8');

					$c_author = $c->find('td[width=16%] b',0);
					$comment_author = trim($c_author->plaintext);

					$c_date_time = $c->find('td[width=85%] .smalltext',0);
					$comment_date = str_replace(array("เมื่อ:",","),"",trim($c_date_time->plaintext));

					$date = explode(" ",$comment_date);

					if(trim($date[4]) == "เมื่อวานนี้" || trim($date[4]) == "วันนี้"){
						$comment_date = dateThText($date[4])." ".$date[6];
					}else{		
						$dd = $date[5];
						$mm = thMonth_decoder($date[4],"full");
						$yy = $date[6];
						$tt = $date[7];	

						$comment_date = $yy."-".$mm."-".$dd." ".$tt;
					}
				
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
			}*/
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>