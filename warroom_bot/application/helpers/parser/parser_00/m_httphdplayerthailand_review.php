<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_httphdplayerthailand_review($fetch,$debug = false){
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('title');
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			$board_msg = $html->find('.review-lcd table[width=550]',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));			
			
			$author = $html->find('table[width=550] table[width=550] table[width=550] td',2);
			if(empty($author)){
				$author = $html->find('table[width=550] table[width=550] td',2);
			}
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
						
			$post_author = trim(str_replace("รีวิว","",$post_author));

			$date_time = $html->find('.review-lcd table[width=100%] td',3);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

			$date = explode(" ",$post_date);
			
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
			
		$comments = $html->find('td[width=575] table[width=100%] td[bgcolor=#DFDFDF]');

		$i = 0;
		
		foreach($comments as $c){ 	
				
			$c_title = $c->find('p b',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
			$c_body = $c->find('p',1);
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
	
	$url = "http://hdplayerthailand.com/review_detail.asp?topic_id=34";
	
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
	
	parse_httphdplayerthailand_review($fetch,ture);
?>