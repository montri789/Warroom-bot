<?php
	function parse_manager_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_manager_forum';

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
			// Page is dead
			$page->outdate = 1;
			$page->update();
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				$main_content = $html->find('.headline',0);
				$post_title = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('table.body',5);
				$post_body = utf8_encode(trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));

				$author = $html->find('table.body',4);
				$post_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$post_author = trim(str_replace(array("ผู้เขียน:","&nbsp;"),"",$post_author));

				$date_time = $html->find('table.body',6);
				$post_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$post_date = trim(str_replace(array("วันที่ :","&nbsp;"),"",$post_date));
				$pdate = explode(" ",$post_date);

				$yy = thYear_decoder($pdate[2]);
				$mm = thMonth_decoder($pdate[1],'full');

				$post_date = $yy."-".$mm."-".$pdate[0]." ".$pdate[3];

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
			$comments = $html->find('table[width=100%] td.body table[width=100%]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				//if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$s = $c->parent()->parent()->next_sibling();
			
				$c_body = $s->find('table.body tr',0);
				$comment_body = trim(iconv("CP874","utf-8",$c_body->plaintext));
			
				$c_author = $c->find('td.body',0);
				$comment_author = trim(iconv("CP874","utf-8",$c_author->plaintext));
				$comment_author = explode(" ",$comment_author);
				$com_aut = "";
				for($i=0; $i < count($comment_author)-1 ; $i++){
					$com_aut .= " ".$comment_author[$i];
				}
				$comment_title = $comment_author[count($comment_author)-1];
				$comment_author = trim($com_aut);

				$date_time = $s->find('table.body tr',1);
				$comment_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$comment_date = trim(str_replace(array("วันที่ :","&nbsp;"),"",$comment_date));
					
				$cdate = explode(" ",$comment_date);
					
				$yy = thYear_decoder($cdate[2]);
				$mm = thMonth_decoder($cdate[1],'full');
					
				$comment_date = $yy."-".$mm."-".$cdate[0]." ".$cdate[3];
			
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
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
?>