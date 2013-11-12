<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("../simple_html_dom_helper.php");
	require("../date_decoder_th_helper.php");
	
	function parse_techoops($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('title');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('div[class=post]');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('div[class=poster] h4 a');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('div[class=smalltext]',0);
			$post_date = trim($date_time->plaintext);

			$str = explode(" ",$post_date);
			if(trim($str[3]) == "เมื่อวานนี้" || trim($str[3]) == "วันนี้"){
				$post_date = dateThText($str[3])." ".$str[5];	
			}else{
				$yy = $str[5];
				$mm = $str[4];
				$dd = $str[3];
				$tt = $str[7];
				$post_date = $yy."-".thMonth_decoder($mm,"full")."-".$dd." ".$tt;
			}
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$post_date;
			echo "<br/>";
			echo "<hr/>";
		}
		
		$comments = $html->find('hr[class=post_separator] div[class^=windowbg]');
	
		foreach($comments as $c)
		{ 	
			$c_title = $c->find('.keyinfo h5[id^=subject_]',0);
			$comment_title = trim($c_title->plaintext);
			
			$c_body = $c->find('div[class=post] div[class=inner]',0);
			$comment_body = trim($c_body->plaintext);
			
			$c_author = $c->find('div[class=poster] h4',0);
			$comment_author = trim($c_author->plaintext);
			
			$c_date_time = $c->find('div[class=smalltext]',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$str = explode(" ",$comment_date);
			if(trim($str[4]) == "เมื่อวานนี้" || trim($str[4]) == "วันนี้"){
				$comment_date = dateThText($str[4])." ".$str[6];	
			}else{
				$yy = $str[6];
				$mm = $str[5];
				$dd = $str[4];
				$tt = $str[8];
				$comment_date = $yy."-".thMonth_decoder($mm,"full")."-".$dd." ".$tt;
			}
		
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
		
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.techoops.com/webboard/index.php?topic=19221.0";
	
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
	
	parse_techoops($fetch);
?>