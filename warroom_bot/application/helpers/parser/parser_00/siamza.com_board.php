<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_siamza_board($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div[style=width....]
			$main_content = $html->find('div[id=content] th[colspan=2]',0);
			$post_title = trim($main_content->plaintext);

			// Post Body at div.lyriccontent
			$board_msg = $html->find('td[class=webboard_right]');
			$post_body = trim($board_msg[0]->plaintext);

			// Post Meta at 
			$author = $html->find('td[class=webboard_left] div[class=colleft] span',0);
			$post_author = trim($author->plaintext);;

			$date_time = $html->find('td[class=webboard_left] div[class=colleft]',0);
			$post_date = trim($date_time->plaintext);

			$date = explode(" ",$post_date);
			$pdate = explode("/",$date[4]);

			// View Count
			//$page_info = $html->find('',0);
			//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info->plaintext));

			$pview = explode(" ",$page_view);
  
			//$date = explode(" ",$post_date);
			//$yy = thYear_decoder($pdate[3]);
			$mm = enMonth_decoder($date[7],'cut');
			//$dd = $date[2];
			//$tt = $date[6];
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			//echo "ViewCount:".$pview[1];
			//echo "<br/>";
			echo "PostDate:".$pdate[2]."-".$pdate[1]."-".$pdate[0]." ".$date[6];
			echo "<hr/>";	
		}
	
	//function parse_dek_d($fetch)
	//{
		//$html = str_get_html($fetch);
		
		//$parsed_posts_count = 0;
		
		//if($parsed_posts_count == 0) // No early post and not a sub comment page
	//	{
			// Post Title at div.maincontent, 1st h1 element
//			$main_content = $html->find('div[class=maincontent] h1');
//			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			// Post Body at div.boardmsg
//			$board_msg = $html->find('div[class=boardmsg]');
//			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			// Post Meta at ul#ownerdetail
//			$author = $html->find('ul[id=ownerdetail] li b');
//			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

//			$date_time = $html->find('ul[id=ownerdetail] li',2);
//			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

			// View Count
//			$page_info = $html->find('div[class=maincontent] p span strong');
//			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

//			$date = explode(" ",$post_date);
//			$yy = thYear_decoder($date[4]);
//			$mm = thMonth_decoder($date[3],'full');
//			$dd = $date[2];
//			$tt = $date[6];
			
//			echo "PostTitle:".$post_title;
//			echo "<br/>";
//			echo "PostBody:".$post_body;
	//		echo "<br/>";
	//		echo "PostAuthor:".$post_author;
	//		echo "<br/>";
	//		echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
	//		echo "<hr/>";
			
	//	}
		
		// Comments at ul#listComment li.bd1soCCC


		$comments = $html->find('table[width=100%]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
			//Comment Title as div.listCommentHead
			$c_title = $c->find('th[colspan=2]',0);
			$comment_title = trim($c_title->plaintext);
			
			//Comment Body as div.commentBox div.boardmsg
			$c_body = $c->find('table[class=webboard]');
			$comment_body = trim($c_body[0]->plaintext);
			
			//Comment Author as ui#ownerdetail li b
			$c_author = $c->find('td[class=webboard_left] div[class=colleft] span',0);
			$comment_author = trim($c_author->plaintext);
			
			//Comment Date ul#ownerdetail li
			$c_date_time = $c->find('td[class=webboard_left] div[class=colleft]',0);
			$comment_date = trim($c_date_time->plaintext);

			$adate = explode(" ",$post_date);
			$cdate = explode("/",$adate[4]);
			
			//$date = explode(" ",$comment_date);
			//$yy = thYear_decoder($cdate[3]);
			$mm = thMonth_decoder($cdate[2],'full');
			//$dd = $date[2];
			//$tt = $date[6];
			//$dd = onlyNum($cd[1]);
			
			echo "CommentTitle:".$comment_title;
			echo "<br/>";
			echo "CommentBody:".$comment_body;
			echo "<br/>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$cdate[2]."-".$cdate[1]."-".$cdate[0]." ".$adate[6];
			echo "<hr/>";
				}
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	
	//$url = "http://www.dek-d.com/board/view.php?id=2369243";
	$url = "http://webboard.siamza.com/view.php?cat=10&id=450126";
	
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
	
	parse_siamza_board($fetch);
	//parse_dek_d($fetch);
?>