<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_hilight_kapook($fetch,$debug = false){
		$html = str_get_html($fetch);
				
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('.tab_score');
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

			$board_msg = $html->find('td[width=86%]',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));			
			
			$author = $html->find('table[width=670] table[width=100%] td',2);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
			$post_author = str_replace(array("::","&nbsp;"),"",$post_author);
			
			$str = explode(":",trim($post_author));
			
			$post_author = trim($str[1]);
			
			
			
			$date = explode(" ",trim($str[2]));
			$post_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1],"cus")."-".$date[0]; 		
		
			if($debug){							
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_author;
				echo "<br/>";
				echo "PostDate:".$post_date;
				echo "<br/>";
				echo "<hr/>";
			}else{
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
			
		$comments = $html->find('.table_00');

		$i = 0;
		
		foreach($comments as $c){ 	
				
			$c_title = $c->find('.table_01',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));						
			$comment_title = trim(str_replace("&nbsp;"," ",$comment_title));
			
			if(!empty($comment_title)){		
				$c_body = $c->find('td[width=86%]',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				$comment_body = trim(str_replace("&nbsp;"," ",$comment_body));
					
				$c_author = $c->find('table[width=100%] td',2);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				$comment_author = str_replace(array("::","&nbsp;"),"",$comment_author);
				$str = explode(":",trim($comment_author));
				
				$comment_author = trim($str[1]);
				
				$date = explode(" ",trim($str[2]));
				$comment_date = thYear_decoder($date[2])."-".thMonth_decoder($date[1],"cus")."-".$date[0];
								
				if($debug){			
					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date;
					echo "<br>";
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
		}
	
		$html->clear();
		unset($html);
	}
	
	$url = "http://football.kapook.com/webboard_inside.php?qid=49394";
	
	$options = array( 
	        CURLOPT_RETURNTRANSFER => true,         // return web page 
	        CURLOPT_HEADER         => false,        // don't return headers 
	        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
	        CURLOPT_ENCODING       => "",           // handle all encodings 
	        CURLOPT_USERAGENT      => "Google bot",// who am i 
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
	
	parse_hilight_kapook($fetch,ture);
?>