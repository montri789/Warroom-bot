<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_siamsport($fetch,$debug = false){
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('td[height=20] div[class=style5] strong',0);
			$post_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
			$str = explode(": ",$post_title0);
			$post_title = $str[1];

			$board_msg = $html->find('div[class=board_content_div]',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('span[class=tx_mag]',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));

			$date = $html->find('span[class=tx_time]',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date->plaintext));		
  
			$str0 = explode(" ",$post_date);
			$date = $str0[2];
			$str = explode("/",$date);
			$yy = $str[2];
			$mm = $str[1];
			$dd = $str[0];
			
			if($debug)
			{
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:" .thYear_decoder($yy)."-".$mm."-".$dd;
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
			
		$comments = $html->find('table[id^=item_]');

		$i = 0;
		
		foreach($comments as $c){ 	
				
			$c_title = $c->find('strong',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
			$c_body = $c->find('div[class=board_content_div]',0);
			$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
			
			$c_author = $c->find('.tx_mag',0);
			$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
			
			$c_date = $c->find('td[class=classname]',0);
			$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date->plaintext));
			
			$str0 = explode(" ",$comment_date);
			$date = $str0[2];
			$str = explode("/",$date);
			$yy = $str[2];
			$mm = $str[1];
			$dd = $str[0];
			
			$comment_date = thYear_decoder($yy)."-".$mm."-".$dd;						
			
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
	
	$url = "http://smethailandclub.com/board-view.php?id=38&gid=12";
	
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