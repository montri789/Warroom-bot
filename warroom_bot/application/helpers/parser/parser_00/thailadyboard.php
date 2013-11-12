<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_thailadyboard($fetch,$debug = false){
		
		$html = str_get_html($fetch);
		
		//echo $html;
		
		$parsed_posts_count = 0;

		if($parsed_posts_count == 0){
			$topic = $html->find('div[class^=windowbg]');
				
			foreach($topic as $p){ 
				$main_content = $p->find('h5[id^=subject_]',0);
				$post_title = trim($main_content->plaintext);
	
				$board_msg = $p->find('.post .inner',0);
				$post_body = trim($board_msg->plaintext);
	
				$author = $p->find('h4 a',0);
				$post_author = trim($author->plaintext);
	
				$date_time = $p->find('.keyinfo .smalltext',0);
				$post_date = trim($date_time->plaintext);
				$post_date = explode("&#171; ตอบกลับ #1 เมื่อ: ",$post_date);
				$post_date = $post_date[1];
				$post_date = explode(" &#187;",$post_date);
				$post_date = $post_date[0];
				$post_date = str_replace(",","",$post_date);
				
				$date = explode(" ",$post_date);	
				$time = @date("H:i", strtotime($date[3]." ".$date[4]));
				$post_date = $date[2]."-".thMonth_decoder($date[0],full)."-".$date[1]." ".$time;
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
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
		
			}
		}
	

		$comments = $html->find('div[class^=windowbg]');

		
	
			foreach($comments as $c){ 
					
					$c_title = $c->find('h5[id^=subject_]',0);
					$comment_title = trim($c_title->plaintext);
				
					$c_body = $c->find('.post .inner');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('h4 a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('.keyinfo .smalltext',0);
					$comment_date = trim($c_date_time->plaintext);
					$cdate = explode(" ",$comment_date);
					$ctime = @date("H:i", strtotime($cdate[7]." ".$cdate[8]));
					$comment_date = str_replace(",","",$cdate[6])."-".thMonth_decoder($cdate[4],full)."-".str_replace(",","",$cdate[5])." ".$ctime;		
					
					if($post_date == $comment_date)
						return;
					
					if($debug)
					{	
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
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
		$html->clear();
		unset($html);
	}
	

	$url = "http://www.thailadyboard.com/index.php/topic,474.0.html";
	
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
	
	parse_thailadyboard($fetch,true);
?>