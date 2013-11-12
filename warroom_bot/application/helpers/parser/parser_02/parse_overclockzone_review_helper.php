<?php
	function parse_overclockzone_review($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_overclockzone_review';

		$html = str_get_html($fetch);
		
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		if($debug)
		{
			$parsed_posts_count = 0;
		}
		else
		{
			$current_posts = $page->get_posts();
			if($current_posts) $parsed_posts_count = count($current_posts);
			else $parsed_posts_count = 0;
		}
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('');
		if($dead_page != null) 
		{
			// Page is dead
			$page->outdate = 1;
			$page->update();
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				$main_content = $html->find('#AutoNumber24 table[width=95%]',1);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('#AutoNumber24',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));
				
				$pb = explode("$post_title",$post_body);
				$post_body = $pb[1];

				$author = $html->find('td.style21 .style204',2);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				if($post_author == null){
					$author = $html->find('td.style21 .style204',0);
					$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				}

				$at	= $html->find('#AutoNumber24 .style21',1);
				$at = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$at->plaintext));
				if($at == null){
					$at	= $html->find('#AutoNumber24 .style21',2);
					$at = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$at->plaintext));
				}
				$at = str_replace("Date: ","",$at);

				$at = explode(" Author: ",trim($at));
				$post_date = explode("-",trim($at[0]));
				if(!is_numeric($post_date[1]))
				$post_date = $post_date[2]."-".enMonth_decoder($post_date[1],"cut")."-".$post_date[0];
				else
				$post_date = $post_date[2]."-".$post_date[1]."-".$post_date[0];

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
					//$post->insert();
		
                                        // add obj to memcache
                                        $key = rand(1000,9999).'-'.microtime(true);
                                        $memcache->add($key, $post, false, 12*60*60) or die ("Failed to save OBJECT at the server");
                                        echo '.';
                                        unset($post);
				}
			}
			else { echo "(sub)"; }

			// No Comment on this page
			
			// // Comments at 
			// $comments = $html->find('div[id^=post_id_]');
			// //echo "CommentCount:".count($comments);
			// log_message('info', $log_unit_name.' : found elements : '.count($comments));
			// echo '(c='.count($comments).')';
			// //echo "<hr>";
			// 
			// $i=0;
			// 
			// foreach($comments as $k=>$c)
			// {
			// 	if($k==0) continue; // skip post entry
			// 	if($i < $parsed_posts_count-1)
			// 	{
			// 		$i++;
			// 		continue;
			// 	}
			// 
			// 	$c_title = $c->find('div.post_wrap span.post_id a',0);
			// 	$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
			// 	
			// 	$c_body = $c->find('div.post_body div',0);
			// 	$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
			// 	if(empty($comment_body)){
			// 		$c_body = $c->find('div.post_body',0);
			// 		$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
			// 	}
			// 	
			// 	$c_author = $c->find('div.post_wrap h3',0);
			// 	$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
			// 	$comment_author = explode(" : ",$comment_author);
			// 	if(count($comment_author) > 1){
			// 		$comment_author = $comment_author[1];
			// 	}else{
			// 		$comment_author = explode(" &nbsp;",$comment_author[0]);
			// 		$comment_author = str_replace("&nbsp;","",trim($comment_author[1]));
			// 	}
			// 	
			// 	
			// 	$c_date_time = $c->find('div.post_body p .published',0);
			// 	$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
			// 	
			// 	$comment_date = trim(str_replace(array("Posted","- ",",","&nbsp;"),"",$comment_date));
			// 	$date = explode(" ",$comment_date);
			// 	
			// 	if($date[0] == "Today" || $date[0] == "Yesterday"){
			// 		$comment_date = dateEnText($date[0])." ".$date[1];
			// 	}else{
			// 		$mm = enMonth_decoder($date[1]);
			// 		$comment_date = $date[2]."-".$mm."-".$date[0]." ".$date[3];
			// 	}
			// 	
			// 	if($debug)
			// 	{
			// 		echo "CommentTitle:".$comment_title;
			// 		echo "<br>";
			// 		echo "CommentBody:".$comment_body;
			// 		echo "<br>";
			// 		echo "CommentAuthor:".$comment_author;
			// 		echo "<br>";
			// 		echo "CommentDate:".$comment_date;
			// 		echo "<hr>";
			// 	}
			// 	else
			// 	{
			// 		$post = new Post_model();
			// 		$post->init();
			// 		$post->page_id = $page->id;
			// 		$post->type = "comment";
			// 		$post->title = $comment_title;
			// 		$post->body = trim($comment_body);
			// 		$post->post_date = $comment_date;
			// 		$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			// 		$post->author_id = $post->get_author_id(trim($comment_author));
			// 		$post->insert();
			// 		unset($post);
			// 	}
			// 	$i++;
			// }
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>