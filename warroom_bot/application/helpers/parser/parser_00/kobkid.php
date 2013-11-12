<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_kobkid($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) // No early post and not a sub comment page
		{
			// Post Title at div.maincontent, 1st h1 element
			$main_content = $html->find('th[colspan=2] a');
			$post_title = trim($main_content[0]->plaintext);
			//$main_content = $html->find('div[class=maincontent] h1');
			//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			// Post Body at div.boardmsg
			$board_msg = $html->find('div[class=data]');
			$post_body = trim($board_msg[0]->plaintext);
			//$board_msg = $html->find('div[class=boardmsg]');
			//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

			// Post Meta at ul#ownerdetail
			$author = $html->find('span[class=user]');
			$post_author = trim($author[0]->plaintext);
			//$author = $html->find('ul[id=ownerdetail] li b');
			//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('span[class=post_date]');
			$post_date = trim($date_time[0]->plaintext);

			 
			//2011-07-24 22:53:08
			
			$date = explode(" ",$post_date);
			$tt = $date[2];
			$date0 = $date[1];
			$date1 =explode("-",$date0);
			$yy = $date1[0];
			$mm = $date1[1];
			$dd = $date1[2];
			
		
			
			
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			echo "PostAuthor:".$post_author;
			echo "<br/>";
			echo "PostDate:".$yy." ".$mm." ".$dd." ".$tt;
			echo "<hr/>";
			
			
			
		}
		
		// Comments at ul#listComment li.bd1soCCC
		$comments = $html->find('.data');
		
		$i = 0;
		
		foreach($comments as $c)
		{
			$t = $c->prev_sibling();
			$n = $c->prev_sibling()->parent()->parent();
			if($i > 0){
			
				
			//Comment Title as div.listCommentHead
			$c_title = $c->find('div[class=postbody] h3',0);
			$comment_title = trim($c_title->plaintext);
			//$c_title = $c->find('div[class=listCommentHead]',0);
			//$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
			
			//Comment Body as div.commentBox div.boardmsg
			$comment_body = trim($c->plaintext);
			
			//Comment Author as ui#ownerdetail li b
			$c_author = $n->find('.user');
			$comment_author = trim($c_author[0]->plaintext);
			
			//Comment Date ul#ownerdetail li
			$c_date_time = $t->find('.post_date',0);
			$comment_date = trim($c_date_time->plaintext);
			
			//echo $comment_date."<br>";
			
			$str = explode(" ",$comment_date);
			$cd = explode("-",$str[1]);
			$yy = $cd[0];
			$mm = $cd[1];
			$dd = $cd[2];
			
			
			
			/*$date = explode(" ",$comment_date);
			$yy = thYear_decoder($date[6]);
			$mm = thMonth_decoder($date[4],'full');
			$dd = $date[3];
			$tt = $date[7];*/
			
			echo "CommentTitle:".$comment_title."#".$i;
			echo "<br>";
			echo "CommentBody:".$comment_body;
			echo "<br>";
			echo "CommentAuthor:".$comment_author;
			echo "<br>";
			echo "CommentDate:".$yy."-".$mm."-".$dd." ".$str[2];
			echo "<br>";
			echo "<hr>";
			}
			
			$i++;
		
		}
		
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://kobkid.com/forum/index.php/forum/show/12.40432";
	
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
	
	
	parse_kobkid($fetch);
?>