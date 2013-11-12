<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_homedd_board($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('table[width=95%] table[width=100%] tr td',0);
			$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

			$board_msg = $html->find('table[width=95%] table[width=100%] tr td',1);
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

			$author = $html->find('table[width=95%] table[width=100%] table[width=100%] font',0);
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
			
			$post_author = trim(str_replace(array("โดยคุณ ","/"," เวลา ")," ",$post_author));
			$post_author = str_replace("  "," ",$post_author);
			$str = explode("เมื่อวันที่",$post_author);
			
			$post_author = $str[0];
			
			$date = explode(" ",trim($str[1]));			
			$post_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
					
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
	
		$comments = $html->find('form[name=frmWebboardTitle] table[bgcolor=#B9CED5]');

		
		foreach($comments as $c)
		{ 			
			$p = $c->prev_sibling();
				
			$c_title = $p->find('td',0);
			$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

			$c_body = $c->find('font',0);
			$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

			$c_author = $c->find('tr',1);
			$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
			
			$comment_author = trim(str_replace(array("โดยคุณ ","/"," เวลา ")," ",$comment_author));
			$comment_author = str_replace("  "," ",$comment_author);
			$str = explode("เมื่อวันที่",$comment_author);
			
			$comment_author = $str[0];
			
			$date = explode(" ",trim($str[1]));			
			$comment_date = thYear_decoder($date[2])."-".$date[1]."-".$date[0]." ".$date[3];
									
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
		
		$html->clear();
		unset($html);
	}
	
	$url = "http://homedd.com/HomeddWeb/servlet/homedd.A_webboard.frontweb.FwWebboardMainServ?hidTitle_id=15342&hidMode=detail";
	
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
	
	parse_homedd_board($fetch,true);
?>