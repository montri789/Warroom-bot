<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_ladyissue($fetch,$debug = false){
		
		$html = str_get_html($fetch);
		
		//echo $html;
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){

			$main_content = $html->find('.topic-title',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($main_content->plaintext)));
	
			$board_msg = $html->find('div.post p',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($board_msg->plaintext)));

			$author = $html->find('#post_name',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($author->plaintext)));

			$date_time = $html->find('.post-date',0);
			$post_date = trim($date_time->plaintext);
			$post_date = explode("/",$post_date);
			$post_date = $post_date[2].'-'.$post_date[1].'-'.$post_date[0];
			
			$time = $html->find('.post-time',0);
			$post_time = trim($time->plaintext);
			$post_time = explode(", ",$post_time);
			$post_time = $post_time[1];
			
			if($debug)
			{
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date.' '.$post_time;
				echo "<hr/>";
			}
			else
			{
				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "post";
				$post->title = $post_title;
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
		
			}
		}
	

		$comments = $html->find('.member-info');

		$i = 0;
	
			foreach($comments as $c){ 
			
				if($i > 0){
					
					$par = $c->parent();
					$c_title = $par->find('');
					$comment_title = trim($c_title->plaintext);
				
					$c_body = $par->find('.webboard-content');
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_body[0]->plaintext)));
					
					$c_author = $par->find('.member-name span[id^=reply_]',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",trim($c_author->plaintext)));
					
					$c_date_time = $par->find('.post-date',0);
					$comment_date = trim($c_date_time->plaintext);
					$comment_date = explode("/",$comment_date);
					$comment_date = $comment_date[2].'-'.$comment_date[1].'-'.$comment_date[0];
			
					$ctime = $par->find('.post-time',0);
					$comment_time = trim($ctime->plaintext);
					$comment_time = explode(", ",$comment_time);
					$comment_time = $comment_time[1];
						
					if($debug)
					{	
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date.' '.$comment_time;
						echo "<br>";
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
	

	$url = "http://www.ladyissue.com/webboards/915993/%E0%B9%83%E0%B8%84%E0%B8%A3%E0%B9%80%E0%B8%84%E0%B8%A2%E0%B9%83%E0%B8%8A%E0%B9%89-smooth-e-gel-%E0%B8%9A%E0%B9%89%E0%B8%B2%E0%B8%87%E0%B9%80%E0%B8%82%E0%B9%89%E0%B8%B2%E0%B8%A1%E0%B8%B2%E0%B8%84%E0%B8%B8%E0%B8%A2%E0%B8%81%E0%B8%B1%E0%B8%99%E0%B8%AB%E0%B8%99%E0%B9%88%E0%B8%AD%E0%B8%A2%E0%B8%84%E0%B9%88%E0%B8%B0.html";
	
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
	
	parse_ladyissue($fetch,true);
?>