<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("../simple_html_dom_helper.php");
	require("../date_decoder_th_helper.php");
	
	function parse_unlimitpc($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			
			$main_content = $html->find('span[class=navbar] strong');
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			$board_msg = $html->find('div[id^=post_message_]');
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			$author = $html->find('div a[class=bigusername]');
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('td[class=thead]');
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time[0]->plaintext));

			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

			$post_date = str_replace(array("AM","PM"),"",$post_date);
			$str = explode(",",$post_date);
			$date = $str[0];
			$time = $str[1];
			$str1 = explode("-",$date);
			$yy = $str1[2];
			$mm = $str1[1];
			$dd = $str1[0];
			
			$post_date = $yy."-".$mm."-".$dd." ".$time;
				
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$post_date;
			echo "<hr/>";
		}

		$comments = $html->find('table[id^=post]');
	
		$i = 0;
       
        foreach($comments as $c)
        {    
            if($i > 0){ 
				$c_title = $c->find('td[style=font-weight:normal; border: 0px solid #efefef; border-left: 0px]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
				$c_body = $c->find('div[id^=post_message_]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('.bigusername',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
							$c_date_time = $c->find('td[class=thead]',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
				$comment_date = str_replace(array("AM","PM"),"",$comment_date);
				$str = explode(",",$comment_date);
				$date = $str[0];
				$time = $str[1];
				$str = explode("-",$date);
				$yy = $str[2];
				$mm = $str[1];
				$dd = $str[0];
				
				$comment_date = $yy."-".$mm."-".$dd." ".$time;
				
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
           
            $i++;
		}
		
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.unlimitpc.com/gigotalk/showthread.php?t=14448";
	
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

	parse_unlimitpc($fetch);
	
?>