<?php
	function parse_siamzone_board($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_siamzone_board';

		
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

		$dead_page = $html->find('div[class=thankyou-txt box-round5] p',0);
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
				$main_content = $html->find('tr td[bgcolor=#EFEFEF]');
				$post_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
				$str = explode("(",$post_title0);
				$post_title = $str[0];

				$board_msg = $html->find('div[style=width: 600px;] p');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
				$post_body = str_replace("U+2588","",$post_body);

				$author = $html->find('table tr td div[style=margin-left:5px; width:148px; overflow:hidden;]');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
				$p_author = explode("เลขที่",$post_author);
				$post_author = $p_author[0];

				$date_time = $html->find('tr td[class=thais]',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				// $page_info = $html->find('div[class=maincontent] p span strong');
				// $page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

				$str = explode(" ",$post_date);
				$yy = thYear_decoder($str[3]);
				$mm = thMonth_decoder($str[2],"cut");
				$dd = $str[1];
				$tt = $str[5];
				$page_view =$str[8];

				$post_date = $yy."-".$mm."-".$dd." ".$tt;
				
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
					$post->post_date = mdate('%Y-%m-%d %H:%i',time());
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					$post->insert();
					unset($post);
				}
			}
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[class=thaivs]');
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

				$c_title = $c->find('table.thaim div',1);
				$comment_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
				echo $comment_title0."<br>";		
						
				$str = explode("::",$comment_title0);
				$ctitle = explode("แจ้งลบ",$str[0]);
				$comment_title = $ctitle[1];
				
				$c_body = $c->find('table.thaim div',3);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('table.thaim div',0);
				$comment_author = strip_tags(trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext)));
				
				$c_author = explode("เลขที่",$comment_author);
				$comment_author = $c_author[0];
				
				$c_date_time = $c->find('tr div.thais',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
				$str = explode(" ",$comment_date);
				$yy = thYear_decoder($str[7]);
				$mm = thMonth_decoder($str[6],"cut");
				$dd = $str[5];
				$tt = $str[9];
				$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				
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
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>