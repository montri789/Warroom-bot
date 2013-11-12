<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_zoneza($fetch,$debug)
	{
		$html = str_get_html($fetch);
			
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('div[id=content] h1',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('div[id=content]',0);
			$post_body = strip_tags(trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));
			
			$author = $html->find('div[id=postContentDesc] span[id=postbyname]',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));	
			
			$post_author = explode(": ",$post_author);
			
			$view = $html->find('div[id=postContentDesc] span[id=PageViewShw]',0);
			$post_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$view->plaintext));
			
			$post_view = explode(": ",$post_view);
			
			$date = $html->find('div[id=postContentDesc] span[id=postdate]',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date->plaintext));

			$pd = explode(" ",trim($post_date));
			$pt = explode("/",$pd[1]);
			$month = explode(",",$pt[1]);
			$day = explode(",",$pt[0]);
			$year = explode(",",$pt[2]);
			$years = thYear_decoder($year[0]);
			
			$post_date = $years."-".$month[0]."-".$day[0]." ".$pd[2];
			
			if($debug)
			{
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostView:".$post_view[1];
				echo "<br/>";
				echo "PostAuthor:".$post_author[1];
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

		$comments = $html->find('.commentTB');
		
			
			foreach($comments as $k=>$c)
			{ 	
				
				
					$c_title = $c->find('.LeftTDcommentTB',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					$c_body = $c->find('.RightTDcommentTB .CommentMessShw',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					$comment_body = trim(str_replace("&nbsp;"," ",$comment_body));
					
					
					
					$c_author = $c->find('.RightTDcommentTB .PosterNameShw',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					$comment_author = trim(str_replace("โดย :","",$comment_author));
					
					$c_date = $c->find('.RightTDcommentTB .CommentDTShw',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date->plaintext));
					$comment_date = trim(str_replace(array("วันที่ ","/")," ",$comment_date));
					
					$cdate = explode(" ",$comment_date);

					$comment_date = thYear_decoder($cdate[2])."-".$cdate[1]."-".$cdate[0]." ".$cdate[3];
					
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
		$html->clear();
		unset($html);
	}
	
	
	$url = "http://www.zoneza.com/view7801.htm";
	
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

	parse_zoneza($fetch,true);
?>