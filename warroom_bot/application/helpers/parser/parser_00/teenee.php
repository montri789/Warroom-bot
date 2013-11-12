<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_teenee($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('div[align=center] table[width=725] strong ',0);
			$post_title = trim($main_content->plaintext);

			$board_msg = $html->find('div[align=center] table[width=725] td[class=A2]',0);
			$post_body = trim($board_msg->plaintext);

			$author = $html->find('div[align=center] table[width=725] td[bgcolor=#ffffff]',0);
			$post_author = trim($author->plaintext);

			$author = explode("โดย :",$post_author);
			$pauthor = explode("โพสเมื่อ",$author[1]);
			$date = explode("[",$pauthor[1]);
			$pdate = explode("]",$date[1]);
			$fdate = explode(" ",$pdate[0]);
			
			$post_author = $pauthor[0];

 
			$yy = thYear_decoder($fdate[5]);
			$mm = thMonth_decoder($fdate[4],'full');
			$post_date = $yy."-".$mm."-".$fdate[3]." ".$fdate[7];

			if($debug)
			{
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date ;
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
	

		$comments = $html->find('blockquote[style=padding-left:70]');

		$i = 0;
	
			foreach($comments as $c)
			{ 	
				if($i > 0){
			
					$c_title = $c->find('td[bgcolor=#F5F5F5]',0);
					$comment_title = trim($c_title->plaintext);
		
					$ctitle = explode("[",$comment_title);
					$comment_title = $ctitle[0];
					
					$c_body = $c->find('td[bgcolor=#F5F5F5] table[width=95%]');
					$comment_body = trim($c_body[0]->plaintext);
					
					$c_author = $c->find('table[width=100%] td font',0);
					$comment_author = trim($c_author->plaintext);
					
					$c_date_time = $c->find('table[width=100%] td[width=150]',0);
					$s = $c_date_time->parent()->next_sibling();
					$c_date_time = $s->find('td font',0);
					
					$comment_date = trim($c_date_time->plaintext);
		
					$adate = explode("[",$comment_date);
					$time = explode("]",$adate[1]);
					$cdate = explode(" ",$time[0]);
					
					$yy = thYear_decoder($cdate[5]);
					$mm = thMonth_decoder($cdate[4],'full');
					$comment_date = $yy."-".$mm."-".$cdate[3]." ".$cdate[7];
		
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
	
	$url = "http://variety.teenee.com/foodforbrain/42776.html";
	
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

	parse_teenee($fetch,true);
?>