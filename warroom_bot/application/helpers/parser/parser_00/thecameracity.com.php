<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_thecameracity($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.first');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('.content');
			$post_body = trim($board_msg[0]->plaintext);

			$author = $html->find('.author strong');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('.author',0);
			$post_date = trim($date_time->plaintext);
			
			$post_date = str_replace("  "," ",$post_date);
			$post_date = str_replace(",","",$post_date);
			$pdate = explode(" ",$post_date);
			
			$l = count($pdate);
			
			$mm = thMonth_decoder($pdate[$l-5],'cut');
			
			$post_date = $pdate[$l-3]."-".$mm."-". $pdate[$l-4]." ".$pdate[$l-2];
			
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

		$comments = $html->find('div[class^=post bg]');
			
			$i = 0;
			foreach($comments as $k=>$c)
			{ 	
				if($i > 1){
					$c_title = $c->find('.postbody h3',0);
					$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.content',0);
					$comment_body = trim($c_body->plaintext);
			
					$c_author = $c->find('.author strong',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date = $c->find('.author',0);
					$comment_date = trim($c_date->plaintext);
					
					$comment_date = str_replace("  "," ",$comment_date);
					$comment_date = str_replace(",","",$comment_date);					
					$cdate = explode(" ",$comment_date);
					
					$l = count($cdate);

					$mm = thMonth_decoder($cdate[$l-5],'cut');

					$comment_date = $cdate[$l-3]."-".$mm."-". $cdate[$l-4]." ".$cdate[$l-2];
								
					if($debug)
					{
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
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
	
	$url = "http://www.thecameracity.com/webboard/viewtopic.php?f=6&t=8733";
	
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

	parse_thecameracity($fetch,true);
?>