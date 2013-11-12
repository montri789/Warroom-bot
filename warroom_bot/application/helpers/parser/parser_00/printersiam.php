<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_printersiam($fetch,$debug)
	{
		$html = str_get_html($fetch);

		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0) 
		{
			$main_content = $html->find('.textdefault .textboard',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('.textdefault .textboard',2);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
			$post_body = trim(str_replace("&nbsp;"," ",$post_body));

			$author = $html->find('.textdefault .FontMenu',6);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
			
			$date_index = 8;
			if($post_author == "&nbsp;"){
				$author = $html->find('.textdefault .FontMenu',7);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$date_index = 9;
			}
			
			$post_author = explode("&nbsp;&nbsp;&nbsp;",$post_author);
			$post_author = $post_author[0];
			$date_time = $html->find('.textdefault .FontMenu',$date_index);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
			
			$post_date = str_replace(array("[","]","&nbsp;","AM","PM"),"",$post_date);
			$post_date = explode("IP",$post_date);
		
			$date = explode(" ",trim($post_date[0]));		
			$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1])."-".$date[0]." ".$date[3];
			
			if($debug){
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date;
				echo "<hr/>";
			}else{
				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "post";
				$post->title = $post_title;
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
			}
		}

		$comments = $html->find('table[width="80%"] .textdefault');

		$i = 0;
	
		foreach($comments as $c){ 				
				$c_title = $c->find('.FontMenu',1);		
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				$comment_title = str_replace("&nbsp;","",$comment_title);
					
				$c_body = $c->find('.textboard',1);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				$comment_body = trim(str_replace("&nbsp;"," ",$comment_body));
					
				$c_author = $c->find('.FontMenu',9);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_index = 11;
				if($comment_author == "&nbsp;"){
					$c_author = $c->find('.FontMenu',10);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
					$c_date_index = 12;
				}
				
				$comment_author = explode("&nbsp;&nbsp; ",$comment_author);
				$comment_author = $comment_author[0];
					
				$c_date_time = $c->find('.FontMenu',$c_date_index);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				$comment_date = str_replace(array("[","]","&nbsp;","AM","PM"),"",$comment_date);
				$comment_date = explode("IP",$comment_date);
		
				$date = explode(" ",trim($comment_date[0]));		
				$comment_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1])."-".$date[0]." ".$date[3];
							
				if($debug){				
					echo "CommentTitle:".$comment_title;
					echo "<br/>";
					echo "CommentBody:".$comment_body;
					echo "<br/>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<br/>";
					echo "<hr/>";
				}else{
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
	

	$url = "http://printersiam.com/_block/webboard/webboard_reply.asp?WebboardGroupId=1&id=2427&menuid=3";
	
	$options = array( 
	        CURLOPT_RETURNTRANSFER => true,         // return web page 
	        CURLOPT_HEADER         => false,        // don't return headers 
	        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
	        CURLOPT_ENCODING       => "",           // handle all encodings 
	        CURLOPT_USERAGENT      => "Googlebot",// who am i 
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
	
	parse_printersiam($fetch,true);
?>