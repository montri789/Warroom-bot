<?php
	function parse_manager3_news($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_manager3_news';
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
		
				$main_content = $html->find('table[width=100%] td.headline');
				$post_title = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('table[width=100%] td.body');
				$post_body = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$board_msg[4]->plaintext));

				$author = $html->find('table[width=100%] td.body');
				$post_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$author[2]->plaintext));

				$pauthor = explode(" ",$post_author);

				$date_time = $html->find('table[width=100%] td.date',0);
				$post_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$pdate = explode(" ",$post_date);
				
				$p_view = $html->find('td[valign=baseline]',8);
				$page_view = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));

				//$page_view = explode(":",$page_view);
				//$view = explode (" ",$page_view[1]);

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
			$comments = $html->find('.tableBorder .msgBody');
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
				
				$p = $c->parent()->parent();
				$s = $p->prev_sibling();
				
				$c_title = $c->find('td.body',0);
				$comment_title = trim(iconv("CP874","utf-8",$c_title->plaintext));
		
				$c_body = $c->find('td.body',3);
				$comment_body = trim(iconv("CP874","utf-8",$c_body->plaintext));
		
				$c_author = $c->find('td.body font',0);
				$comment_author = trim(iconv("CP874","utf-8",$c_author->plaintext));
								
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
				//$i++;
		}
			}
		}
		
		$html->clear();
		unset($html);
		return $result;
	}
?>