<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("../date_decoder_th_helper.php");
	
	function parse_pramool($fetch,$debug)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) 
		{
			$main_content = $html->find('title');
			$post_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
			$str = explode(" - ",$post_title0);
			$post_title = $str[0];
			
			$board_msg = $html->find('hr font[size=2]');
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
			$author = $html->find('a[name=0] a');
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

			$date_time = $html->find('td[valign=top] font[size=1]',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

			$str = explode(",",$post_date);
			$date = $str[0];
			$oth = $str[1];
			$str1 = explode("-",$date);
			$yy = $str1[2];
			$mm = $str1[1];
			$dd0 = $str1[0];
			$dd = preg_replace("/[^0-9]/", '',$dd0);
			$str2 = explode(" ",$oth);
			$tt = $str2[1];			
			
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
				
		$comments = $html->find('tr');

		$i = 0;
	       
		foreach($comments as $c)
		{    
		    if($i > 4){ 
				
				$c_title = $c->find('a[name^=]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				$comment_title = explode(". ",$comment_title);
				$comment_title = $comment_title[0];
				
				$c_body = $c->find('hr font[size=2]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('a[name^=] a',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $c->find('td[valign=top] font[size=1]',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
				$str = explode(",",$comment_date);
				$date = $str[0];
				$oth = $str[1];
				$str1 = explode("-",$date);
				$yy = $str1[2];
				$mm = $str1[1];
				$dd0 = $str1[0];
				$dd = preg_replace("/[^0-9]/", '',$dd0);
				$str2 = explode(" ",$oth);
				$tt = $str2[1];
				
				//$comment_title = $i-4;
				
				if($debug)
				{
				echo "CommentTitle:".$comment_title;
				echo "<br>";
				echo "CommentBody:".$comment_body;
				echo "<br>";
				echo "CommentAuthor:".$comment_author;
				echo "<br>";
				echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
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
	
	
	$url = "http://bbs.pramool.com/webboard/view.php3?katoo=s21834";
	
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
	
	parse_pramool($fetch,true);
?>