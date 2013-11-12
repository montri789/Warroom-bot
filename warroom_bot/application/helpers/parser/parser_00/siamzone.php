<?PHP
	header ('Content-type: text/html; charset=utf-8');
	require("simple_html_dom_helper.php");
	require("../date_decoder_th_helper.php");
	
	function parse_siamzone($fetch,$debug = false)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		
		if($parsed_posts_count == 0)
		{
			$main_content = $html->find('tr td[bgcolor=#EFEFEF]');
			$post_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
			$str = explode(":",$post_title0);
			$post_title = $str[1];

			$board_msg = $html->find('div[style=width: 600px;] p');
			$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
			
			$author = $html->find('table tr td div[style=margin-left:5px; width:148px; overflow:hidden;]');
			$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
			
			$date_time = $html->find('tr td[class=thais]',0);
			$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

			$page_info = $html->find('div[class=maincontent] p span strong');
			$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
		 
			$str = explode(" ",$post_date);
			$yy = thYear_decoder($str[3]);
			$mm = thMonth_decoder($str[2],"cut");
			$dd = $str[1];
			$tt = $str[5];
			$page_view =$str[8];
			
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
				echo "<br/>";
				echo "PageView:".$page_view;
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
		
		$comments = $html->find('table[bgcolor=#DFDFDF]');

		$i=0;
	
		foreach($comments as $c)
		{ 	
			if($i > -1){
	
				$c_title = $c->find('table.thaim div',1);
				$comment_title0 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
	
				$str = explode(" ",$comment_title0);
				$comment_title = $str[1];
				$comment_no = $str[2];
				
				$c_body = $c->find('table.thaim div',3);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
				
				$c_author = $c->find('table.thaim div',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $c->find('tr div.thais',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
				$str = explode(" ",$comment_date);
				$yy = thYear_decoder($str[7]);
				$mm = thMonth_decoder($str[6],"cut");
				$dd = $str[5];
				$tt = $str[9];
				$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				
				if($debug)
				{	
					echo "CommentTitle:".$comment_title." ".$comment_no;
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
	
	
	$url = "http://www.siamzone.com/board/view.php?sid=2905334";
	
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

	
	parse_siamzone($fetch,true);
?>