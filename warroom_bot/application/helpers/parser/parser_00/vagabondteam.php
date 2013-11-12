<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_vagabondteam($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.msgtitle',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('.msgtext',0);
			$post_body = strip_tags(trim($board_msg->plaintext));
			
			$author = $html->find('.view-username a',0);
			$post_author = trim($author->plaintext);	
			
			$date = $html->find('.msgdate',0);
			$post_date = trim($date->title);
			
			$pd = explode("/",$post_date);
			$pt = explode(" ",$pd[2]);
			$month = explode(",",$pd[1]);
			$day = explode(",",$pt[0]);
			$year = explode(",",$pd[0]);
			//$mm = thMonth_decoder($month[0],'full');
			
			$post_date = $year[0]."-".$month[0]."-".$day[0]." ".$pt[1];
			
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

		$comments = $html->find('#fb_views table[width=100%] .fb_sth');
		
		$as = $comments;
		
		$i = 0;
		foreach($comments as $k=>$c)
		{ 		
			if($i > 0){
				
				$c = $c->parent()->parent();
				
				$c_title = $c->find('.msgtitle',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('.msgtext',0);
				$comment_body = trim($c_body->plaintext);
		
				$c_author = $c->find('.view-username',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date = $c->find('.msgdate',0);
				$comment_date = trim($c_date->title);
				$cdate = str_replace("/","-",$comment_date);
				
				$comment_date = $cdate;
				
				if($debug){
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
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
			$i++;
		}
		$html->clear();
		unset($html);
	}
	
	$url = "http://vagabondteam.com/index.php/component/option,com_kunena/Itemid,81/catid,33/func,view/id,167775/";
	
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

	parse_vagabondteam($fetch,true);
?>