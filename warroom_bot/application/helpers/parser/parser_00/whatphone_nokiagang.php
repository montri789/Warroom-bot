<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_whatphone_nokiagang($fetch){
		
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){

			$main_content = $html->find('div[id=page-body] h2');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('div[class=content]');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('p[class=author] strong');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('div[class=postbody] p[class=author]',0);
			$post_date = trim($date_time->plaintext);
			
			$post_date = str_replace(",","",$post_date);
			$post_date = explode("&raquo;",$post_date);
			$post_date = preg_split("/[\s]+/",trim($post_date[1]));

			if(preg_match("/^[a-zA-Z]/",$post_date[0])){
				if(trim($post_date[0]) == "Yesterday" || trim($post_date[0]) == "Today"){
					$post_date = dateThText($post_date[0])." ".$post_date[4];	
				}else{ 
					$dd = $post_date[2];	
					$mm = enMonth_decoder($post_date[1],"cut");
					$yy = $post_date[3];
					$tt = $post_date[4];
					$post_date = $yy."-".$mm."-".$dd." ".$tt;
				}
			}else{   
				if(trim($post_date[0]) == "เมื่อวานนี้" || trim($post_date[0]) == "วันนี้"){
					$post_date = dateThText($post_date[0])." ".$post_date[4];	
				}else{  
					$dd = $post_date[2];	
					$mm = thMonth_decoder($post_date[1],"cut");
					$yy = $post_date[3];
					$tt = $post_date[4];
					$post_date = $yy."-".$mm."-".$dd." ".$tt;	
				}
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

		$comments = $html->find('hr[class=divider] div[class=inner]');
	
		foreach($comments as $c){ 	
	
			$c_title = $c->find('div[class=postbody] h3',0);
			$comment_title = trim($c_title->plaintext);
			
			$c_body = $c->find('div[class=content]',0);
			$comment_body = trim($c_body->plaintext);
			
			$c_author = $c->find('p[class=author] strong',0);
			$comment_author = trim($c_author->plaintext);
			
			$c_date_time = $c->find('div[class=postbody] p[class=author]',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$comment_date = str_replace(",","",$comment_date);
			$comment_date = explode("&raquo;",$comment_date);
			$comment_date = preg_split("/[\s]+/",trim($comment_date[1]));

			if(preg_match("/^[a-zA-Z]/",$comment_date[0])){
				if(trim($comment_date[0]) == "Yesterday" || trim($comment_date[0]) == "Today"){
					$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
				}else{ 
					$dd = $comment_date[2];	
					$mm = enMonth_decoder($comment_date[1],"cut");
					$yy = $comment_date[3];
					$tt = $comment_date[4];
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				}
			}else{   
				if(trim($comment_date[0]) == "เมื่อวานนี้" || trim($comment_date[0]) == "วันนี้"){
					$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
				}else{  
					$dd = $comment_date[2];	
					$mm = thMonth_decoder($comment_date[1],"cut");
					$yy = $comment_date[3];
					$tt = $comment_date[4];
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;	
				}
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
	
	$url = "http://www.nokiagang.com/forums/ovi-t25754.html";
	
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

	parse_whatphone_nokiagang($fetch);
	
?>