<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_siamfishing($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('div[style=float: left; padding-left:5px;] h2',0);
			$post_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('tbody tr td[width=100%]',7);
			$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('div[class=poster_col] strong',0);
			$post_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));

			$date_time = $html->find('div[style=float:left; padding:4px 5px 0px 0px;]',0);
			$post_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
		
					
			$str = explode(" ",$post_date);
			$date = $str[1];
			$tt = $str[2];
			$str2 = explode("-",$date);
			$yy = $str2[2];
			$mm = $str2[1];
			$dd = $str2[0];
			
			$post_date = thYear_decoder($yy)."-"."$mm"."-".$dd." ".$tt;
			
						
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
	

		$comments = $html->find('div[class=poster_col]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
			
					$c_title = $c->find('font[size=-1]',0);
					$comment_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
					
					$p = $c->parent()->next_sibling()->next_sibling();
					$c_body = $p->find('tbody tr td[width=100%]',0);
					$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					
					$c_author = $c->find('strong',0);
					$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $p->find('div[style=float:left; padding:4px 5px 0px 0px;]',0);
					$comment_date = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
					
					
					$str = explode(" ",$comment_date);
					$date = $str[1];
					$tt = $str[2];
					$str2 = explode("-",$date);
					$yy = $str2[2];
					$mm = $str2[1];
					$dd = $str2[0];
					
					$comment_date = thYear_decoder($yy)."-"."$mm"."-".$dd." ".$tt;
					
		
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
				$i++;
			}
		$html->clear();
		unset($html);
	}
	
	$url = "http://siamfishing.com/board/view.php?tid=649534";
	
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

	parse_siamfishing($fetch,true);
?>