<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_thaiandriodphone($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) 
		{
			$main_content = $html->find('div[id=body_02] div[id=postlist] h1[class=ts] a',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('div[id=body_02] div[id=ct] td[class=plc] td[class=t_f]',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('div[id=body_02] div[id=ct] td[class=pls] div[class=authi] a',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('div[id=body_02] div[id=ct] td[class=plc] div[class=authi] em',0);
			$post_date = trim($date_time->plaintext);
			$post_date = str_replace("โพสต์เมื่อ ","",$post_date);
			
			$date = explode("&nbsp;",$post_date);	
				
			if($date[1] == "ชั่วโมงก่อน" || $date[1] == "นาทีก่อน" || $date[1] == "วินาทีก่อน"){
				$post_date = dateThText($date[1],$date[0]);
			}else if($date[1] == "วันก่อน"){
				$post_date = dateThText($date[0]);
			}else if($date[0] == "วันนี้" || $date[0] == "เมื่อวาน" || $date[0] == "เมื่อวานซืน"){
				$post_date = dateThText($date[0])." ".$date[1];
			}
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$post_date;
			echo "<hr/>";	
		}

		$comments = $html->find('table[id^=pid]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
		
					$c_title = $c->find('.pi strong em',0);
					$comment_title = trim($c_title->plaintext);
					
					$c_body = $c->find('div[class=pct] td[class=t_f]',0);
					$comment_body = trim($c_body->plaintext);
					
					$c_author = $c->find('td[class=pls] div[class=authi] a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('td[class=plc] div[class=pi] em',0);
					$comment_date = trim($c_date_time->plaintext);
		
					$comment_date = str_replace("โพสต์เมื่อ ","",$comment_date);
			
					$date = explode("&nbsp;",$comment_date);
				
					if($date[1] == "ชั่วโมงก่อน" || $date[1] == "นาทีก่อน" || $date[1] == "วินาทีก่อน"){
						$comment_date = dateThText($date[1],$date[0]);
					}else if($date[1] == "วันก่อน"){
						$comment_date = dateThText($date[0]);
					}else if($date[0] == "วันนี้" || $date[0] == "เมื่อวาน" || $date[0] == "เมื่อวานซืน"){
						$comment_date = dateThText($date[0])." ".$date[1];
					}
					
					echo "CommentTitle:".$comment_title;
					echo "<br/>";
					echo "CommentBody:".$comment_body;
					echo "<br/>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<hr/>";
				}
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.thaiandroidphone.com/thread-27947-1-1.html";
	
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
	
	parse_thaiandriodphone($fetch,true);
?>