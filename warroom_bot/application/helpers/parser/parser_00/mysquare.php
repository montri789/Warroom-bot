<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_mysquare($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('h1');
			$post_title = trim($main_content[0]->plaintext);

			// Post Body at div.content_only
			$board_msg = $html->find('div[class=t_msgfontfix]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at [no have]
			$author = $html->find('div[class=popuserinfo] p a');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('div[class=authorinfo] em span');
			$post_date = trim($date_time[0]->plaintext);
			
			// View Count
			$page_info = $html->find('ul li.right');
			$page_view = trim($page_info[1]->plaintext);

			$str = explode(" ",$post_date);
			$yy = $str[5];
			$mm = $str[3];
			$dd = $str[4];
			$tt = $str[6]; 
			
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
	

		
		$comments = $html->find('table[id^=pid]');

		$i = 0;
		
		foreach($comments as $c)
		{ 	
		if($i > 0){
			
				
			$c_title = $c->find('',0);
			$comment_title = trim($c_title->plaintext);
				
			
		
			$c_body = $c->find('div[class=t_msgfontfix]',0);
			$comment_body = trim($c_body->plaintext);
			
			
			$c_author = $c->find('div[class=popuserinfo] p a',0);
			$comment_author = trim($c_author->plaintext);
			
		
			$c_date_time = $c->find('div[class=authorinfo] em',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$str = explode(" ",$comment_date);
			$date = $str[1];
			$tt = $str[2];
			
			
			echo "CommentTitle:".$i."#";
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$date." ".$tt;
			echo "<br>";
			echo "<hr>";
			}
			
			$i++;
		
		}
		//
		$html->clear();
		unset($html);
	}
	
	
//	$url = "http://www.dek-d.com/board/view.php?id=2369243";
	$url = "http://board.mysquare.in.th/viewthread.php?tid=82042&extra=page%3D1";
	
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
	
	parse_mysquare($fetch);
	//parse_dek_d($fetch);
?>