<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_yenta4($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('#topic_title',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('#topic_body',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('#topic_author_profile a',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('#topic_date acronym',0);
			$post_date = trim($date_time->plaintext);
			$post_date = str_replace("น.","",$post_date);
			
			$date = explode(" ",$post_date);
			
			$post_date = $date[5]."-".thMonth_decoder($date[4],"cut")."-".$date[3]." ".$date[0];
			
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
	
		$comments = $html->find('#comment_list li[id^=comment-]');

		$i = 0;
	
		foreach($comments as $c)
		{ 	
			
			$c_title = $c->find('.comment_seq',0);
			$comment_title = trim($c_title->plaintext);
					
			$c_body = $c->find('.comment_info_frame p',1);
			$comment_body = trim($c_body->plaintext);
					
			$c_author = $c->find('span.comment_author',0);
			$comment_author = trim($c_author->plaintext);
		
			$c_date_time = $c->find('.comment_time acronym',0);
			$comment_date = trim($c_date_time->plaintext);
			$comment_date = str_replace("น.","",$comment_date);
			$comment_date = str_replace(array("   ","  ")," ",$comment_date);
			
			$date = explode(" ",$comment_date);
			
			$comment_date = $date[3]."-".thMonth_decoder($date[2],"cut")."-".$date[1]." ".$date[0];
		
			if($debug)
			{		
				echo "CommentTitle:".$comment_title;
				echo "<br/>";
				echo "CommentBody:".$comment_body;
				echo "<br/>";
				echo "CommentAuthor:".$comment_author;
				echo "<br>";
				echo "CommentDate:".$comment_date ;
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
	
	
	$url = "http://webboard.yenta4.com/topic/513422";
	
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
	
	parse_yenta4($fetch,true);

?>