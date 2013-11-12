<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_coffeemade($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
	
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('.content .h1',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('td[width=650]',0);
			$post_body = str_replace('&nbsp;','',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));

			$author = $html->find('.content div[align=right] .h3',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));

			$date_time = $author->parent();
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
		
		
			$date_time = $board_msg->parent();
			$date_time = $date_time->next_sibling();
			$date_time = explode('วันที่ลงประกาศ ',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext)));
			$date_time = explode(' ', $date_time[1]);
			$post_date = $date_time[0].' '.str_replace('&nbspIP','',$date_time[1]);


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
	
		$comments = $html->find('table[style=padding:10px;border:1px solid #18B400;]');

			foreach($comments as $c){
					
					$comment_title = '';
					
					$c_body = $c->find('tr td[colspan=2]',0);
					$comment_body = str_replace('&nbsp;','',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext)));
		
					$c_author = $c->find('div[align=right] .h3',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					
					$c_date_time = $c_body->parent();
					$c_date_time = $c_date_time->next_sibling();
		
					$c_date_time = explode('วันที่ตอบ ',trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext)));
					$c_date_time = explode(' ', $c_date_time[1]);
					$comment_date = $c_date_time[0].' '.str_replace('&nbspIP','',$c_date_time[1]);
					
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
		$html->clear();
		unset($html);
	}
	
	$url = "http://www.coffeemade.com/index.php?lay=boardshow&ac=webboard_show&WBntype=1&Category=coffeemadecom&thispage=1&No=1261786";
	
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
	
	parse_coffeemade($fetch,true);
?>