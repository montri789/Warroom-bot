<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_thaiproperty($fetch,$debug = false){
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){			
			$main_content = $html->find('table[width=100%] h3',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $main_content->parent();
			$post = trim($board_msg->plaintext);			
			
			$post = explode(" จากคุณ ",$post);
			$post_body = $post[0];
			
			$post = explode(" วันที่ ",$post[1]);
		
			$post_author = trim($post[0]);
						
			$date = explode(" ",trim($post[1]));			
			$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1],"full")."-".$date[0];
			
			if($debug){							
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date;
				echo "<br/>";
				echo "<hr/>";
			}else{
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
			
		$comments = $html->find('table[width=100%] table[width=100%] td[align=left] table[cellpadding=4]');
		
		$i = 0;
		foreach($comments as $c)
		{ 		
			if($i > 0){
				$comment_title = "RE : ".$post_title;
						
				$c_body = $c->find('td',0);
				$comment_body = trim($c_body->plaintext);
				
				$comment = $c_body->parent()->next_sibling()->plaintext;
				$comment = str_replace("จากคุณ ","",$comment);
				$comment = explode(" - ",$comment);
				
				$comment_author  = trim($comment[0]);
									
				$date = str_replace("/"," ",trim($comment[1]));
				
				$date = explode(" ",$date);			
				$comment_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
							
				if($debug){			
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<br>";
					echo "<hr>";
				}else{
					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "comment";
					$post->title = $comment_title;
					$post->body = trim($comment_body);
					$post->post_date = $comment_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($comment_author));
					$post->insert();
					unset($post);
				}
			}
			$i++;
		}
	
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.thaiproperty.in.th/Webboard.aspx?TopicID=2184";
	
	$options = array( 
	        CURLOPT_RETURNTRANSFER => true,         // return web page 
	        CURLOPT_HEADER         => false,        // don't return headers 
	        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
	        CURLOPT_ENCODING       => "",           // handle all encodings 
	        CURLOPT_USERAGENT      => "Google bot",// who am i 
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
	
	parse_thaiproperty($fetch,ture);
?>