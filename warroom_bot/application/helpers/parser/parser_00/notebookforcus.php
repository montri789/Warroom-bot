<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_notebookfocus($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.maincontent, 1st h1 element
			$main_content = $html->find('tr font[color=#0066CC]');
			$post_title = trim($main_content[0]->plaintext);
			//$main_content = $html->find('div[class=maincontent] h1');
			//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			// Post Body at div.boardmsg
			$board_msg = $html->find('td table[width=95%]');
			$post_body = trim($board_msg[0]->plaintext);
			//$board_msg = $html->find('div[class=boardmsg]');
			//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			// Post Meta at ul#ownerdetail
			$author = $html->find('p[style="font: 12px/normal tahoma; color: #333333; text-transform: none; text-indent: 0px; letter-spacing: normal; word-spacing: 0px; white-space: normal; orphans: 2; widows: 2; font-size-adjust: none; font-stretch: normal; background-color: #FFFFFF; -webkit-text-size-adjust: auto; -webkit-text-stroke-width: 0px;] strong');
			$post_author0 = trim($author[0]->plaintext);
			$str = explode(":",$post_author0);
			$post_author = $str[1];
			//$author = $html->find('ul[id=ownerdetail] li b');
			//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('td[class=Text_Standard]',3);
			$post_date = trim($date_time->plaintext);

			// View Count
			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext)); 

			
			$str = explode(":",$post_date);
			$date = $str[1];
			$str1 = explode("-",$date);
			$yy = $str1[2];
			$mm = $str1[1];
			$dd0 = $str1[0];
			$dd = preg_replace("/[^0-9]/", '',$dd0);
			
			
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy."-".$mm."-".$dd;
			echo "<hr/>";
			
			
			
		}
		
		// Comments at ul#listComment li.bd1soCCC
		$comments = $html->find('hr[class=divider] div[class=inner]');

	
		foreach($comments as $c)
		{ 	
			//Comment Title as div.listCommentHead
			$c_title = $c->find('div[class=postbody] h3',0);
			$comment_title = trim($c_title->plaintext);
			//$c_title = $c->find('div[class=listCommentHead]',0);
			//$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
			
			//Comment Body as div.commentBox div.boardmsg
			$c_body = $c->find('div[class=content]',0);
			$comment_body = trim($c_body->plaintext);
			
			//Comment Author as ui#ownerdetail li b
			$c_author = $c->find('p[class=author] strong a',0);
			$comment_author = trim($c_author->plaintext);
			
			//Comment Date ul#ownerdetail li
			$c_date_time = $c->find('div[class=postbody] p[class=author]',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$str= explode(" ",$comment_date);
			$comment_author0 = $str[0];  
			$date = $str[3];
			$yy = $str[6];
			$mm = $str[4];
			$dd = $str[5];
			$tt = $str[7];
			
			/*$date = explode(" ",$comment_date);
			$yy = thYear_decoder($date[4]);
			$mm = thMonth_decoder($date[3],'full');
			$dd = $date[2];
			$tt = $date[6];*/
			
			echo "CommentTitle:".$comment_title;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$date." ".$mm." ".$dd.$yy." ".$tt;
			echo "<br>";
			echo "<hr>";
			
		}
		
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.notebookfocus.com/readnews.php?id=1367";
	
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
	
	
	parse_notebookfocus($fetch);
?>