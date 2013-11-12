<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_it24hrs($fetch)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.title');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('.entry');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('.view-username');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('.meta',0);
			$post_date = trim($date_time->plaintext);
			
			//echo $post_date."<br>";
			$post_date = str_replace("  "," ",$post_date);

			$pdate = explode(" ",$post_date);
			
			//$page_view = explode(":",$page_view);
			//$view = explode (" ",$page_view[1]);
			//$year = explode(",",$pdate[6]);
			//$pday = explode(",",$pdate[5]);
			

			//$yy = thYear_decoder($year[0]);
			$mm = thMonth_decoder($pdate[2],'full');
			
			$post_date = $pdate[3]."-".$mm."-".$pdate[1];
			
			echo "PostTitle:".$post_title;
			echo "<br/>";
			echo "PostBody:".$post_body;
			echo "<br/>";
			//echo "PostView:".$post_view;
			//echo "<br/>";
			//echo "PostAuthor:".$post_author;
			//echo "<br/>";
			echo "PostDate:".$post_date;
			echo "<hr/>";	
		}

		$comments = $html->find('li[class^=fbFeedbackPost fbFirstPartPost uiListItem]');
			
			$i = 0;
			$size = count($comments);
			$last_comment = $comments[$size-1];
			foreach($comments as $k=>$c)
			{
				//$c = $b->next_sibling();
				//if($k>=$size-1) continue; 	
				if($i > 0){
					$c_title = $c->find('.msgtitle',0);
					$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.msgtext',0);
					$comment_body = trim($c_body->plaintext);
			
					$c_author = $c->find('.view-username',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.msgdate',0);
					$comment_date = trim($c_date->title);
					
					//echo $comment_date."<br>";
					
					$comment_date = str_replace("  "," ",$comment_date);
					$cd = explode(" ",$comment_date);
					$cdate = explode("/",$cd[0]);

					//$year = explode(",",$cdate[6]);
					//$cday = explode(",",$cdate[5]);

					//$mm = enMonth_decoder($cdate[3],'cut');

					$comment_date = $cdate[0]."-".$cdate[1]."-".$cdate[2]." ".$cd[1];
					
			
			
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<hr>";
				}
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.it24hrs.com/2012/mwc2012-windows8-consumer-preview-iso-release/";
	
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

	parse_it24hrs($fetch);
?>