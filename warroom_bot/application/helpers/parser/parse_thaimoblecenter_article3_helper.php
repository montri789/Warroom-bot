<?php
	function parse_thaimoblecenter_article3($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_thaimoblecenter_article3';
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

		$dead_page = $html->find('td[id=errormsg]',0);
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
		
				$main_content = $html->find('td[class=16] font',0);
				$post_title = trim(iconv("CP874","utf-8",$main_content->plaintext));

				$board_msg = $html->find('td[class=16] span[class=text12]',0);
				$post_body = trim(iconv("CP874","utf-8",$board_msg->plaintext));

				$author = $html->find('td[class=16] span[class=text12] font[color=#000000]',0);
				$post_author = trim(iconv("CP874","utf-8",$author->plaintext));
				if($post_author == null) $post_author = $default_author;

				$date_time = $html->find('table[width=778] table[width=96%] table[cellpadding=1] td[class=text10]',1);
				$post_date = trim(iconv("CP874","utf-8",$date_time->plaintext));

				$date = explode("/",$post_date);
				$dd = explode(":",$date[0]);
				$yy = thYear_decoder($date[2]);

				$post_date = $yy."-".$date[1]."-".trim($dd[1]);
					
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
			$comments = $html->find('table[width=96%] table[width=95%]');
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
				
					$c_body = $c->find('td[width=83%] td[class=text12]');
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body[0]->plaintext));	

					$c_author = $c->find('td[width=73%] font',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

					$c_date_time = $c->find('td[width=83%] td[class=text10] font',2);
					$comment_date = trim(iconv("tis-620","utf-8",$c_date_time->plaintext));

					$cdate = explode("/",$comment_date);
					$cd = explode("&nbsp;",$cdate[0]);
					$cy = explode(" ",$cdate[2]);
					$yy = thYear_decoder($cy[0]);
					$dd = onlyNum($cd[1]);
					$comment_date = $yy."-".$cdate[1]."-".trim($dd)." ".$cy[2];
				
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