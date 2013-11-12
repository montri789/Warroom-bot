<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_klongdigital_board($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('table[width=800] table[width=100%] td[height=35]',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('.commentboard');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('table[width=800] table[width=680] td[width=510] div a',0);
			$post_author = trim($author->plaintext);;
				
			$date_time = $html->find('table[width=800] table[width=680] td[width=510]',0);
			$s = $date_time->parent()->next_sibling()->next_sibling();
			$date_time = $s->find('td div',0);
			$post_date = trim($date_time->plaintext);
			$post_date = str_replace("วันที่ ","",$post_date);
			$post_date = str_replace("-"," ",$post_date);
			$date = explode(" ",$post_date);
			
			$post_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];

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
	
		$comments = $html->find('table.comment');

		foreach($comments as $c)
		{ 		
			$c_title = $c->find('div',0);
			$comment_title = trim($c_title->plaintext);
			
			$c_body = $c->find('.commentboard',0);
			$comment_body = trim($c_body->plaintext);

			$c_author = $c->find('td[width=480] u',0);
			if(!empty($c_author)){
				$comment_author = trim($c_author->plaintext);
			}else{
				$ca = $c->find('.commentboard',0);
				$s =  $ca->parent();
				
				$c_author = $s->find('div',1);
				$comment_author = trim($c_author->plaintext);
				$comment_author = str_replace("โดย ","",$comment_author);
				$comment_author = explode(" - ",$comment_author);
				
				$date =   str_replace("-"," ",trim($comment_author[1]));
				$date = explode(" ",$date);
				
				$comment_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
				$comment_author = trim($comment_author[0]);
			}
			
			$d = $c->find('td[width=480]',0);
			if(!empty($d)){
				$s = $d->parent()->next_sibling()->next_sibling();
				
				$c_date_time = $s->find('td div',0);
				$comment_date = trim($c_date_time->plaintext);
				$comment_date = str_replace("วันที่ ","",$comment_date);
				$comment_date = str_replace("-"," ",$comment_date);
				$date = explode(" ",$comment_date);
				
				$comment_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
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
				$post->author_id = $post->get_author_id(trim($comment_author));
				$post->insert();
				unset($post);
			}
		}
		
		$html->clear();
		unset($html);
	}
	
	$url = "http://klongdigital.com/webboard/59273.html";
	
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
	
	parse_klongdigital_board($fetch,true);
?>