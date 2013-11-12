<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_bcoms($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('div[align=left] font',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('td[bordercolordark=white]',0);
			$post_body = strip_tags(trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext)));
			$post_body = explode(" : ",$post_body);
			$post_body = trim($post_body[0]);
			
			$author = $html->find('div[align=left] td[colspan=2]',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));	
			
			$post_author = explode(": ",$post_author);
			$post_author = trim($post_author[1]);
			
			$date = $html->find('div[align=left] td[colspan=2]',1);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date->plaintext));
			
			$post_date  = str_replace(array("  ",": "),"",$post_date);
			
	
			$pd = explode(" ",trim($post_date));
			$month = explode(",",$pd[1]);
			$day = explode(",",$pd[0]);
			$year = explode(",",$pd[2]);
			$mm = thMonth_decoder($month[0],'full');
			$years = thYear_decoder($year[0]);
			
			$post_date = $years."-".$mm."-".$day[0]." ".$pd[4];
			
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

		$comments = $html->find('table[width=98%]');
		
			
			$i =0;
			foreach($comments as $k=>$c)
			{ 	
				
				if($i > 0){
					
				
					$c_title = $c->find('b',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					$c_body = $c->find('td',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
					$comment_body = str_replace("  ","",$comment_body);
					$comment_body = explode("ตอบโดย :",$comment_body);
					$comment_body = $comment_body[0];
					$comment_body = explode(" ",$comment_body);
					unset($comment_body[0]); unset($comment_body[1]);
					
					$comment_body = implode(" ",$comment_body);
					
					
					$c_author = $c->find('table[width=100%] td',1);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					$comment_author = str_replace(array("  ",": "),"",$comment_author);
					
					$c_date = $c->find('table[width=100%] td',3);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date->plaintext));
					$comment_date = str_replace(array("  ",": "),"",$comment_date);
					
					$comment_date = str_replace("  "," ",$comment_date);
					$cdate = explode(" ",$comment_date);
					
					$cday = explode(",",$cdate[1]);

					$mm = thMonth_decoder($cday[0],'full');

					$comment_date = thYear_decoder($cdate[2])."-".$mm."-".$cdate[0]." ".$cdate[4];
					
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
	
	
	$url = "http://bcoms.net/webboard/detail.asp?id=67282";
	
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

	parse_bcoms($fetch,true);
?>