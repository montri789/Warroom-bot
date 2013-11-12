<?php
	function parse_jokergameth_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_jokergameth_board';

		
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
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				$main_content = $html->find('title',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
	
				$board_msg = $html->find('blockquote[class=postcontent restore]',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
	
				$author = $html->find('div[class=popupmenu memberaction] strong',0);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
	
				$date_time = $html->find('span[class=date]',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$str = explode("&nbsp;",$post_date);
				
				if($str[0] == 'เมื่อวานนี้' || $str[0] == 'วันนี้' )
				{
					$post_date = dateThText($str[0])." ".$str[1];
				}
				else
				{
					$str = explode(" ",$post_date);
					$time = explode("&nbsp;",$str[2]);
					$yy = $time[0];
					$tt = $time[1];
					$post_date = $yy."-".enMonth_decoder($str[1])."-".str_replace(array("st","rd","nd","th"),"",$str[0])." ".$tt;
				}
				
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
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_body;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					$post->insert();
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('div[class=posthead]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}
					$n = $c->next_sibling();
					$c_title = $c->find('a[class=postcounter]',0);
				if($c_title != null){
					$comment_title = $comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$c_body = $n->find('div[id^=post_message_]',0);
					$comment_body = $comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$c_author = $n->find('div[class=username_container] strong',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
					$c_date_time = $c->find('span[class=date]',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
	
					$str = explode("&nbsp;",$comment_date);
			
					if($str[0] == 'เมื่อวานนี้' || $str[0] == 'วันนี้' ){
						$comment_date = dateThText($str[0])." ".$str[1];
					}
					else{
						$str = explode(" ",$comment_date);
						$time = explode("&nbsp;",$str[2]);
						$yy = $time[0];
						$tt = $time[1];
						$comment_date = $yy."-".enMonth_decoder($str[1])."-".str_replace(array("st","rd","nd","th"),"",$str[0])." ".$tt;
					}

				if($debug){
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<hr>";
				}
				else{
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "comment";
					$post->title = $comment_title;
					$post->body = trim($comment_body);
					$post->post_date = $comment_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($comment_author));
					$post->insert();
					unset($post);
				}
				}
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
?>