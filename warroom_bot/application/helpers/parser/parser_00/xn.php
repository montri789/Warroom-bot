<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_xn($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('title');
			$post_title = trim($main_content[0]->plaintext);

			// Post Body at div.content_only
			$board_msg = $html->find('div[class=boardpost_right] div[style=max-width:700px; border-top:solid 1px #CCC; padding-top:10px;]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at [no have]
			$author = $html->find('div[class=boardpost_left]');
			$post_author0 = trim($author[0]->plaintext);
			$pa = explode(" ",$post_author0);
			$post_author = $pa[0];

			$date_time = $html->find('div[style=text-align:right; color:#AAA;]');
			$post_date = trim($date_time[0]->plaintext);
			
			//เมื่อ 26 ธันวาคม 2554 22:19:45

			$date = explode(" ",$post_date);
			$yy = thYear_decoder($date[3]);
			$mm = thMonth_decoder($date[2],'full');
			$dd = $date[1];
			$tt = $date[4];
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
			echo "<br/>";
			echo "<hr/>";	
		}
	

		
		$comments = $html->find('div[class=boardpost]');

	
		$i = 0;
		foreach($comments as $c)
		{ 	
		if($i > 0){
			
			$c_title = $c->find('div[style=background-color:#ffddb1; font-weight:bold; color:#ff4e00; height:25px; padding-left:5px; padding-top:5px;]');
			$comment_title = trim($c_title[0]->plaintext);	
			
			$c_body = $c->find('div[class=right_data]');
			$comment_body = trim($c_body[2]->plaintext);
			
			$c_author = $c->find('.left_data strong',0);
			$comment_author = trim($c_author->plaintext);
			
			$c_date = $c->find('div[class=right_data]');
			$comment_date = trim($c_date[1]->plaintext);
			
			//echo $comment_date."<br>";
			
			$cdate = explode(" ",$comment_date);
			$dd = $cdate[1];
			$tt = $cdate[4];
			
			//$date = explode(" ",$comment_date);
			$yy = thYear_decoder($cdate[3]);
			$mm = thMonth_decoder($cdate[2],'full');
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
		//
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.xn--72cf3a2bya5bzcff3t.com/webboard/viewpost.php?p_id=4179";
	
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
	
	parse_xn($fetch);
	//parse_dek_d($fetch);
?>