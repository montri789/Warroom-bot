<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_zabzaa($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('h2 a');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('div[class=content]');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('div[class=profile-top] p a');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('p[class=author]',0);
			$post_date = trim($date_time->plaintext);

			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
		 
			$str = explode(" ",$post_date);
			$yy = $str[3];
			$mm = thMonth_decoder($str[2],"cut");
			$dd = $str[1];
			$tt = $str[4];
			
			$post_date = $yy."-".$mm."-".$dd." ".$tt;
			
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
		
		$comments = $html->find('div[class=post-content]');

		$i=0;

		foreach($comments as $c)
		{ 	
			if($i > 0){
				
				$c_title = $c->find('h3 a',0);
				$comment_title = trim($c_title->plaintext);
	
				$c_body = $c->find('div[class=content]',0);
				$comment_body = trim($c_body->plaintext);
				
				$c_author = $c->find('p[class=author] a',1);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('p[class=author]',0);
				$comment_date = trim($c_date_time->plaintext);
				$comment_date = str_replace("  "," ",$comment_date);
				
				$str = explode(" ",$comment_date);
				$yy = $str[3];
				$mm = thMonth_decoder($str[2],"cut");
				$dd = $str[1];
				$tt = $str[4];
				
				$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				
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
	
	$url = "http://zabzaa.com/community/viewtopic.php?f=7&t=36658";
	
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
	
	parse_zabzaa($fetch,true);
?>