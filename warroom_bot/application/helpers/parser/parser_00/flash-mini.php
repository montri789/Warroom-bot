<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_flashmini($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('table[width=700] h2',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('div[style^=font-size]',0);
			$post_body = trim($board_msg->plaintext);
			
			$author = $html->find('table[width=700] td[align=center] h4 a',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('table[width=700] td[align=center]',0);
			$post_date = trim($date_time->plaintext);
			
			$post_date = str_replace('  ',' ',$post_date);
			$date = preg_split("/[\s,]+/",$post_date);
			
			$l = count($date);
			
			$m = thMonth_decoder($date[$l-4],'cut');
			$y = thYear_decoder($date[$l-3]);
			$t = $date[$l-2];
			$day = trim($date[$l-5]);
		
			$post_date = $y.'-'.$m.'-'.$day.' '.$t;
						
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
	
		$comments = $html->find('.sample');

			$i = 0;
			foreach($comments as $c){
				
				if($i > 0){

					$c_title = $c->find('td[align=center] b',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('div[style^=font-size]',0);
					$comment_body = trim($c_body->plaintext);
		
					$c_author = $c->find('td[align=center] h4 a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('td[align=center]',0);
					$comment_date = trim($c_date_time->plaintext);					
					
					$comment_date = str_replace('  ',' ',$comment_date);
					$comment_date = trim(str_replace('ผู้ดูแล','',$comment_date));
					
					$date = preg_split("/[\s,]+/",$comment_date);
			
					$l = count($date);
					
					$m = thMonth_decoder($date[$l-4],'cut');
					$y = thYear_decoder($date[$l-3]);
					$t = $date[$l-2];
					$day = trim($date[$l-5]);
					
					
					$comment_date = $y.'-'.$m.'-'.$day.' '.$t;
					
					if($debug)
					{
						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<br/>";
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
						$post->author_id = $post->get_author_id(trim($author));
						$post->insert();
						unset($post);
					}
				}
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	$url = "http://flash-mini.com/forums/viewboard.php?ID_TOPIC=1315&pageid=5&title=%20%E0%B8%A1%E0%B8%B2%E0%B8%A3%E0%B9%8C%E0%B8%84%E0%B8%AA%E0%B8%A7%E0%B8%94%E0%B8%A3%E0%B8%B1%E0%B8%90%E0%B8%88%E0%B8%B9%E0%B8%87%E0%B8%88%E0%B8%A1%E0%B8%B9%E0%B8%81%E0%B8%AD%E0%B8%B1%E0%B8%A2%E0%B8%81%E0%B8%B2%E0%B8%A3%20%E0%B8%AB%E0%B9%88%E0%B8%A7%E0%B8%87%E0%B8%81%E0%B8%A3%E0%B8%B0%E0%B8%9A%E0%B8%A7%E0%B8%99%E0%B8%81%E0%B8%B2%E0%B8%A3%E0%B8%A2%E0%B8%B8%E0%B8%95%E0%B8%B4%E0%B8%98%E0%B8%A3%E0%B8%A3%E0%B8%A1%E0%B9%80%E0%B8%9B%E0%B9%8B#lastcom";
	
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
	
	parse_flashmini($fetch,true);
?>