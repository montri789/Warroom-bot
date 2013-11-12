<?php
	function parse_clubnotebook3_forum($fetch,$page,$debug=false,$domain)
	{
		$log_unit_name = 'parse_clubnotebook3_forum';
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
		
				$main_content = $html->find('div[id^=subject] a');
				//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[1]->plaintext));
				$post_title = trim($main_content[0]->plaintext);
				//$post_title = trim(str_replace('"',"",$post_title));

				$board_msg = $html->find('.post');
				//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('.windowbg a');
				//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('.smalltext',1);
				//$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$post_date = trim($date_time->plaintext);

				//echo $post_date."<br>";
				$post_date = str_replace("  "," ",$post_date);
				$post_date = str_replace(",",null,$post_date);
				//$pd = explode(" ",$post_date);
				$pdate = explode(" ",$post_date);
				//echo $pdate[4]."-".$pdate[3]."-".$pdate[3]."<br>";

				$p_view = $html->find('.catbg3 td',2);
				$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));
				$page_view = trim($p_view->plaintext);
				//$page_view = onlyNum($page_view);

				$page_view = explode("(",$page_view);
				$pview = explode(")",$page_view[1]);
				$ptest = onlyNum($pview[0]);
				if(!is_numeric($ptest))
				{
				$page_view = explode(")",$page_view[2]);
				$page_view = $page_view[0];
				}else $page_view = $pview[0];


				//$yy = thYear_decoder($pdate[2]);
				$mm = thMonth_decoder($pdate[2],'full');

				$post_date = $pdate[4]."-".$mm."-".trim($pdate[3])." ".trim($pdate[5]);
					
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
			$comments = $html->find('td[class^=windowbg]');
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
				
					$c_title = $c->find('h5[id^=subject] a',0);
					//$comment_title = trim(iconv("tis-620","utf-8",$c_title->plaintext));
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('.post',0);
					//$comment_body = trim(iconv("tis-620","utf-8",$c_body->plaintext));
					$comment_body = trim($c_body->plaintext);
					
					$c_author = $c->find('a',0);
					//$comment_author = trim(iconv("tis-620","utf-8",$c_author->plaintext));
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.smalltext',1);
					//$comment_date = trim(iconv("tis-620","utf-8",$c_date->plaintext));
					$comment_date = trim($c_date->plaintext);
					
					//echo $comment_date."<br>";
					$comment_date = str_replace("  "," ",$comment_date);
					$comment_date = str_replace(",",null,$comment_date);
					$cdate = explode(" ",$comment_date);
					//echo $cdate[0]."<br>";
					//if($cdate[0] == 'Yesterday')
					//{
						//$com_date = dateEnText($cdate[0]);
						//$comment_date = $com_date." ".$cdate[1];
					//}else
					//{
					$yy = thYear_decoder($cdate[2]);
					$mm = thMonth_decoder($cdate[4],'full');
					//echo $cdate[6]."-".$mm."-".$cdate[4]."<br>";
					
					$comment_date = $cdate[6]."-".$mm."-".$cdate[5]." ".$cdate[7];
				
				i
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