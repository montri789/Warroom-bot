<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("../../simple_html_dom_helper.php");
	require("../../date_decoder_th_helper.php");
	
	function parse_car2care($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{

			$main_content = $html->find('span[style=color: #000000]');
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			$board_msg = $html->find('div[id=rDetail]');
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			$author = $html->find('tr[bgcolor=#FFCC00] strong');
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('table[width=800] td[align=left] table[width=100%] td[align=left] .tsmall',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
		
			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
			
			$post_date = str_replace(array("&nbsp; Posted:","AT"),"",$post_date);
			$date = explode(" ",trim($post_date));
			$post_date  = $date[2]."-".enMonth_decoder($date[1],"full")."-".$date[0]." ".$date[4];
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$post_date;
			echo "<hr/>";	
		}
		
		$comments = $html->find('table[width=100%] td[width=150]');
		
 		$i = 0;
        foreach($comments as $c)
      	{
			if($i > 0){
				$e = $c->next_sibling();
				
				$c_title = $e->find('.tsmall',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
	
				$c_body = $e->find('#rDetail',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('td[align=left] strong',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $e->find('.tsmall',2);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
				$comment_date = str_replace(array("&nbsp;Posted:","AT"),"",$comment_date);
				$date = explode(" ",$comment_date );
	
				$comment_date  = $date[3]."-".enMonth_decoder($date[2],"full")."-".$date[1]." ".$date[5];
				
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
	
	$url = "http://www.car2care.com/forum/viewtopic.aspx?t=57683";
	
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
	
	parse_car2care($fetch);
?>