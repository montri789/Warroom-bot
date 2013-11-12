<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_pdamobiz($fetch,$debug)
	{
		$html = str_get_html($fetch);

		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) 
		{
			$main_content = $html->find('span[class=lgText]',0);
			$p_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));
			$str = explode("ชื่อกระทู้:",$p_title);
			$post_title = $str[1];

			$board_msg = $html->find('td[class=text] font[size=2]',0);
			$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('tbody td[class=bold]',1);
			$post_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));

			$date_time = $html->find('tbody tr td[class=smText]',2);
			$p_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
			$str2 = explode(" ",$p_date);
			$date = $str2[1];
			$tt = $str2[3];
			$pd = explode("-",$date);
			$dd = $pd[0];
			$mm = $pd[1];
			$yy = thYear_decoder(12);
			$post_date = $yy."-".$mm."-".$dd." ".$tt;
			
			
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

		$comments = $html->find('td[class=smText] table[width=100%]');

		$i = 0;
	
			foreach($comments as $c){ 	
				if($i > 0){
					
					//$p = $c->parent()->parent();
					
					$comment_title = '';
					
					
					$p = $c->parent()->parent()->next_sibling();
					$c_body = $p->find('td[class=text]',0);
					$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$p = $c->parent()->prev_sibling();
					$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$p->plaintext));
					
					
					//$c_date_time = $p->find('.font_add .style5',1);
					$comment_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c->plaintext));
					$str2 = explode(" ",$comment_date);
			
					$date = $str2[1];
					$tt = $str2[3];
					$pd = explode("-",$date);
					$dd = $pd[0];
					$mm = $pd[1];
					$yy = thYear_decoder(12);
					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
							
					if($debug){				
						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<br/>";
						echo "<hr/>";
					}else{
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
	

	$url = "http://pdamobiz.com/forum/forum_posts.asp?TID=381737&PN=1";
	
	$options = array( 
	        CURLOPT_RETURNTRANSFER => true,         // return web page 
	        CURLOPT_HEADER         => false,        // don't return headers 
	        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
	        CURLOPT_ENCODING       => "",           // handle all encodings 
	        CURLOPT_USERAGENT      => "Googlebot",// who am i 
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
	
	parse_pdamobiz($fetch,true);
?>