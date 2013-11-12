<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_phom($fetch,$debug = false){
		
		$html = str_get_html($fetch);
			
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){

			$main_content = $html->find('td h2',0);
			$post_title = trim($main_content->plaintext);

			$ptitle = explode("(",$post_title);
			$pview = explode(")",$ptitle[1]);

			$board_msg = $html->find('td div',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('td h4',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('.sample table[width=700] td',0);
			$post_date = trim($date_time->plaintext);
			
			$date = preg_split("/[\s,]+/",$post_date);
			$len = count($date);
			
			$day = substr($date[$len-5],-2,2);
			$day  = ereg_replace("[^0-9]", "",$day);
		
			$post_date = thYear_decoder($date[$len-3])."-". thMonth_decoder($date[$len-4],"cut")."-".$day." ".$date[$len-2];	

			if($debug){
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date;
				echo "<hr/>";
			}else{
				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "post";
				$post->title = $post_title;
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
			}
		}	

		$comments = $html->find('.sample table[width=700] tbody');

		$i = 0;
	
		foreach($comments as $c){ 
		
			if($i > 0){

				$comment_title = "";
				
				$c_title = $c->find('b',0);
				$comment_title = trim($c_title->plaintext);
				$comment_title = str_replace(array("(",")"),"",$comment_title);
							
				$c_body = $c->find('div[style=font-size:0.9em]',0);
				$comment_body = trim($c_body->plaintext);
				
				if(!empty($comment_body)) {
					
					$c_author = $c->find('h4 b',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('h4',0);
					$comment_date = trim($c_date_time->plaintext);
					
					$c_date = preg_split("/[\s,]+/",$comment_date);
					
					$len = count($c_date);	
					$comment_date = thYear_decoder($c_date[$len-3])."-". thMonth_decoder($c_date[$len-4],"cut")."-".$c_date[$len-5]." ".$c_date[$len-2];				
					if($debug)
					{	
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
			}
			
			$i++;
		}
		$html->clear();
		unset($html);
	}

	$url = "http://www.phom.info/board/viewboard.php?ID_TOPIC=21&title=%20%E0%B8%9C%E0%B8%A1%E0%B8%AA%E0%B8%B1%E0%B9%89%E0%B8%99%20%E0%B8%97%E0%B8%B3%E0%B8%9C%E0%B8%A1%E0%B8%97%E0%B8%A3%E0%B8%87%E0%B9%84%E0%B8%AB%E0%B8%99%E0%B8%97%E0%B8%B5%E0%B9%88%E0%B9%80%E0%B8%AB%E0%B8%A1%E0%B8%B2%E0%B8%B0";
	
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
	
	parse_phom($fetch,true);
?>