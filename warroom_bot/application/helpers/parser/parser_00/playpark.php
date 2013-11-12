<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_playpark($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('span[class=main_topic_title]');
			$post_title = trim($main_content[0]->plaintext);

			// Post Body at div.content_only
			$board_msg = $html->find('div[class=post entry-content]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at [no have]
			$author = $html->find('span[class=author vcard] a');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('abbr[class=published]');
			$post_date = trim($date_time[1]->plaintext);
			
			
			// View Count
			$page_info = $html->find('li[class=statistics_counter last]');
			$page_view = trim($page_info[0]->plaintext);
			$page_view = preg_replace("/[^0-9]/", '',$page_view);
			$date = explode(" ",$post_date);
			$yy = $date[2];
			$mm = $date[1];
			$dd = $date[0];
			$tt = $date[4];
			$ampm =$date[5];
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt." ".$ampm;
			echo "<br/>";
			echo "<hr/>";	
		}
	

		
		$comments = $html->find('div[id^=post_id_]');

		$i = 0;
		
		foreach($comments as $c)
		{ 	
			if($i > 0){
	
			$c_title = $c->find('a[title^=Link to post #]',0);
			$comment_title = trim($c_title->plaintext);
				
			$c_body = $c->find('div[class=post entry-content]',0);
			$comment_body = trim($c_body->plaintext);
			
			$c_author = $c->find('span[class=author vcard]',0);
			$comment_author = trim($c_author->plaintext);
			
			$c_date_time = $c->find('abbr[class=published]',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$date = explode(" ",$comment_date);
			$yy = $date[2];
			$mm = $date[1];
			$dd = $date[0];
			$tt = $date[4];
			$ampm =$date[5];
			
			echo "CommentTitle:".$comment_title;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt." ".$ampm;
			echo "<br>";
			echo "<hr>";
			}
			
			$i++;
		
		}
		//
		$html->clear();
		unset($html);
	}
	
	$url = "http://bbs.playpark.com/topic/159-%E0%B8%AA%E0%B8%B2%E0%B8%A2%E0%B8%95%E0%B8%A3%E0%B8%87-%E0%B8%94%E0%B8%B2%E0%B8%A7%E0%B9%83%E0%B8%95%E0%B9%89%E0%B9%80%E0%B8%87%E0%B8%B2/";
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
	
	//echo $errmsg;
	//var_dump($info);
	
	parse_playpark($fetch);
	
?>