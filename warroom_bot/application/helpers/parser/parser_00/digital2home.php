<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("../date_decoder_th_helper.php");

	function parse_digital2home($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.catbg3');
			$post_title = trim($main_content[0]->plaintext);
			$pt = explode("(",$post_title);
			
			$post_title = str_replace("หัวข้อ: ","",$pt[0]);
			
			$board_msg = $html->find('.post');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('.contacts b');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('div.smalltext',1);
			$post_date = trim($date_time->plaintext);

			$pdate = explode(" ",$post_date);

			if($cdate[3] == "วันนี้" || $cdate[3] == "เมื่อวานนี้"){
				$post_date = dateThText($cdate[3])." ".$cdate[5];
			}else{
				$year = explode(",",$pdate[5]);
				$pday = explode(",",$pdate[4]);
	
				$mm = thMonth_decoder($pdate[3],'full');
				
				$post_date = $year[0]."-".$mm."-".$pday[0]." ".$pdate[6];
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

		$comments = $html->find('.bordercolor');
			
			$i = 0;
			foreach($comments as $k=>$c)
			{ 	
				if($i > 0){
					$c_title = $c->find('div[id^=subject_]',0);
					$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.post',0);
					$comment_body = trim($c_body->plaintext);
			
					$c_author = $c->find('.contacts b',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('div.smalltext',1);
					$comment_date = trim($c_date->plaintext);
					
					$cdate = explode(" ",$comment_date);
						
					
					if($cdate[4] == "วันนี้" || $cdate[4] == "เมื่อวานนี้"){
						$comment_date = dateThText($cdate[4])." ".$cdate[6];
					}else{
						$year = explode(",",$cdate[6]);
						$cday = explode(",",$cdate[5]);
	
						$mm = thMonth_decoder($cdate[4],'full');
	
						$comment_date = $year[0]."-".$mm."-".$cday[0]." ".$cdate[7];
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
						$post->insert();
						unset($post);
					}
				}
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.digital2home.com/board/index.php?topic=115.0";
	
	$options = array( 
	        CURLOPT_RETURNTRANSFER => true,         // return web page 
	        CURLOPT_HEADER         => false,        // don't return headers 
	        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
	        CURLOPT_ENCODING       => "",           // handle all encodings 
	        CURLOPT_USERAGENT      => "ThothSpider",// who am i 
	        CURLOPT_AUTOREFERER    => true,         // set referer on redirect 
	        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect 
	        CURLOPT_TIMEOUT        => 120,          // timeout on response 
	        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects 
	        CURLOPT_POST           => 0,            // i am sending post data 
	        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl 
	        CURLOPT_SSL_VERIFYPEER => false,        // 
	        CURLOPT_VERBOSE        => 1 
	    );
	
	$ch = curl_init($url);
	curl_setopt_array($ch,$options);
	$fetch = curl_exec($ch);
	$err = curl_errno($ch);
	$errmsg = curl_error($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);

	parse_digital2home($fetch,true);
?>