<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_siamsport($fetch,$debug = false){
		$html = str_get_html($fetch);
		
		
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('h1[class=h_content fGray5]');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('div[class=pageContent]',0);
			$post_body = trim($board_msg->plaintext);			
					
			$post_author = "admin";
						
			$date_time = $html->find('div[class=status]',0);
			$post_date = trim($date_time->plaintext);

			$post_date = str_replace("Posted: ","",$post_date); 		
			$date = explode(" ",$post_date);
			
			$post_date = $date[2]."-".enMonth_decoder($date[1],"cut")."-".$date[0];
			
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
			
		$comments = $html->find('div[class=message]');

		//$i = 0;
		
		foreach($comments as $c){ 	
				
			$c_title = $c->find('strong',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
			$c_body = $c->find('.board_content_div',1);
			$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
			
			$c_author = $c->find('p',2);
			$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
			$comment_author = str_replace("โดย:","",$comment_author);		
			
			$comment = explode(" เมื่อ: ",$comment_author);
			$comment_author = trim($comment[0]);
			
			$date = str_replace("/"," ",$comment[1]);
			$date = explode(" ",$date);
			
			$comment_date = $date[2]."-".$date[1]."-".$date[0]." ".$date[3];
						
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
	
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.i3.in.th/content/view/7849";
	
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
	
	parse_siamsport($fetch,ture);
?>