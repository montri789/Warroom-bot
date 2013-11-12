<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_beautyintrend($fetch,$debug = false){
		
		$html = str_get_html($fetch);
		
		//echo $html;
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){

			$main_content = $html->find('.postbody h3 a',0);
			$post_title = trim($main_content->plaintext);

			$ptitle = explode("(",$post_title);
			$pview = explode(")",$ptitle[1]);

			$board_msg = $html->find('.content',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('.author strong a',0);
			$post_author = trim($author->plaintext);

			$date_time = $html->find('p.author',0);
			$post_date = trim($date_time->plaintext);
			
			
			$date = explode(" ",$post_date);
						
			$time = @date("H:i", strtotime($date[7]." ".$date[8]));
			$post_date = $date[6]."-". enMonth_decoder($date[4],"cut")."-".str_replace(",","",$date[5])." ".$time;
		

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
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
		
			}
		}
	

		$comments = $html->find('div.post');

		$i = 0;
	
			foreach($comments as $c){ 
			
				if($i > 0){
					
					$c_title = $c->find('.postbody a',0);
					$comment_title = trim($c_title->plaintext);
				
					$c_body = $c->find('.content');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('p.author strong a',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('p.author',0);
					$comment_date = trim($c_date_time->plaintext);
		
					$cdate = explode(" ",$comment_date);
				
					$ctime = @date("H:i", strtotime($cdate[7]." ".$cdate[8]));
					$comment_date = $cdate[6]."-". enMonth_decoder($cdate[4],"cut")."-".str_replace(",","",$cdate[5])." ".$ctime;
					
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
				
				$i++;
			}
		$html->clear();
		unset($html);
	}
	

	$url = "http://www.beautyintrend.com/main_viewboard.php?post_id=024486";
	
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
	
	parse_beautyintrend($fetch,true);
?>