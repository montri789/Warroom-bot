<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_taradsungha($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('title');
			$post_title = trim($main_content[0]->plaintext);

			// Post Body at div.content_only
			$board_msg = $html->find('div[class=msgtext]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at [no have]
			$author = $html->find('span[class=view-username]');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('.msgdate');
			$post_date = trim($date_time[0]->title);
			
			$cd = explode(" ",$post_date);
			$c_date = explode("/",$cd[0]);
			// View Count
			$page_info = $html->find('ul li.right');
			$page_view = trim($page_info[1]->plaintext);

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
			echo "PostDate:".$c_date[0]."-".$c_date[1]."-".$c_date[2]." ".$cd[1];
			echo "<br/>";
			echo "<hr/>";	
		}
	

		
		$comments = $html->find('tr[class=fb_sth]');

		$i = 0;
		
		foreach($comments as $c)
		{
			$r = $c->parent()->parent();
			if($i > 0){
			
				
			$c_title = $r->find('.msgtitle',0);
			$comment_title = trim($c_title->plaintext);

			$c_body = $r->find('.msgtext span',0);
			$comment_body = trim($c_body->plaintext);

			$c_author = $r->find('.view-username a',0);
			$comment_author = trim($c_author->plaintext);

			$c_date_time = $r->find('.msgdate',0);
			$comment_date = trim($c_date_time->title);
			
			$cd = explode(" ",$comment_date);
			$c_date = explode("/",$cd[0]);
			
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
			echo "CommentDate:".$c_date[0]."-".$c_date[1]."-".$c_date[2]." ".$cd[1];
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
	$url = "http://taradasungha.com/%E0%B9%80%E0%B8%9F%E0%B8%AD%E0%B8%A3%E0%B9%8C%E0%B8%99%E0%B8%B4%E0%B9%80%E0%B8%88%E0%B8%AD%E0%B8%A3%E0%B9%8C-%E0%B8%95%E0%B8%81%E0%B9%81%E0%B8%95%E0%B9%88%E0%B8%87%E0%B8%A0%E0%B8%B2%E0%B8%A2%E0%B9%83%E0%B8%99/3105-%E0%B9%82%E0%B8%9B%E0%B8%A3%E0%B9%82%E0%B8%A1%E0%B8%8A%E0%B8%B1%E0%B9%88%E0%B8%99%E0%B8%A5%E0%B8%94%E0%B8%A3%E0%B8%B2%E0%B8%84%E0%B8%B2%E0%B8%96%E0%B8%A5%E0%B9%88%E0%B8%A1%E0%B8%97%E0%B8%A5%E0%B8%B2%E0%B8%A2-%E0%B9%80%E0%B8%95%E0%B8%B2%E0%B8%9D%E0%B8%B1%E0%B8%87-Rinnai-%E0%B8%88%E0%B8%B1%E0%B8%94%E0%B8%AA%E0%B9%88%E0%B8%87%E0%B8%97%E0%B8%B1%E0%B9%88%E0%B8%A7%E0%B8%9B%E0%B8%A3%E0%B8%B0%E0%B9%80%E0%B8%97%E0%B8%A8.html";
	
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
	
	parse_taradsungha($fetch);
	//parse_dek_d($fetch);
?>