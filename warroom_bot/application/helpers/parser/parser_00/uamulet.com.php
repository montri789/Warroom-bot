<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_uamulet($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('.#lblQuestionName',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('td[style=font-size: 16px; color: #ffffff;]',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('.tx_TitleWhite ',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",str_replace(array('&nbsp;','</a>'),'',$author->plaintext)));

			$date_time = $html->find('table[width=98%] td.tx_white',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",str_replace('&nbsp;','',$date_time->plaintext)));
		
			$date = explode(' ',$post_date);
			$t = $date[3];
			$date = explode('/',$date[1]);
			$d = $date[0];
			$m = $date[1];
			$y = thYear_decoder($date[2]);
			$post_date = $y.'-'.$m.'-'.$d.' '.$t;

			if($debug)
			{
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date;
				echo "<hr/>";
			}
			else
			{
				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "post";
				$post->title = $post_title;
				$post->body = $post_body;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
			}
		}
	
		$comments = $html->find('.style1 table[width=980]');

			foreach($comments as $c){

					$comment_title = '';
					
					$c_body = $c->find('td[style=font-size: 16px; color: #ffffff;]',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",str_replace(array('&nbsp;',' '),'',$c_body->plaintext)));
		
					$c_author = $c->find('.tx_TitleWhite',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $c->find('table[width=98%] td.tx_white',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",str_replace('  ','',$c_date_time->plaintext)));	

					$date = explode(' ',$comment_date);
					$t = $date[3];
					$date = explode('/',$date[1]);
					$d = $date[0];
					$m = $date[1];
					$y = thYear_decoder($date[2]);
					$comment_date = $y.'-'.$m.'-'.$d.' '.$t;
					
					if($debug)
					{
						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<br/>";
						echo "<hr/>";
					}
					else
					{
						$post = new Post_model();
						$post->init();
						$post->page_id = $page->id;
						$post->type = "comment";
						$post->title = $comment_title;
						$post->body = trim($comment_body);
						$post->post_date = $comment_date;
						$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
						$post->author_id = $post->get_author_id(trim($author));
						$post->insert();
						unset($post);
					}
			}
		$html->clear();
		unset($html);
	}
	
	$url = "http://forum.uamulet.com/view_topic.aspx?bid=4&qid=44108";
	
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
	
	parse_uamulet($fetch,true);
?>