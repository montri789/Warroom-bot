<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_clipmass($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('#container .big-text',0);
			$post_title = trim($main_content->plaintext);
			$post_title = str_replace("กระทู้: ","",$post_title);
			
			$board_msg = $html->find('.detail',2);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('#container .small',0);
			$post_author = trim($author->plaintext);

			$pauthor = explode("โพสโดย",$post_author);
			$post_author = $pauthor[1];
			
			$date_time = $html->find('#container .small',1);
			$post_date = trim($date_time->plaintext);

			$date = explode(" ",$post_date);

			$yy = thYear_decoder($date[3]);
			$mm = thMonth_decoder($date[2],'cut');
			$post_date = $yy."-".$mm."-".$date[1]." ".$date[5];
			
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

		$comments = $html->find('div[style^=margin: 0px 100px 0px 130px;]');

		foreach($comments as $c)
		{ 	
			$c_title = $c->find('div[class=label]',0);
			$comment_title = trim($c_title->plaintext);

			$ctitle = explode("[",$comment_title);
			
			$c_body = $c->find('div[class=detail]');
			$comment_body = trim($c_body[0]->plaintext);
			
			$c_author = $c->find('div[class=small]',0);
			$comment_author = trim($c_author->plaintext);
			
			$c_date_time = $c->find('div[style^=font-size: 12px; color]',1);
			$comment_date = trim($c_date_time->plaintext);

			$cdate = explode(" ",$comment_date);

			$yy = thYear_decoder($cdate[3]);
			$mm = thMonth_decoder($cdate[2],'cut');
			
			$comment_date = $yy."-".$mm."-".$cdate[1]." ".$cdate[5];

			if($debug)
			{
				echo "CommentTitle:".$ctitle[0];
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
	
	
	$url = "http://forum.clipmass.com/board/forum_read.php?id=8065";
	
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

	parse_clipmass($fetch,true);

?>