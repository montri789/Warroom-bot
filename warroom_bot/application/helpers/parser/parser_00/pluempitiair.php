<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_airmany($fetch)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.clear-list-mar, 1st h1 element
			$main_content = $html->find('td[class=subject]');
			$post_title = trim($main_content[0]->plaintext);

			// Post Body at div.content_only
			$board_msg = $html->find('.block-middle td[align=left]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at [no have]
			$author = $html->find('.poster span');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('.post-details span');
			$post_date = trim($date_time[0]->plaintext);
			
			$time_date = $html->find('.post-details span',1);
			$post_time = trim($time_date->plaintext);
			
			//echo $post_date."<br>";
			
			$pd = explode("/",$post_date);
			$post_time = str_replace(" ",null,$post_time);
			$pt = explode(" ",trim($post_time));
			
			$tt = $pt[0];
			$dd = $pd[0];
			$mm = $pd[1];
			$yy = $pd[2];
			
			
			
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
			echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt." ".$ampm;
			echo "<br/>";
			echo "<hr/>";	
		}
	

		
		$comments = $html->find('.board-reply-set');

  		$i = 0;
		
		foreach($comments as $c)
		{ 	
		    if($i > -1){
			
				
			$c_title = $c->find('.post-order',0);
			$comment_title = trim($c_title->plaintext);
				
			
		
			$c_body = $c->find('td[align=left]',0);
			$comment_body = trim($c_body->plaintext);
			
			
			$c_author = $c->find('.poster span',0);
			$comment_author = trim($c_author->plaintext);
			
		
			$c_date_time = $c->find('.post-details span',1);
			$comment_date = trim($c_date_time->plaintext);
			
			$c_time = $c->find('.post-details span',2);
			$comment_time = trim($c_time->plaintext);
			
			
			$cd = explode("/",$comment_date);
			$time = explode(" ",$comment_time);
			$dd = $cd[0];
			$mm = $cd[1];
			
			
			
			//$date = explode(" ",$comment_date);
			$yy = thYear_decoder($cd[2]);
			//$mm = thMonth_decoder($date[3],'full');
			//$dd = $date[2];
			//$tt = $date[6];
			
			echo "CommentTitle:".$comment_title;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$yy."-".$mm."-".$dd." ".$time[0]." ".$ampm;
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
	$url = "http://www.pluempitiair.com/store/webboard/view/Mitsu_Econno_13000_BTU-4100421-th.html";
	
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
	
	parse_airmany($fetch);
	
?>