<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_prakard_board($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('.header1Title',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('.message',0);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('.postheader b',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				
			$date_time = $html->find('.postheader .postheader',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			$post_date = str_replace(array("Posted:",","),"",$post_date);
			$post_date = trim(str_replace("  "," ",$post_date));
                        $date = explode(" ",$post_date);
			
			$post_date = $date[3]."-".enMonth_decoder($date[1],"full")."-".$date[2]." ".$date[4];
					
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
	
		$comments = $html->find('table.content tr.postheader');

		$i = 0;
		foreach($comments as $c)
		{ 	
		    if($i > 0){
			$s = $c->next_sibling();
				
			$comment_title = " RE: ".$post_title;

			$c_body = $s->find('.message',0);
			$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

			$c_author = $c->find('td b',0);
			$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
			$c_date_time = $c->find('.postheader',0);
			$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
			$comment_date = str_replace(array("Posted:",","),"",$comment_date);
                        $comment_date = trim(str_replace("  "," ",$comment_date));
                        $date = explode(" ",$comment_date);
				
			$comment_date = $date[3]."-".enMonth_decoder($date[1],"full")."-".$date[2]." ".$date[4];
				
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
	
	$url = "http://prakard.com/default.aspx?g=posts&t=386874";
	
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
	
	parse_prakard_board($fetch,true);
?>