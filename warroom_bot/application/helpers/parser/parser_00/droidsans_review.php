<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");
	
	function parse_droidsans_review($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.body-block-header .caption',0);
			$post_title = trim($main_content->plaintext);
			
			$board_msg = $html->find('.forum-post-panel-main .content',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('.author-name');
			$post_author = trim($author[0]->plaintext);

			$date_time = $html->find('.posted-on',0);
			$post_date = trim($date_time->plaintext);
			
			$post_date = str_replace(array(",","-"),"",$post_date);
			$post_date = str_replace("/"," ",$post_date);
			$date = explode(" ",$post_date);
			
			$post_date =$date[3]."-".$date[1]."-".$date[2]." ".$date[5];
				 		
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
		
		$comments = $html->find('#forum-comments .forum-post');
	
		foreach($comments as $c)
		{ 	
			$c_title = $c->find('.postbody h3',0);
			$comment_title = trim($c_title->plaintext);
			$comment_title = (empty($comment_title)) ? "Re:".$post_title : $comment_title; 

			$c_body = $c->find('.content',0);
			$comment_body = trim($c_body->plaintext);

			$c_author = $c->find('.author-name',0);
			$comment_author = trim($c_author->plaintext);

			$c_date_time = $c->find('.posted-on',0);
			$comment_date = trim($c_date_time->plaintext);
			
			$comment_date = str_replace(array(",","-"),"",$comment_date);
			$comment_date = str_replace("/"," ",$comment_date);
			$date = explode(" ",$comment_date);
			
			$comment_date =$date[3]."-".$date[1]."-".$date[2]." ".$date[5];
			
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
		
		$html->clear();
		unset($html);
	}
	
	$url = "http://droidsans.com/angry-birds-2-years-anniversay-update";
	
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

	parse_droidsans_review($fetch,true);
?>