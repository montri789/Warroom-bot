<?PHP
	@header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("date_decoder_th_helper.php");

	function parse_siamsport($fetch,$debug = false){
		$html = str_get_html($fetch);
		
		
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0){
			
			$main_content = $html->find('span[class=msgtitle]');
			$post_title = trim($main_content[0]->plaintext);

			$board_msg = $html->find('div[class=msgtext]',0);
			$post_body = trim($board_msg->plaintext);			
					
			$author = $html->find('span[class=view-username]',0);
			$post_author = trim($author->plaintext);
						
			$date_time = $html->find('span[class=msgdate]',0);
			$post_date = trim($date_time->plaintext);
			
			$arr = array("day -"=>"วัน","hours -"=>"ชั่วโมง",
				      "seconds -"=>"วินาที","week -"=>"สัปดาห์",
				      "month -"=>"เดือน","year -"=>"ปี");
			
			$post_date = trim(str_replace(array("ก่อน",","),"",$post_date));
			foreach($arr as $key => $val){
				$post_date = str_replace($val,$key,$post_date);
			}
			
			$post_date = str_replace("  ","",$post_date);
			$post_date = substr($post_date,0,-1);
			$post_date = date("Y-m-d H:i:s",strtotime("- ".$post_date));
			
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
			
		$comments = $html->find('td[class=fb-msgview-right]');

		$i = 0;
		
		foreach($comments as $c){
			
			if($i > 0){
				
			$c_title = $c->find('span[class=msgtitle]',0);
			$comment_title = trim($c_title->plaintext);
					
			$c_body = $c->find('.msgtext',0);
			$comment_body = trim($c_body->plaintext);
			
			$c_author = $c->find('span[class=view-username]',0);
			$comment_author = trim($c_author->plaintext);
				
			$c_date = $c->find('span[class=msgdate]',0);
			$comment_date = trim($c_date->plaintext);

			$comment_date = trim(str_replace(array("ก่อน",","),"",$comment_date));
			foreach($arr as $key => $val){
				$comment_date = str_replace($val,$key,$comment_date);
			}
			
			$comment_date = str_replace("  ","",$comment_date);
			$comment_date = substr($comment_date,0,-1);
			$comment_date = date("Y-m-d H:i:s",strtotime("- ".$comment_date));
			
						
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
		
			$i++;
		}
	
		$html->clear();
		unset($html);
	}
	
	$url = "http://motherandchild.in.th/component/option,com_kunena/Itemid,59/func,view/catid,14/id,3326/";
	
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
	
	parse_siamsport($fetch,ture);
?>