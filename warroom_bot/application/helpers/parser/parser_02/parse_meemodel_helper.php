<?php
	function parse_meemodel($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_meemodel';

		
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
				// Post Title at div.maincontent, 1st h1 element
				$main_content = $html->find('td[height=45] h1');
				$post_title = trim($main_content[0]->plaintext);
				//$main_content = $html->find('div[class=maincontent] h1');
				//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				// Post Body at div.boardmsg
				$board_msg = $html->find('font[style=font-family:Tahoma; font-size:16px]');
				$post_body = trim($board_msg[0]->plaintext);
				//$board_msg = $html->find('div[class=boardmsg]');
				//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				// Post Meta at ul#ownerdetail
				$author = $html->find('font[size=+1]');
				$post_author = trim($author[0]->plaintext);
				//$author = $html->find('ul[id=ownerdetail] li b');
				//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('td[width=515]');
				$post_date = trim($date_time[0]->plaintext);

				// View Count
				$page_info = $html->find('div[class=maincontent] p span strong');
				$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

				$str= explode(" ",$post_date);
				$date = $str[68];
				$tt = $str[69];
				
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
			$comments = $html->find('tr td table[bordercolor=#FFCCFF]');
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
				//Comment Title as div.listCommentHead
				$c_title = $c->find('font[size=-1]');
				$comment_title = trim($c_title->plaintext);
				//$c_title = $c->find('div[class=listCommentHead]',0);
				//$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

				//Comment Body as div.commentBox div.boardmsg
				$c_body = $c->find('div[class=xtext] p',0);
				$comment_body = trim($c_body->plaintext);

				//Comment Author as ui#ownerdetail li b
				$c_author = $c->find('td[align=left] a',1);
				$comment_author = trim($c_author->plaintext);

				//Comment Date ul#ownerdetail li
				$c_date_time = $c->find('div[class=xtool] span',0);
				$comment_date = trim($c_date_time->plaintext);

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