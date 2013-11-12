<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_techxcite($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.maincontent, 1st h1 element
			$main_content = $html->find('div[id=page-body] h2');
			$post_title = trim($main_content[0]->plaintext);
			//$main_content = $html->find('div[class=maincontent] h1');
			//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			// Post Body at div.boardmsg
			$board_msg = $html->find('div[class=content]');
			$post_body = trim($board_msg[0]->plaintext);
			//$board_msg = $html->find('div[class=boardmsg]');
			//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			// Post Meta at ul#ownerdetail
			$author = $html->find('p[class=author] strong a');
			$post_author = trim($author[0]->plaintext);
			//$author = $html->find('ul[id=ownerdetail] li b');
			//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('div[class=postbody] p[class=author]',0);
			$post_date = trim($date_time->plaintext);

			// View Count
			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
			
			$str= explode(" ",$post_date);
			$post_author0 = $str[3];  
			$tt = $str[4];
			$ampm = $str[5];
			$str= explode("/",$post_author0);
			$yy = $str[2];
			$mm = $str[1];
			$dd = $str[0];
			
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt." ".$ampm;
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
			$comment_author0 = $str[3]; 
			$tt = $str[4];
			$ampm = $str[5];
			$str= explode("/",$post_author0); 
			$yy = $str[2];
			$mm = $str[1];
			$dd = $str[0];
			
			
			echo "CommentTitle:".$comment_title;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$yy."-".$mm."-".$dd."".$tt." ".$ampm;
			echo "<br>";
			echo "<hr>";
			
		}
		
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.techxcite.com/forum/viewtopic.php?f=66&t=15556";
	
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
	
	
	parse_techxcite($fetch);
?>