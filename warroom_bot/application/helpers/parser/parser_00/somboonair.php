<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_varietypc($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('div[id^=subject_]');
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			// Post Body at div.content_only
			$board_msg = $html->find('td[class=windowbg] div[class=post]');
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			// Post Meta at [no have]
			$author = $html->find('td[class=windowbg] tbody tr td');
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('td[class=windowbg] div[class=smalltext]');
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time[1]->plaintext));
			$str = explode(" ",$post_date);
			$yy = $str[5];
			$mm = thMonth_decoder($str[3],'full');
			$dd = $str[4];
			$tt = $str[6]; 

			// View Count
			$page_info = $html->find('ul li.right');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

			//$date = explode(" ",$post_date);
			//$yy = thYear_decoder($date[4]);
			//$mm = thMonth_decoder($date[3],'full');
			//$dd = $date[2];
			//$tt = $date[6];/**/
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
			echo "<hr/>";	
		}
	

		
		$comments = $html->find('td[class^=windowbg]');

		$i = 0;
		
		foreach($comments as $c)
		{ 	
			if($i > 0){
			
				
			$c_title = $c->find('div[id^=subject_]',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
			
		
			$c_body = $c->find('div[class=post]',0);
			$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
			
			
			$c_author = $c->find('tbody tr td',0);
			$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
			
		
			$c_date_time = $c->find('div[class=smalltext]',1);
			$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
			$str = explode(" ",$comment_date);
			$yy = $str[6];
			$mm = thMonth_decoder($str[4],'full');
			$dd = $str[5];
			$tt = $str[7];
			
			
			//« ตอบ #1 เมื่อ: มกราคม 03, 2010, 11:18:05 AM »
			
			//$date = explode(" ",$comment_date);
			//$yy = thYear_decoder($date[4]);
			//$mm = thMonth_decoder($date[3],'full');
			//$dd = $date[2];
			//$tt = $date[6];
			
			echo "CommentTitle:".$comment_title;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
			echo "<br>";
			echo "<hr>";
			}
			
			$i++;
		
		}
		
		$html->clear();
		unset($html);
	}
	
	

	$url = "http://www.somboonair.com/webboard/index.php?PHPSESSID=502295061696553779d5262d77b94110&topic=2567.0x";
	
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
	
	parse_varietypc($fetch);
?>