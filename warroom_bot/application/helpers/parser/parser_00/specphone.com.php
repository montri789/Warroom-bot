<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_specphone($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('.title',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('.content',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('div.userinfo a',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));

			$date_time = $html->find('div.posthead span.date',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
		
			$date = explode("&nbsp;",str_replace(array("AM","PM"),"",$post_date));
			
			if(trim($date[0]) == "วันนี้" || trim($date[0]) == "เมื่อวานนี้"){
				$post_date = dateThText($date[0])." ".$date[1];	
			}else{
				$day = explode("-",$date[0]);
				$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
				$post_date = $y."-".$day[1]."-".$day[0]." ".$date[1];
			}

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
	
		$comments = $html->find('li[id^=post_]');

			$i = 0;
			foreach($comments as $c){
				
				if($i > 0){
					$c_title = $c->find('div.posthead span.nodecontrols a',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$c_body = $c->find('div.content',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
		
					$c_author = $c->find('div.userinfo a',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $c->find('div.posthead span.date',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));					
					
					$date = explode("&nbsp;",str_replace(array("AM","PM"),"",$comment_date));
				
					if(trim($date[0]) == "วันนี้" || trim($date[0]) == "เมื่อวานนี้"){
						$comment_date = dateThText($date[0])." ".$date[1];	
					}else{ 	
						$day = explode("-",$date[0]);
						$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
						$comment_date = $y."-".$day[1]."-".$day[0]." ".$date[1];
					}
					
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
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.specphone.com/forum/showthread.php?3230";
	
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
	
	parse_specphone($fetch,true);
?>