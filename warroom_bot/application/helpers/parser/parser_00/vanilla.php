<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_vanilla($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.mypage-box9-bbs h1',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('.p8bbsbox2',0);
			$post_body = strip_tags(trim($board_msg->plaintext));
			
			$author = $html->find('.mypage-box9-bbs .l-white a',0);
			$post_author = trim($author->plaintext);	
			
			$date = $html->find('.pbbstitle2',0);
			$post_date = trim($date->plaintext);
			
			//echo $post_date;
			//echo "</br>";
			
			$post_date = str_replace("   "," ",$post_date);
			$pd = explode(" ",$post_date);
			$pt = explode("/",$pd[2]);
			$month = explode(",",$pt[1]);
			$day = explode(",",$pt[0]);
			$year = explode(",",$pt[2]);
			//$mm = thMonth_decoder($month[0],'cut');
			//$years = thYear_decoder($year[0]);
			
			$post_date = $year[0]."-".$month[0]."-".$day[0]." ".$pd[3];
			
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

		$comments = $html->find('table[align=center] .p8bbsbox-back');
		
			foreach($comments as $k=>$c)
			{ 	
				
					//$c_title = $c->find('',0);
					//$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.pkuchi',0);
					$comment_body = trim($c_body->plaintext);
			
					$c_author = $c->find('.l-normal a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.bbsres',0);
					$comment_date = trim($c_date->plaintext);
					
					$comment_date = str_replace("   "," ",$comment_date);
					$cdate = explode(" ",$comment_date);
					$cd = explode("/",$cdate[2]);
					$month = explode(",",$cd[1]);
					$day = explode(",",$cd[0]);
					$year = explode(",",$cd[2]);
					$cday = explode(",",$cdate[1]);

					$mm = enMonth_decoder($cdate[0],'full');

					$comment_date = $year[0]."-".$month[0]."-".$day[0]." ".$cdate[3];
					
					if($debug)
					{
			
						//echo "CommentTitle:".$comment_title;
						//echo "<br>";
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
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.vanilla.in.th/bbs.cgi?type=topic&sno=1336998354";
	
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

	parse_vanilla($fetch,true);
?>