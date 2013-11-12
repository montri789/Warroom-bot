<?php
    // Turn all error into PHP exception.
    // http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warningTaken from
    function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    set_error_handler('handleError');

	function parse_varietypc_forum($fetch,$page,$debug=false)
	{
		$log_unit_name = 'parse_varietypc_forum';

		
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

		$dead_page = $html->find('tr[class="catbg"]',0);
		if($dead_page != null) 
		{
			if($debug)
			{
				echo "Page is dead.";
				echo "<br/>";
			}
			else
			{
				// Page is dead
				$page->outdate = 1;
				$page->update();
			}
		}
		else
		{
			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
			{
				$main_content = $html->find('td[class=windowbg] div[id^=subject_] h2 a');
				//$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('td[class=windowbg] div[class=post]');
				//$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('td[class=windowbg] table tbody tr td b a');
				//$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('td[class=windowbg] div[class=smalltext]');
				//$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
				$post_date = trim($date_time[1]->plaintext);
				
				$str = explode(" ",$post_date);

				if(trim($str[3]) == "เมื่อวานนี้" || trim($str[3]) == "วันนี้"){
					$post_date = dateThText($str[3])." ".$str[5];
				}else{
					$yy = substr($str[5],0,-1);
					$mm = thMonth_decoder($str[4],'full');
					$dd = $str[3];
					$tt = substr($str[6],0,8);
					$post_date = $yy."-".$mm."-".$dd." ".$tt;
				}
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('td[class^=windowbg]');
			//echo "CommentCount:".count($comments);
			log_message('info', $log_unit_name.' : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;
			foreach($comments as $k=>$c)
			{
				if($k<3) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('div[id^=subject_] h2',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div[class=post]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('table tbody tr td b a',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('div[class=smalltext]',1);
				$comment_date = trim($c_date_time->plaintext);

				$str = explode(" ",$comment_date);
				if(trim($str[4]) == "เมื่อวานนี้" || trim($str[4]) == "วันนี้"){
					$comment_date = dateThText($str[4])." ".$str[6];
				}else{
					$yy = substr($str[6],0,-1);
					$mm = thMonth_decoder($str[5],'full');
					$dd = $str[4];
					$tt = substr($str[7],0,8);

					$comment_date = $yy."-".$mm."-".$dd." ".$tt;
				}

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
					//$post->insert();
		
                                        // add obj to memcache
                                        $key = rand(1000,9999).'-'.microtime(true);
                                        $memcache->add($key, $post, false, 12*60*60) or die ("Failed to save OBJECT at the server");
                                        echo '.';
                                        unset($post);
				}
				$i++;
			}
		}
		
		$memcache->close();
		
		$html->clear();
		unset($html);
	}
?>