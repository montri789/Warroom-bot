<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_thaicybergames_board($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('div[class=postrow] h2',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('div[class=content hasad] blockquote');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('.username_container a',0);
			$post_author = trim($author->plaintext);
			if(empty($post_author)){
				$author = $html->find('.username_container .username',0);
				$post_author = trim($author->plaintext);
			}
			
			$date_time = $html->find('div[class=posthead] span',0);
			$post_date = trim($date_time->plaintext);
			
			$post_date = str_replace(",","",$post_date);
			$post_date = str_replace(array("-","&nbsp;")," ",$post_date);
			
			$date = explode(" ",$post_date);
			
			if($date[0] == "Today" || $date[0] == "Yesterday"){
				$post_date = dateEnText($date[0])." ".$date[1];
			}else{
				$post_date = $date[2]."-".$date[1]."-".$date[0]." ".$date[3];
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
	
					$c_title = $c->find('div[class=posthead] span[class=nodecontrols] a',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('div[class=content hasad] blockquote');
					$comment_body = trim($c_body[0]->plaintext);
			
					$c_author = $c->find('..username_container a',0);
					$comment_author = trim($c_author->plaintext);
					if(empty($comment_author)){
						$author = $html->find('.username_container .username',0);
						$comment_author = trim($author->plaintext);
					}
					
					$c_date_time = $c->find('div[class=posthead] span',0);
					$comment_date = trim($c_date_time->plaintext);
		
					$comment_date = str_replace(",","",$comment_date);
					$comment_date = str_replace(array("-","&nbsp;")," ",$comment_date);
					
					$date = explode(" ",$comment_date);
					
					if($date[0] == "Today" || $date[0] == "Yesterday"){
						$comment_date = dateEnText($date[0])." ".$date[1];
					}else{
						$comment_date = $date[2]."-".$date[1]."-".$date[0]." ".$date[3];
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
	
	$url = "http://www.thaicybergames.com/main/showthread.php?t=37311";
	
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
	        CURLOPT_POSTFIELDS     => $curl_data,   // this are my post vars 
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
	
	parse_thaicybergames_board($fetch,true);

?>