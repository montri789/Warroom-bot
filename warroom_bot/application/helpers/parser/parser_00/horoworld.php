<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_horoworld($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('title',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('div[class=t_fsz]',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('div[class=authi]',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('div[class=pi] div[class=authi]',1);
			$p_date = trim($date_time->plaintext);
			
			$str = explode(" ",$p_date);
			$date = $str[1];
			$tt = $str[2];
			$post_date = $date." ".$tt;
			
			
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
	

		$comments = $html->find('div[id^=post_] div[class=pct]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
			
					$c_title = $c->find('a[class=postcounter]',0);
					$comment_title = trim($c_title->plaintext);
					
					$comment_body = trim($c->plaintext);
					
					
					$p = $c->parent()->prev_sibling();
					$c_author = $p->find('div[class=authi]',0);
					$comment_author = trim($c_author->plaintext);
					
					$p = $c->prev_sibling();
					$c_date_time = $p->find('div[class=authi]',0);
					$c_date = trim($c_date_time->plaintext);
			
					$str = explode(" ",$p_date);
					$date = $str[1];
					$tt = $str[2];
					$comment_date = $date." ".$tt;
					
					
		
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
	
	$url = "http://webboard.horoworld.com/topics/7657-1-1-%E0%B8%94%E0%B8%B9%E0%B8%94%E0%B8%A7%E0%B8%87%E0%B8%9F%E0%B8%A3%E0%B8%B5+%E0%B8%81%E0%B8%B1%E0%B8%9A+%E0%B8%AD+%E0%B9%82%E0%B8%AB%E0%B8%99%E0%B9%88%E0%B8%87+%E0%B8%A3%E0%B8%B1%E0%B8%95%E0%B8%95%E0%B8%B4%E0%B8%81%E0%B8%B2%E0%B8%A5.html";
	
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

	parse_horoworld($fetch,true);
?>