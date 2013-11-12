<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_soul4street($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('title',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('blockquote[class=postcontent restore]',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('div[class=popupmenu memberaction] strong',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('span[class=date]',0);
			$post_date = trim($date_time->plaintext);
			$str = explode("&nbsp;",$post_date);
			
			if($str[0] == 'Yesterday' || $str[0] == 'Today' )
			{
				$post_date = dateEnText($str[0])." ".$str[1];
			}
			else
			{
				$str = explode(" ",$post_date);
				$time = explode("&nbsp;",$str[2]);
				$yy = $time[0];
				$tt = $time[1];
				$post_date = $yy."-".enMonth_decoder($str[1])."-".str_replace("th","",$str[0])." ".$tt;
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
	

		$comments = $html->find('li[id^=post_]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
			
					$c_title = $c->find('a[class=postcounter]',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('div[id^=post_message_]');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('div[class=username_container] strong',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('span[class=date]',0);
					$comment_date = trim($c_date_time->plaintext);
		
					$str = explode("&nbsp;",$comment_date);
			
					if($str[0] == 'Yesterday' || $str[0] == 'Today' )
					{
						$comment_date = dateEnText($str[0])." ".$str[1];
					}
					else
					{
						$str = explode(" ",$post_date);
						$time = explode("&nbsp;",$str[2]);
						$yy = $time[0];
						$tt = $time[1];
						$comment_date = $yy."-".enMonth_decoder($str[1])."-".str_replace("th","",$str[0])." ".$tt;
					}
		
					if($debug)
					{
						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<hr/>";
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
	
	$url = "http://www.soul4street.net/forums/showthread.php?5938-quot-Rola-Takizawa-quot-%E0%B9%80%E0%B8%98%E0%B8%AD%E0%B9%80%E0%B8%81%E0%B8%B4%E0%B8%94%E0%B8%A1%E0%B8%B2%E0%B9%80%E0%B8%9E%E0%B8%B7%E0%B9%88%E0%B8%AD%E0%B8%86%E0%B9%88%E0%B8%B2-Miyabi-15";
	
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

	parse_soul4street($fetch,true);
?>