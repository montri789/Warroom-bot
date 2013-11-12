<?php
	function parse_zoneit_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_zoneit_forum';

		
		$html = str_get_html($fetch);
		
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
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

		$dead_page = $html->find('p[class=information centertext]',0);
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
				$main_content = $html->find('div[id=forumposts] div[class=keyinfo] h5',0);
				if($main_content != null){
				$post_title = trim($main_content->plaintext);
				$ptitle = explode("(",$post_title);
				$post_title = $ptitle[0];
			}else{
				$main_content = $html->find('head title',0);
				$post_title = trim($main_content->plaintext);
			}

				$board_msg = $html->find('div[id=forumposts] div[class=post]',0);
				if($board_msg != null){
				$post_body = trim($board_msg->plaintext);
			}else{
				$board_msg = $html->find('textarea[class=bbcode]',0);
				$post_body = trim($board_msg->plaintext);
				$post_body = strip_tags(html_entity_decode("$post_body"));
				$post_body = str_replace("nbsp;"," ",$post_body);
			}

				$author = $html->find('div[id=forumposts] div[class=poster] a',0);
				if($author != null){
				$post_author = trim($author->plaintext);
			}else{
				$author = $html->find('',0);
				$post_author = "unknow";
			}
    
				$date_time = $html->find('div[id=forumposts] div[class=postarea] div[class=keyinfo] div[class=smalltext]',0);
				if($date_time != null){
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
				$ydate = explode(",",$date[5]);

				$page_info = $html->find('div[id=forumposts] h3',0);
				$page_view = trim($page_info->plaintext);

				$aview = explode("(",$page_view);
				$pview = explode(")",$aview[1]);
	  			$page_view = $pview[0];
				
				if(trim($date[3]) == "เมื่อวานนี้" || trim($date[3]) == "วันนี้" || trim($date[3]) == "วันปิยมหาราชปีที่แล้ว" || trim($date[3]) == "วันวาเลนไทน์ปีที่แล้ว"){
					$post_date = dateThText($date[3])." ".$date[5];	
				}else{
					$mm = thMonth_decoder($date[4],'full');
					$post_date = $ydate[0]."-".$mm."-".$date[3]." ".$date[6];	
				}
			}else{
				$date_time = $html->find('div[class=date]',0);
				$post_date = trim($date_time->plaintext);
				$pdate = str_replace("  "," ",$post_date);
					$pdate = explode(" ",$pdate);
					$pdate[0] = str_replace(",","",$pdate[0]);
					
					if($pdate[0] == "วันปิยมหาราชปีที่แล้ว" || $pdate[0] == "วันวาเลนไทน์ปีที่แล้ว"){
						$post_date = dateThText($pdate[0])." ".$pdate[1];
					}else{
						// check if $cdate[4] is NOT numeric, skip this comment element.
						if(!is_numeric($pdate[0])) $post_date = mdate('%Y-%m-%d %H:%i',time());
						else
						{
							$pyear = explode(",",$pdate[3]);
							$mm = thMonth_decoder($pdate[1],'full');
							$yy = thYear_decoder($pyear[0]);
							$post_date = $yy."-".$mm."-".$pdate[0]." ".$pdate[4];
						}
					}
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
			$comments = $html->find('div[class^=window]');
			if($comments == null) $comments = $html->find('div[class=post]');
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

				$c_title = $c->find('h5[id^=subject_] a',0);
				if($c_title == null) $comment_title = null;
				else $comment_title = trim($c_title->plaintext);
				
				$c_body = $c->find('div[class=post]',0);
				if($c_body == null) $c_body = $c->find('textarea[class=bbcode]',0);
				$comment_body = htmlentities(trim($c_body->plaintext),ENT_IGNORE);
				$comment_body = strip_tags(html_entity_decode($comment_body));
				$comment_body = str_replace("nbsp;"," ",$comment_body);
				
				$c_author = $c->find('div[class=poster] h4',0);
				if($c_author == null) $c_author = $c->find('h2[class=author]',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('div[class=keyinfo] div[class=smalltext]',0);
				
				if($c_date_time != null){
					$comment_date = trim($c_date_time->plaintext);
		
					$cdate = explode(" ",$comment_date);
					$a = explode (",",$cdate[6]);

					if(trim($cdate[4]) == "เมื่อวานนี้" || trim($cdate[4]) == "วันนี้" || trim($cdate[4]) == "วันปิยมหาราชปีที่แล้ว" || trim($cdate[4]) == "วันวาเลนไทน์ปีที่แล้ว"){
						$comment_date = dateThText($cdate[4])." ".$a[0];	
					}else{
					$mm = thMonth_decoder($cdate[5],'full');
					$comment_date = $a[0]."-".$mm."-".$cdate[4]." ".$cdate[7];
					}
				}else{  
					$c_date_time = $c->find('div[class=date]',0);
					$comment_date = trim($c_date_time->plaintext);
					$cdate = str_replace("  "," ",$comment_date);
					
					$cdate = explode(" ",$cdate);
					$cdate[0] = str_replace(",","",$cdate[0]);
					
					if($cdate[0] == "วันปิยมหาราชปีที่แล้ว" || $cdate[0] == "วันวาเลนไทน์ปีที่แล้ว"){
						$comment_date = dateThText($cdate[0])." ".$cdate[1];
					}else{
						// check if $cdate[4] is NOT numeric, skip this comment element.
						if(!is_numeric($cdate[0])) continue;
						
						$cyear = explode(",",$cdate[3]);
						$mm = thMonth_decoder($cdate[1],'full');
						$yy = thYear_decoder($cyear[0]);
						$comment_date = $yy."-".$mm."-".$cdate[0]." ".$cdate[4];
					}
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
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "comment";
					$post->title = $comment_title;
					$post->body = trim($comment_body);
					$post->post_date = $comment_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($comment_author));
					//$post->insert();
		
                                        // add obj to memcache
                                        $key = rand(1000,9999).'-'.microtime(true);
                                        $memcache->add($key, $post, false, 12*60*60) or die ("Failed to save OBJECT at the server");
                                        echo '.';
                                        unset($post);
				}
				$i++;
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>