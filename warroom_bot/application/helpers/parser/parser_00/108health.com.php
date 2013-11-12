<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_108health($fetch,$debug = false){
		
		$html = str_get_html($fetch);
		
		//echo $html;
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){

			$main_content = $html->find('td b',0);
			$post_title = trim($main_content->plaintext);

			$ptitle = explode("(",$post_title);
			$pview = explode(")",$ptitle[1]);

			$board_msg = $html->find('.div_innerall td',0);
			$post_body = explode('<br />',$board_msg);
			$post_body = trim($post_body[1]);

			$author = $html->find('.font-02',0);
			$post_author = trim($author->plaintext);
			$post_author_ = explode(' ',$post_author);
			$post_author = str_replace('|','',$post_author_[2]);

			$post_date = $post_author_[5];
	
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
				$post->body = $post_date;
				$post->post_date = $post_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($post_author));
				$post->insert();
				unset($post);
		
			}
		}
	

		$comments = $html->find('div[style=width:644px; margin:auto; margin-bottom:5px; border:#CCC solid 0px;]');

		$i = 0;
	
			foreach($comments as $c){ 
			
				if($i > 0){
					
				//	$next_date = $c->next_sibling()->next_sibling();
				//	$next_body = $c->parent()->next_sibling()->next_sibling();
					
			
					$comment_title = "";
				
				
					$c_body = $c->find('.bradius');
					$comment_body = trim($c_body[0]->plaintext);
					$comment_body1 = explode("โดย : ",$comment_body);
					$comment_body2 = str_replace("  ","",$comment_body1[0]);
					$comment_body3 = explode(" ",trim($comment_body2));
                    unset($comment_body3[0]);		
					$comment_body = implode(" ",$comment_body3);
					$comment_body = substr($comment_body,2,strlen($comment_body));	
					
					$c_author = $c->find('.bradius',0);
					$comment_author = trim($c_author->plaintext);
					$comment_author = $comment_body1[1];
					$comment_author = explode("|",$comment_author);
					$comment_author = $comment_author[0];
					
					$c_date_time = $c->find('.bradius',0);
					$comment_date = trim($c_date_time->plaintext);
					$comment_date = explode("|",$comment_date);
					$cdate = str_replace("วันที่ : ","",$comment_date[1]);
					$ctime = str_replace("เวลา : ","",$comment_date[2]);
					$comment_date = $cdate.' '.$ctime;
					

					if($debug)
					{	
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
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
						$post->author_id = $post->get_author_id(trim($author));
						$post->insert();
						unset($post);
					}
				}
				
				$i++;
			}
		$html->clear();
		unset($html);
	}
	

	$url = "http://www.108health.com/108health/webboard_views.php?webb_id=790&ref_btype_id=";
	
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
	        CURLOPT_POSTFIELDS     => $curl_data,   // this are my post vars 
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
	
	parse_108health($fetch,true);
?>