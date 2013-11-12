<?php
	function parse_smethailandclub3_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_smethailandclub3_forum';
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
		
				$main_content = $html->find('.style5 strong');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[4]->plaintext));
				//$post_title = trim($main_content[0]->plaintext);
				//$post_title = trim(str_replace('"',"",$post_title));

				$board_msg = $html->find('.board_content_div');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
				//$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('.tx_mag');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
				//$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('.tx_time',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				//$post_date = trim($date_time->plaintext);

				//echo $post_date."<br>";
				$post_date = str_replace("  "," ",$post_date);
				$post_date = str_replace(",",null,$post_date);
				$pd = explode(" ",$post_date);
				$pdate = explode("/",$pd[1]);
				//echo $pdate[4]."-".$pdate[3]."-".$pdate[3]."<br>";

				//$p_view = $html->find('.catbg3 td',2);
				//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$p_view->plaintext));
				//$page_view = trim($p_view->plaintext);
				//$page_view = onlyNum($page_view);

				//$page_view = explode("(",$page_view);
				//$pview = explode(")",$page_view[1]);
				//if(!is_int($pview))			
				//$page_view = explode(")",$page_view[2]);
				//$page_view = $page_view[0];


				$yy = thYear_decoder($pdate[2]);
				$mm = thMonth_decoder($pdate[2],'full');

				$post_date = $yy."-".$pdate[1]."-".trim($pdate[0])." ".trim($pdate[5]);
					
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
			$comments = $html->find('table[id^=item]');
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
					
					$c_body = $c->find('.board_content_div',0);
					$comment_body = trim(iconv("tis-620","utf-8",$c_body->plaintext));
					//$comment_body = trim($c_body->plaintext);
					
					$c_author = $c->find('strong',1);
					$comment_author = trim(iconv("tis-620","utf-8",$c_author->plaintext));
					//$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.classname',0);
					$comment_date = trim(iconv("tis-620","utf-8",$c_date->plaintext));
					//$comment_date = trim($c_date->plaintext);
					
					//echo $comment_date."<br>";
					$comment_date = str_replace("  "," ",$comment_date);
					$post_date = str_replace(",",null,$comment_date);
					$cd = explode(" ",$comment_date);
					$cdate = explode("/",$cd[1]);
					//if($cdate[0] == 'Yesterday')
					//{
						//$com_date = dateEnText($cdate[0]);
						//$comment_date = $com_date." ".$cdate[1];
					//}else
					//{
					$yy = thYear_decoder($cdate[2]);
					//$mm = thMonth_decoder($cdate[4],'full');
					//echo $cdate[6]."-".$mm."-".$cdate[4]."<br>";
					
					$comment_date = $yy."-".$cdate[1]."-".$cdate[0]." ".$cdate[7];
					//}6]."-".$mm."-".$cdate[5]." ".$cdate[7];
					//}
				
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
						echo "validate comment failed . . .";
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