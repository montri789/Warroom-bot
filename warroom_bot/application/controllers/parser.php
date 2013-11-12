<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// According to http://stackoverflow.com/questions/2247977/what-does-zend-mm-heap-corrupted-mean
ini_set('output_buffering', 8192);

// Turn all error into PHP exception.
// Taken from http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
function handle_error($errno, $errstr, $errfile, $errline, array $errcontext)
{
	// error was suppressed with the @-operator
	if (0 === error_reporting())
	{
	    return false;
	}

	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	return false;
}

class Parser extends CI_Controller {
    
	function __construct()
	{
		parent::__construct();
		log_message('info'," PARSER : Turn all errors into PHP exception");
		set_error_handler('handle_error');
		// set_error_handler(array(&$this, 'handle_error'));
	}
	
	function __destruct()
	{
		log_message('info', "Restore error handler to basic CI's one");
		restore_error_handler();
	}
	
	function parse_zoneit_forum($fetch,$page,$debug=false)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=forumposts] div[class=keyinfo] h5',0);
				$post_title = trim($main_content->plaintext);
				$ptitle = explode("(",$post_title);
				$post_title = $ptitle[0];

				$board_msg = $html->find('div[id=forumposts] div[class=post]',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div[id=forumposts] div[class=poster] a',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('div[id=forumposts] div[class=postarea] div[class=keyinfo] div[class=smalltext]',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
				$ydate = explode(",",$date[5]);

				$page_info = $html->find('div[id=forumposts] h3',0);
				$page_view = trim($page_info->plaintext);

				$aview = explode("(",$page_view);
				$pview = explode(")",$aview[1]);
	  			$page_view = $pview[0];

				$mm = thMonth_decoder($date[4],'full');
				$post_date = $ydate[0]."-".$mm."-".$date[3]." ".$date[6];
				
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
			$comments = $html->find('div[class^=window]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_zoneit_forum : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post enf
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('h5[id^=subject_] a',0);
				$comment_title = trim($c_title->plaintext);
				
				$c_body = $c->find('div[class=post]');
				$comment_body = trim($c_body[0]->plaintext);
				
				$c_author = $c->find('div[class=poster] h4',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('div[class=keyinfo] div[class=smalltext]',0);
				$comment_date = trim($c_date_time->plaintext);
	
				$cdate = explode(" ",$comment_date);
				$a = explode (",",$cdate[6]);
	
				$mm = thMonth_decoder($cdate[5],'full');
				$comment_date = $a[0]."-".$mm."-".$cdate[4]." ".$cdate[7];
				
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
					$post->author_id = $post->get_author_id(trim($comment_author));
					$post->insert();
					unset($post);
				}
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}

	function parse_manager_news($fetch,$page,$debug=false)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('table[width=100%] td.headline');
				$post_title = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('table[width=100%] td.body');
				$post_body = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$board_msg[3]->plaintext));

				$author = $html->find('table[width=100%] td.body');
				$post_author = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$author[2]->plaintext));

				$pauthor = explode(" ",$post_author);

				$date_time = $html->find('table[width=100%] td.date',0);
				$post_date = trim(iconv("CP874","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$pdate = explode(" ",$post_date);

				// $page_view = explode(":",$page_view);
				// $view = explode (" ",$page_view[1]);

				$yy = thYear_decoder($pdate[2]);
				$mm = thMonth_decoder($pdate[1],'full');

				$post_date = $yy."-".$mm."-".$pdate[0]." ".$pdate[3];

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
			$comments = $html->find('table[cellspacing=7]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_manager_news : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k<1) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('td.body',0);
				$comment_title = trim(iconv("CP874","utf-8",$c_title->plaintext));
		
				$c_body = $c->find('td.body',3);
				$comment_body = trim(iconv("CP874","utf-8",$c_body->plaintext));
		
				$c_author = $c->find('td.body font',0);
				$comment_author = trim(iconv("CP874","utf-8",$c_author->plaintext));

				$comment_date = $post_date;
				
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
					$post->author_id = $post->get_author_id(trim($comment_author));
					$post->insert();
					unset($post);
				}
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}

	function parse_vmodtech_forum($fetch,$page,$debug=false)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('tr[class=catbg3] td[id=top_subject]',0);
				$post_title = trim($main_content->plaintext);

				$ptitle = explode("(",$post_title);
				$pview = explode(")",$ptitle[1]);

				$board_msg = $html->find('td[class=windowbg] div[class=post]',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('td[class=windowbg] a',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('td[class=windowbg] div[class=smalltext]',1);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);

				if(trim($date[4]) == "เมื่อวานนี้" || trim($date[4]) == "วันนี้"){
					$post_date = dateThText($date[4])." ".$date[6];
				}else{	
					$ydate = explode(",",$date[5]);

					$mm = thMonth_decoder($date[4],'full');
					$post_date = $ydate[0]."-".$mm."-".$date[3]." ".$date[6];
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
			$comments = $html->find('td[class^=window]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_vmodtech_forum : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('div[id^=subject_] a',0);
				$comment_title = trim($c_title->plaintext);
			
				$c_body = $c->find('div[class=post]');
				$comment_body = trim($c_body[0]->plaintext);
				
				$c_author = $c->find('a',0);
				$comment_author = trim($c_author->plaintext);
				
				$c_date_time = $c->find('div[class=smalltext]',1);
				$comment_date = trim($c_date_time->plaintext);
	
				$cdate = explode(" ",$comment_date);

				if(trim($cdate[4]) == "เมื่อวานนี้" || trim($cdate[4]) == "วันนี้"){
					$comment_date = dateThText($cdate[4])." ".$cdate[6];
				}else{
				
					$a = explode (",",$cdate[6]);
					$mm = thMonth_decoder($cdate[5],'full');
					$comment_date = $a[0]."-".$mm."-".$cdate[4]." ".$cdate[7];
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
				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	function parse_mxphone_article($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=main] h1.page-title a',0);
				$post_title = trim($main_content->plaintext);

				$board_msg = $html->find('div[id=main] div.article-content p',0);
				$post_body = trim($board_msg->plaintext);

				$author = $html->find('div[id=main] div.post-meta strong',0);
				$post_author = trim($author->plaintext);

				$date_time = $html->find('div[id=main] div.post-meta',0);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
				$dd = explode("/",$date[3]);

				$page_info = $html->find('div[id=main] div.metadata div[class=stats total-read]',0);
				$page_view = trim($page_info->plaintext);			

				$yy = thYear_decoder($date[2]);
				$post_date = $dd[2]."-".$dd[1]."-".$dd[0]." ".$date[4];
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('article.comment');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_mxphone_article : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$comment_title = "#".$i++;

				$c_body = $c->find('div.comment-content');
				$comment_body = trim($c_body[0]->plaintext);

				$c_author = $c->find('div[class=comment-author vcard] span',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('div[class=comment-author vcard] time',0);
				$comment_date = trim($c_date_time->plaintext);

				$cdate = explode(" ",$comment_date);
				$cd = explode("/",$cdate[0]);
				$comment_date = $cd[2]."-".$cd[1]."-".$cd[0]." ".$cdate[2];
				
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

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parse_bloggang_blog($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('table[width=99%] td[width=50%] td[valign=top] b',0);
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content->plaintext));

				$board_msg = $html->find('table[width=99%] td[width=50%] td[valign=top]',0);
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg->plaintext));

				$author = $html->find('table[width=99%] td[width=25%] table[width=100%] b',2);
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));

				$cut = explode("Create Date",$post_body);
				$pdate = explode("Last Update",$cut[1]);
				$date = explode(" ",$pdate[1]);

				//View Count
				//$page_info = $html->find('div[id=main] div.metadata div[class=stats total-read]',0);
				//$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info->plaintext));			

				//$date = explode(" ",$post_date);
				$yy = thYear_decoder($date[4]);
				$mm = thMonth_decoder($date[3],'full');
				//$dd = $date[2];
				//$tt = $date[6];
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[width=100%] [border=2]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_bloggang_blog : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('td[id^=]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
				$c_body = $c->find('div.comment');
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body[0]->plaintext));
				
				if(!empty($comment_body)){
					
					$c_author = $c->find('td[id^=] td[width=100%]',1);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
		
					$comment_author = explode(" ",trim($comment_author));
					$comment_author = $comment_author[1];
					
					$c_date_time = $c->find('td[id^=] td[width=100%]',1);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
						
					$comment_date = explode("วันที่:",$comment_date);
					$dt = explode(" ",trim(str_replace(array("เวลา:","น."),"",$comment_date[1])));
					$comment_date = thYear_decoder($dt[2])."-".thMonth_decoder($dt[1],'full')."-".$dt[0]." ".$dt[3];
				
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
				$i++;
			}
		}
		$html->clear();
		unset($html);		
	}

	function parse_thainotebook_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=bodyarea] div.nav a');
				$post_title = trim($main_content[3]->plaintext);

				$board_msg = $html->find('div[id=bodyarea] div.post');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('div[id=bodyarea] td.windowbg b');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('div[id=bodyarea] td.windowbg div.smalltext',1);
				$post_date = trim($date_time->plaintext);

				$pdate = explode(" ",$post_date);

				if(trim($pdate[3]) == "เมื่อวานนี้" || trim($pdate[3]) == "วันนี้"){
					$post_date = dateThText($pdate[3])." ".$pdate[5];	
				}else{	
					$year = explode(",",$pdate[5]);
					$day = explode(",",$pdate[4]);

					$mm = thMonth_decoder($pdate[3],'full');
					$post_date = $year[0]."-".$mm."-".$day[0]." ".$pdate[6];
				}

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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[cellpadding=3] td[class^=windowbg]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_thainotebook_forum : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('table[width=100%] div.smalltext b',0);
				$comment_title = trim($c_title->plaintext);

				$ctitle = explode(" ",$comment_title);
				$comment_title = $ctitle[1];
		
				$c_body = $c->find('div.post',0);
				$comment_body = trim($c_body->plaintext);
		
				$c_author = $c->find('b',0);
				$comment_author = trim($c_author->plaintext);
		
				$c_date_time = $c->find('div.smalltext',1);
				$comment_date = trim($c_date_time->plaintext);

				$cdate = explode(" ",$comment_date);
				
				if(trim($cdate[4]) == "เมื่อวานนี้" || trim($cdate[4]) == "วันนี้"){
					$comment_date = dateThText($cdate[4])." ".$cdate[6];	
				}else{	
					$year = explode(",",$cdate[6]);
					$day = explode(",",$cdate[5]);
					$cmm = thMonth_decoder($cdate[4],'full');
					$comment_date = $year[0]."-".$cmm."-".$day[0]." ".$cdate[7];
				}
				
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

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parse_thaimobilecenter_article($fetch,$page)
	{
		// This section does not have POST AUTHOR so make it default
		$default_author = "thaimobilecenter_writer";
		
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('td[class=16] font',0);
				$post_title = trim(iconv("CP874","utf-8",$main_content->plaintext));

				$board_msg = $html->find('td[class=16] span[class=text12]',0);
				$post_body = trim(iconv("CP874","utf-8",$board_msg->plaintext));

				$author = $html->find('td[class=16] span[class=text12] font[color=#000000]',0);
				$post_author = trim(iconv("CP874","utf-8",$author->plaintext));
				if($post_author == null) $post_author = $default_author;

				$date_time = $html->find('table[width=778] table[width=96%] table[cellpadding=1] td[class=text10]',1);
				$post_date = trim(iconv("CP874","utf-8",$date_time->plaintext));

				$date = explode("/",$post_date);
				$dd = explode(":",$date[0]);
				$yy = thYear_decoder($date[2]);

				$post_date = $yy."-".$date[1]."-".trim($dd[1]);
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('table[width=96%] table[width=95%]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_thaimobilecenter_article : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				// if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('td[width=83%] td[class=text10] font',1);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

				if(!empty($comment_title)){

					$c_body = $c->find('td[width=83%] td[class=text12]');
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body[0]->plaintext));	

					$c_author = $c->find('td[width=73%] font',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

					$c_date_time = $c->find('td[width=83%] td[class=text10] font',2);
					$comment_date = trim(iconv("tis-620","utf-8",$c_date_time->plaintext));

					$cdate = explode("/",$comment_date);
					$cd = explode("&nbsp;",$cdate[0]);
					$cy = explode(" ",$cdate[2]);
					$yy = thYear_decoder($cy[0]);
					$dd = onlyNum($cd[1]);
					$comment_date = $yy."-".$cdate[1]."-".trim($dd)." ".$cy[2];

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

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parse_mthai_movie_blog($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=content] h2');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('div[id=content] p span');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('div[id=content] span');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('div[id=content] abbr',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$date = explode(" ",$post_date);
				$day = explode(",",$date[1]);
				$mm = enMonth_decoder($date[0],'full');
				$post_date = $date[2]."-".$mm."-".$day[0]." ".$date[4];
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('li[id^=comment-]');
			//echo "CommentCount:".count($comments);
			log_message('info',' notebook4game_forum : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				// if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('div.author span',1);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div.inner p',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('div.author span',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('div.commenter-detail',0);
				$comment_date = trim($c_date_time->plaintext);

				$cdate_time = explode("IP",$comment_date);
				$cdate = explode(" ",$cdate_time[0]);
				$ctime = explode(",",$cdate[2]);
				$mm = enMonth_decoder($cdate[1],'full');
				$comment_date = $cdate[3]."-".$mm."-".$ctime[0]." ".$cdate[5];

				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "comment";
				$post->title = $comment_title;
				$post->body = trim($comment_body);
				$post->post_date = $comment_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($comment_author));
				echo "id=".$post->insert();
				unset($post);

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parse_notebook4game_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=contentMain] div[id=postlist] h2');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				$board_msg = $html->find('div.content');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				$author = $html->find('div.userinfo a');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('div.posthead span.date',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				$date = explode("&nbsp;",$post_date);
				if(trim($date[0]) == "Yesterday" || trim($date[0]) == "Today")
				{
					$post_date = dateEnText($date[0])." ".$date[1];        
				}
				else
				{
					$day = explode("-",$date[0]);
					$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
					$post_date = $y."-".$day[1]."-".$day[0]." ".$date[1];
				}
				
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
			else { echo "(sub)"; }

			// Comments at 
			$comments = $html->find('li[id^=post_]');
			//echo "CommentCount:".count($comments);
			log_message('info',' notebook4game_forum : found elements : '.count($comments));
			echo '(c='.count($comments).')';
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				// if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('div.posthead span.nodecontrols a',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
				
				$c_body = $c->find('div.content',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
	
				$c_author = $c->find('div.userinfo a',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));
				
				$c_date_time = $c->find('div.posthead span.date',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));	
				$date = explode("&nbsp;",$comment_date);
				
				if(trim($date[0]) == "Yesterday" || trim($date[0]) == "Today")
				{
					$comment_date = dateEnText($date[0])." ".$date[1];
				}
				else
				{
					$day = explode("-",$date[0]);
					$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
					$comment_date = $y."-".$day[1]."-".$day[0]." ".$date[1];
				}

				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "comment";
				$post->title = $comment_title;
				$post->body = trim($comment_body);
				$post->post_date = $comment_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($comment_author));
				echo "id=".$post->insert();
				unset($post);

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parser_whatphone_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				$main_content = $html->find('div[id=page-body] h2');
				$post_title = trim($main_content[0]->plaintext);

				$board_msg = $html->find('div[class=content]');
				$post_body = trim($board_msg[0]->plaintext);

				$author = $html->find('p[class=author] strong');
				$post_author = trim($author[0]->plaintext);

				$date_time = $html->find('div[class=postbody] p[class=author]',0);
				$post_date = trim($date_time->plaintext);

				//--------------
				$post_date = str_replace(",","",$post_date);
				$post_date = explode("&raquo;",$post_date);
				$post_date = preg_split("/[\s]+/",trim($post_date[1]));

				if(preg_match("/^[a-zA-Z]/",$post_date[0])){
					if(trim($post_date[0]) == "Yesterday" || trim($post_date[0]) == "Today"){
						$post_date = dateThText($post_date[0])." ".$post_date[4];	
					}else{ 
						$dd = $post_date[2];	
						$mm = enMonth_decoder($post_date[1],"cut");
						$yy = $post_date[3];
						$tt = $post_date[4];
						$post_date = $yy."-".$mm."-".$dd." ".$tt;
					}
				}else{   
					if(trim($post_date[0]) == "เมื่อวานนี้" || trim($post_date[0]) == "วันนี้"){
						$post_date = dateThText($post_date[0])." ".$post_date[4];	
					}else{  
						$dd = $post_date[2];	
						$mm = thMonth_decoder($post_date[1],"cut");
						$yy = $post_date[3];
						$tt = $post_date[4];
						$post_date = $yy."-".$mm."-".$dd." ".$tt;	
					}
				}
				//---------------------
				
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

			// Comments at 
			$comments = $html->find('hr[class=divider] div[class=inner]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_whatphone_forum : found elements : '.count($comments));
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				// if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('div[class=postbody] h3',0);
				$comment_title = trim($c_title->plaintext);

				$c_body = $c->find('div[class=content]',0);
				$comment_body = trim($c_body->plaintext);

				$c_author = $c->find('p[class=author] strong',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('div[class=postbody] p[class=author]',0);
				$comment_date = trim($c_date_time->plaintext);

				//----------------
				$comment_date = str_replace(",","",$comment_date);
				$comment_date = explode("&raquo;",$comment_date);
				$comment_date = preg_split("/[\s]+/",trim($comment_date[1]));

				if(preg_match("/^[a-zA-Z]/",$comment_date[0])){
					if(trim($comment_date[0]) == "Yesterday" || trim($comment_date[0]) == "Today"){
						$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
					}else{ 
						$dd = $comment_date[2];	
						$mm = enMonth_decoder($comment_date[1],"cut");
						$yy = $comment_date[3];
						$tt = $comment_date[4];
						$comment_date = $yy."-".$mm."-".$dd." ".$tt;
					}
				}else{   
					if(trim($comment_date[0]) == "เมื่อวานนี้" || trim($comment_date[0]) == "วันนี้"){
						$comment_date = dateThText($comment_date[0])." ".$comment_date[4];	
					}else{  
						$dd = $comment_date[2];	
						$mm = thMonth_decoder($comment_date[1],"cut");
						$yy = $comment_date[3];
						$tt = $comment_date[4];
						$comment_date = $yy."-".$mm."-".$dd." ".$tt;	
					}
				}
				//---------------------

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

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parser_siamphone_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('table[class=forumline] span[class=gen]',0);
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
				$post = $html->find('table[width=620]',0);
				
				$main_content = $post->find('span[class=postdetails] font[size=1] a',0);
				$post_title = trim($main_content->plaintext);
				
				$board_msg = $post->find('span[class=postbody]',0);
				$post_body = trim($board_msg->plaintext);

				$meta = $post->parent()->prev_sibling();
				$author = $meta->find('span[class=name] b',0);
				$post_author = trim($author->plaintext);

				$date_time = $post->find('font[size=1]',1);
				$post_date = trim($date_time->plaintext);

				$date = explode(" ",$post_date);
                $post_date = $date[4]."-".enMonth_decoder($date[3],'cut')."-".$date[2]." ".$date[5];
				
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

			// Comments at 
			$comments = $html->find('table[width=620]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_siamphone_forum : found elements : '.count($comments));
			//echo "<hr>";
			
			$i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue; // skip post entry
				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				$c_title = $c->find('span[class=postdetails] font[size=1] a',0);
				$comment_title = null;

				$c_body = $c->find('span[class=postbody]',0);
				$comment_body = trim($c_body->plaintext);

				$meta = $c->parent()->prev_sibling();
				$c_author = $meta->find('span[class=name] b',0);
				$comment_author = trim($c_author->plaintext);

				$c_date_time = $c->find('font[size=1]',1);
				$comment_date = trim($c_date_time->plaintext);

				$date = explode(" ",$comment_date);
                $comment_date = $date[4]."-".enMonth_decoder($date[3],'cut')."-".$date[2]." ".$date[5];

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

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}

	function parse_varietypc_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[id=main_contentRed]',0);
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
				// Post Title at div.clear-list-mar, 1st h1 element
				$main_content = $html->find('td[class=windowbg] div[id^=subject_] h2 a');
				$post_title = trim($main_content[0]->plaintext);

				// Post Body at div.content_only
				$board_msg = $html->find('td[class=windowbg] div[class=post]');
				$post_body = trim($board_msg[0]->plaintext);
				
				// Post Meta at [no have]
				$author = $html->find('td[class=windowbg] table tbody tr td b a');
				$post_author = trim($author[0]->plaintext);
				
				$date_time = $html->find('td[class=windowbg] div[class=smalltext]');
				$post_date = trim($date_time[1]->plaintext);
				
				log_message('info','post_date='.$post_date);

				$str = explode(" ",$post_date);
				
				echo "123"."<br>";
				
				if(trim($str[3]) == "เมื่อวานนี้" || trim($str[3]) == "วันนี้"){
					$post_date = dateThText($str[3])." ".$str[5];
				}else{
					$yy = substr($str[5],0,-1);
					$mm = thMonth_decoder($str[4],'full');
					$dd = $str[3];
					$tt = substr($str[6],0,8);
					$post_date = $yy."-".$mm."-".$dd." ".$tt;
				}
				
				log_message('info','post_date='.$post_date);

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

//				$page->view = $page_view;
//				$page->update();

			}

			// Comments at 
			$comments = $html->find('td[class^=windowbg]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_varietypc_forum : found elements : '.count($comments));
			//echo "<hr>";

			// If sub comment page, skip Post entry
			if($page->sub_comment) $i=1;
			else $i=0;

			foreach($comments as $k=>$c)
			{
				if($k<3) continue;

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
				
				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "comment";
				$post->title = $comment_title;
				$post->body = $comment_body;
				$post->post_date = $comment_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($comment_author));
				$post->insert();
				unset($post);

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}
	
	function parse_monavista_forum($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		$dead_page = $html->find('div[class=standard_error]',0);
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
				// Post Title at div.maincontent, 1st h1 element
				$main_content = $html->find('title');
				$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

				// Post Body at div.boardmsg
				$board_msg = $html->find('div[class=content]');
				$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

				// Post Meta at ul#ownerdetail
				$author = $html->find('div[class=postdetails] a');
				$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

				$date_time = $html->find('li div span span',0);
				$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

				// View Count
				// $page_info = $html->find('div[class=maincontent] p span strong');
				// $page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

				$str = explode("&nbsp;",$post_date);
                if($str[0] == "Yesterday" || $str[0] == "Today"){
                        $post_date = dateEnText($str[0]);
                }else{
                        $day = explode(" ",$str[0]);

                        $mm = enMonth_decoder($day[1]);
                        $dd0 = $day[0];
                        $dd = preg_replace("/[^0-9]/", '',$dd0);
//                        $str1 = explode("&nbsp;",$yy0);
                        $yy = $day[2];
                        $post_date = $yy."-".$mm."-".$dd;
                }

                $post_date = $post_date." ".$str[1];

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

//				$page->view = $page_view;
//				$page->update();

			}

			// Comments at 
			$comments = $html->find('li[class=postbitlegacy]');
			//echo "CommentCount:".count($comments);
			log_message('info',' parse_monavista_forum : found elements : '.count($comments));
			//echo "<hr>";

			// If sub comment page, skip Post entry
			if($page->sub_comment) $i=1;
			else $i=0;

			foreach($comments as $k=>$c)
			{
				if($k==0) continue;

				if($i < $parsed_posts_count-1)
				{
					$i++;
					continue;
				}

				//Comment Title as div.listCommentHead
				$c_title = $c->find('span a[name^=post]',0);
				$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

				//Comment Body as div.commentBox div.boardmsg
				$c_body = $c->find('div[id^=post_message_] blockquote',0);
				$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

				//Comment Author as ui#ownerdetail li b
				$c_author = $c->find('div[class=postdetails] a',0);
				$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

				//Comment Date ul#ownerdetail li
				$c_date_time = $c->find('div span span',0);
				$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));

				$str = explode("&nbsp;",$comment_date);
                if($str[0] == "Yesterday" || $str[0] == "Today"){
                        $comment_date = dateEnText($str[0]);
                }else{
                        $day = explode(" ",$str[0]);
                        $mm = enMonth_decoder($day[1]);
                        $dd0 = $day[0];
                        $dd = preg_replace("/[^0-9]/", '',$dd0);
                        $yy = $day[2];

                        $comment_date = $yy."-".$mm."-".$dd;
                }

                $comment_date = $comment_date." ".$str[1];

				$post = new Post_model();
				$post->init();
				$post->page_id = $page->id;
				$post->type = "comment";
				$post->title = $comment_title;
				$post->body = $comment_body;
				$post->post_date = $comment_date;
				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
				$post->author_id = $post->get_author_id(trim($comment_author));
				$post->insert();
				unset($post);

				$i++;
			}
		}
		$html->clear();
		unset($html);
	}

	//function parse_adslthailand_forum($fetch,$page)
	//{ 
	// 		$html = str_get_html($fetch);
	// 		
	// 		$current_posts = $page->get_posts();
	// 		if($current_posts) $parsed_posts_count = count($current_posts);
	// 		else $parsed_posts_count = 0;
	// 		log_message('info',' parsed_posts_count : '.$parsed_posts_count);
	// 		
	// 		$dead_page = $html->find('div[id=main_contentRed]',0);
	// 		if($dead_page != null) 
	// 		{
	// 			// Page is dead
	// 			$page->outdate = 1;
	// 			$page->update();
	// 		}
	// 		else
	// 		{
	// 			if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
	// 			{
	// 				// Post Title at div.maincontent, 1st h1 element
	// 				$main_content = $html->find('title');
	// 				$post_title = trim($main_content[0]->plaintext);
	// 
	// 				// Post Body at div.boardmsg
	// 				$board_msg = $html->find('div[class=content]');
	// 				$post_body = trim($board_msg[0]->plaintext);
	// 
	// 				// Post Meta at ul#ownerdetail
	// 				$author = $html->find('div[class=popupmenu memberaction] a');
	// 				$post_author = trim($author[0]->plaintext);
	// 
	// 				$date_time = $html->find('span[class=date]',0);
	// 				$post_date = trim($date_time->plaintext);
	// 
	// 				// View Count
	// 				// $page_info = $html->find('div[class=maincontent] p span strong');
	// 				// $page_view = trim($page_info[1]->plaintext);
	// 
	// 				$str0 = explode("&nbsp;",$post_date);
	// 				$date = $str0[0];
	// 				$tt = substr($str0[1],0,5).':00';
	// 				$str = explode("-",$date);
	// 				$yy = $str[2];
	// //				$mm = $str[1];
	// 				$month = $str[1]; //or whatever 
	// 
	// 				for($i=1;$i<=12;$i++)
	// 				{ 
	// 					if(date("M", mktime(0, 0, 0, $i, 1, 0)) == $month)
	// 					{ 
	// 						if($i<10) $mm = '0'.$i;
	// 						else $mm = $i;
	// 						break; 
	// 					} 
	// 				}
	// 				$dd = $str[0];
	// 
	// 				$post = new Post_model();
	// 				$post->init();
	// 				$post->page_id = $page->id;
	// 				$post->type = "post";
	// 				$post->title = $post_title;
	// 				$post->body = $post_body;
	// 				$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
	// 				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
	// 				$post->author_id = $post->get_author_id(trim($post_author));
	// 				$post->insert();
	// 				unset($post);
	// 			
	// //				$page->view = $page_view;
	// //				$page->update();
	// 			
	// 			}
	// 		
	// 			// Comments at 
	// 			$comments = $html->find('li[id^=post_]');
	// 			//echo "CommentCount:".count($comments);
	// 			log_message('info',' parse_adslthailand : found elements : '.count($comments));
	// 			//echo "<hr>";
	// 		
	// 			// If sub comment page, skip Post entry
	// 			if($page->sub_comment) $i=1;
	// 			else $i=0;
	// 		
	// 			foreach($comments as $k=>$c)
	// 			{
	// 				if($k==0) continue;
	// 				
	// 				if($i < $parsed_posts_count-1)
	// 				{
	// 					$i++;
	// 					continue;
	// 				}
	// 			
	// 				//Comment Title 
	// 				$c_title = $c->find('span a[name^=post]',0);
	// 				$comment_title = trim($c_title->plaintext);
	// 
	// 				//Comment Body 
	// 				$c_body = $c->find('div[id^=post_message_] blockquote',0);
	// 				$comment_body = trim($c_body->plaintext);
	// 
	// 				//Comment Author 
	// 				$c_author = $c->find('div[class=popupmenu memberaction] a',0);
	// 				$comment_author = trim($c_author->plaintext);
	// 
	// 				//Comment Date 
	// 				$c_date_time = $c->find('div span span',0);
	// 				$comment_date = trim($c_date_time->plaintext);
	// 
	// 				$str0 = explode("&nbsp;",$comment_date);
	// 				$date = $str0[0];
	// 				$tt = substr($str0[1],0,5).':00';
	// 				$str = explode("-",$date);
	// 				$yy = $str[2];
	// //				$mm = $str[1];
	// 				$month = $str[1]; //or whatever 
	// 
	// 				for($i=1;$i<=12;$i++)
	// 				{ 
	// 					if(date("M", mktime(0, 0, 0, $i, 1, 0)) == $month)
	// 					{ 
	// 						if($i<10) $mm = '0'.$i;
	// 						else $mm = $i;
	// 						break; 
	// 					} 
	// 				}
	// 
	// 				$dd = $str[0];
	// 			
	// 				$post = new Post_model();
	// 				$post->init();
	// 				$post->page_id = $page->id;
	// 				$post->type = "comment";
	// 				$post->title = $comment_title;
	// 				$post->body = $comment_body;
	// 				$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
	// 				$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
	// 				$post->author_id = $post->get_author_id(trim($comment_author));
	// 				$post->insert();
	// 				unset($post);
	// 			
	// 				$i++;
	// 			}
	// 		}
	// 		$html->clear();
	// 		unset($html);
	// 	}
	// 	
	function parse_lcdtvth_review($fetch,$page)
	{
		$html = str_get_html($fetch);
		
		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);

		if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
		{
			// Page view td[colspan=3]
			$read_write = $html->find('td[colspan=3]',0);
			$read_write_str = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$read_write->plaintext)));
			//var_dump($read_write_str);
			for($i=0,$j=0;$i<count($read_write_str) && $j<2;$i++) // Post Author is at 3rd element
			{
				// Skip those white space, looking for something with chars
				if(strlen(trim($read_write_str[$i])) > 0) $j++;
			}
			$page_view = $read_write_str[$i-1];
			// echo "PostView:".$i.":".$post_view;
			// echo "<hr>";
			
			// Post td[height=25] span[class=font-2]
			$title = $html->find('table[width=97%] span[class=font-2]',0);
			$post_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$title->plaintext));
			$body = $html->find('table[width=550]',0);
			$meta = $body->find('div',0);
			$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$body->plaintext));
			$post_meta = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$meta->plaintext)));
		
			for($i=0,$j=0;$i<count($post_meta) && $j<3;$i++) // Post Author is at 3rd element
			{
				// Skip those white space, looking for something with chars
				if(strlen(trim($post_meta[$i])) > 0) $j++;
			}
			//var_dump($post_meta);
			
			$post = new Post_model();
			$post->init();
			$post->page_id = $page->id;
			$post->type = "post";
			$post->title = $post_title;
			$post->body = $post_body;
			$post->post_date = null;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			$post->author_id = $post->get_author_id(trim($post_meta[$i-1]));
			$post->insert();
			unset($post);
			
			$page->view = $page_view;
			$page->update();
		
			// echo "PostTitle:".$post_title;
			// echo "<br/>";
			// echo "PostBody:".$post_body;
			// echo "<br/>";
			// echo "PostAuthor:".$post_meta[$i-1];
			// echo "<br/>";
			// echo "PostDate:".null;
			// echo "<hr/>";
		}
		
		$comments = $html->find('div[class=style1]');
		//echo "CommentCount:".count($comments);
		log_message('info',' parse_lcdtvth_review : found elements : '.count($comments));
		//echo "<hr>";
		
		// If sub comment page, skip Post entry
		if($page->sub_comment) $i=1;
		else $i=0;
		
		foreach($comments as $c)
		{
			if($i < $parsed_posts_count-1)
			{
				$i++;
				continue;
			}
			
			$title = $c->find('table[height=27]',0);
			$comment_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$title->plaintext));
			$body = $c->find('table[class=text2] tbody tr td[align=left]',0)->first_child()->first_child();
			$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$body->plaintext));
			$meta = $c->find('table[class=text2] tbody tr td[align=left]',0)->first_child()->last_child();
			$author = $meta->find('strong',0);
			$comment_meta = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$meta->plaintext)));
			$date_index = count($comment_meta)-2;
			$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
			$date = explode("/",$comment_meta[$date_index]);
			$yy = $date[2];
			$mm = $date[1];
			$dd = $date[0];
			$tt = $comment_meta[$date_index+1];
			
			$post = new Post_model();
			$post->init();
			$post->page_id = $page->id;
			$post->type = "comment";
			$post->title = $comment_title;
			$post->body = $comment_body;
			$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			$post->author_id = $post->get_author_id(trim($comment_author));
			$post->insert();
			unset($post);
			
			// echo "CommentTitle:".$comment_title;
			// echo "<br/>";
			// echo "CommentBody:".$comment_body;
			// echo "<br/>";
			// echo "CommentAuthor:".$comment_author;
			// echo "<br/>";
			// echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
			// echo "<hr/>";
			
			$i++;
		}
		
		$html->clear();
		unset($html);
	}
	
	function parse_blognone($fetch,$page)
	{
		$html = str_get_html($fetch);
		
		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);
		
		if($parsed_posts_count == 0 && $page->sub_comment == 0) // No early post and not a sub comment page
		{
			// Post
			$post = $html->find('div[class=content-container] div[class=node]',0);
			$post_title = $post->find('h2',0)->plaintext;
			$post_body = $post->find('div[itemprop=description]',0)->plaintext;
			$post_meta = $post->find('div[class=meta] span',0)->plaintext;
			$meta = explode(" ",$post_meta);
//			var_dump($meta);
			$date_index = 0;
			for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
			$date = explode("/",$meta[$date_index]);
			$yy = (int)$date[2] + 2000;
			$mm = $date[1];
			$dd = $date[0];
			$tt = $meta[$date_index+1];
			$author = $meta[1];
			for($i=2;$i<$date_index-2;$i++) { $author = $author.' '.$meta[$i]; }

			$post = new Post_model();
			$post->init();
			$post->page_id = $page->id;
			$post->type = "post";
			$post->title = trim($post_title);
			$post->body = trim($post_body);
			$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			$post->author_id = $post->get_author_id(trim($author));
			$post->insert();
			unset($post);

			// echo "PostTitle:".$post_title;
			// echo "<br/>";
			// echo "PostBody:".$post_body;
			// echo "<br/>";
			// echo "PostAuthor:".$author;
			// echo "<br/>";
			// echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
			// echo "<hr/>";
		}
		
		// Comments at 
		$comments = $html->find('div[id=comments] div[id^=cid-]');
		//echo "CommentCount:".count($comments);
		log_message('info',' parse_blognone : found elements : '.count($comments));
		//echo "<hr>";
		
		// If sub comment page, skip Post entry
		if($page->sub_comment) $i=1;
		else $i=0;
		
		foreach($comments as $c)
		{
			if($i < $parsed_posts_count-1)
			{
				$i++;
				continue;
			}

			$comment_meta = $c->find('div[class=comment-info]',0)->plaintext;
			$comment_body = $c->find('div[class=comment-body]',0)->plaintext;

			$meta = explode(" ",$comment_meta);
//			var_dump($meta);
			$date_index = 0;
			for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
			$date = explode("/",$meta[$date_index]);
			$yy = (int)$date[2] + 2000;
			$mm = $date[1];
			$dd = $date[0];
			$tt = $meta[$date_index+1];
			$author = $meta[6];
			for($i=7;$i<$date_index-2;$i++) { $author = $author.' '.$meta[$i]; }

			$post = new Post_model();
			$post->init();
			$post->page_id = $page->id;
			$post->type = "comment";
			$post->title = null;
			$post->body = trim($comment_body);
			$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			$post->author_id = $post->get_author_id(trim($author));
			$post->insert();
			unset($post);
			
			// echo "CommentTitle:".null;
			// echo "<br/>";
			// echo "CommentBody:".$comment_body;
			// echo "<br/>";
			// echo "CommentAuthor:".$author;
			// echo "<br/>";
			// echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
			// echo "<hr/>";

			$i++;
		}
		
		$html->clear();
		unset($html);
	}

	function parse_pantip_tech($fetch,$page)
	{
		$html = str_get_html($fetch);

		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		else $parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);
		
		$elements = $html->find('table[border=1]');
		$count=count($elements);
		log_message('info',' parse_pantip_tech : found elements : '.$count);
		$i=0; $j=0;
		foreach($elements as $e)
		{
			if($i < $parsed_posts_count)
			{
				$i++;
				continue;
			}
			
			if($e->find('caption') != null) continue;
			
			$post = new Post_model();
			$post->init();
			
			$post->page_id = $page->id;
			if($i==0) 
			{
				$post->type = "post";
				$e = $e->first_child()->first_child();
				$post->title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->children(1)->plaintext));
				$post->body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->children(2)->first_child()->plaintext));
				
				$meta_list = $html->find('font[color=#ffff00]');
				$meta = $meta_list[count($meta_list)-1];
				$txt = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->next_sibling()->plaintext));
				$needle2 = " -[ ";
				$matches = explode($needle2,$txt);
				$author_name = $matches[0];
				$date = explode(" ",trim($matches[1]));
				$tt = $date[4];
			}
			else
			{
				$post->type = "comment";
				//$e = $e->first_child()->first_child()->first_child();
				//while($e->children($j)->tag != "font") $j++;
				$title = $e->find('font',0);
				$post->title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$title->plaintext));

				//$j++;
				//while($e->children($j)->tag != "font") $j++;
				$body = $e->find('font',1);
				$post->body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$body->plaintext));
				
				$e_list = $e->find('font[color]');
				for($i=count($e_list)-1;$i>=0;$i--)
				{
					if($e_list[$i]->getAttribute('color') == "#F0F8FF")
					{
						$meta = $e_list[$i];
						break;
					}
				}
//				$meta = $e->find('font[color]',count($e->find('font[color]'))-1);
				$str = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->plaintext);
				$needle1 = "จากคุณ : ";
				$needle2 = " - [ ";
				$txt = substr($str,strlen($needle1));
				$matches = explode($needle2,$txt);
				$author_name = $matches[0];
				$date = explode(" ",trim($matches[1]));
				$post_date = $date[0]."-".thMonth_decoder($date[1])."-".thYear_decoder($date[2]);
				$tt = $date[3];
			}
			//$post->sentiment = $this->sentiment->check_sentiment($post->body);
			
			$yy = thYear_decoder($date[2]);
			$mm = thMonth_decoder($date[1]);
			$dd = $date[0];
			$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			$post->author_id = $post->get_author_id(trim($author_name));
			
			$post->insert();
			unset($post);
			$i++;
			$j=0;
		}
		
		$html->clear();
		unset($html);
	}

	function parse_pantip_cafe($fetch,$page)
	{
		$html = str_get_html($fetch);
		
		$parsed_posts_count = 0;
		$current_posts = $page->get_posts();
		if($current_posts) $parsed_posts_count = count($current_posts);
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);
		
		
		$elements = $html->find('table[height=36]');
		$count=count($elements);
		log_message('info',' parse_pantip_cafe : found elements : '.$count);
		$i=0;
		foreach($elements as $e)
		{
			if($i < $parsed_posts_count) 
			{ 
				//log_message('info','skip : '.$i);
				$i++;
				continue; // skip post order if less than parsed post
			}
			
			// Check if it is a quote block skip
			$quote = str_get_html($e->outertext);
			$res = $quote->find('img[hspace=3]');
			$quote->clear();
			unset($quote);
			if(count($res) == 0) continue;
			
			$post = new Post_model();
			$post->init();
			
			$post->page_id = $page->id;
			if($i==0) $post->type = "post";
			else $post->type = "comment";
			
			$post->title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->plaintext));
			
			$body1 = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->next_sibling()->plaintext));
			$body2 = null;

			// Search for form
			$form = str_get_html($e->next_sibling()->outertext);
			$res = $form->find('form');
			if(count($res) > 0) // if form found, read post body 2 and adjust meta location
			{
				$meta = $e->next_sibling()->next_sibling()->next_sibling()->next_sibling();
				$body2 =  trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->next_sibling()->next_sibling()->next_sibling()->plaintext));
			}
			else
			$meta = $e->next_sibling()->next_sibling();
			
			$form->clear();
			unset($form);
			
			$body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$e->next_sibling()->plaintext));
			$post->body = $body1.$body2;
			
			// if post 
			if($i==0)
			{
				// check if meta 2
				$needle = "จากคุณ";
				$pos = false;
				while(!$pos)
				{
					if($meta == null)
					{
						$meta = $e->parent(); // RESET! end of tree.
					}
					else
					{
						$haystack = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->plaintext));
						$pos = strstr($haystack,$needle);
						//echo "found:";
						if(!$pos)
						{
							if($meta->next_sibling() == null) $meta = $meta->parent(); // NEXT PARENT NODE. this tree node don't have it.
							else $meta = $meta->next_sibling();
						}
					}
				}
				$meta = $meta->first_child()->first_child();
				$author_name = str_replace(" ","",iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->first_child()->next_sibling()->plaintext));
				$post_date = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$meta->next_sibling()->first_child()->next_sibling()->plaintext);
			}
			else // else comment
			{
				//Find Meta with table cellspacing=1 from e->parent();
				$parent = $e->parent();
				$meta_element_pos = count($parent->find('table[cellspacing=1] tr td'));
				if($meta_element_pos == 0) continue;
				$author = $parent->find('table[cellspacing=1] tr td',$meta_element_pos-5);
				$author_name = str_replace(" ","",iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author->plaintext));
				$date = $parent->find('table[cellspacing=1] tr td',$meta_element_pos-3);
				$post_date = iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date->plaintext);
			}

			$author_name = substr($author_name,1);
			$post->author_id = $post->get_author_id(trim($author_name));
			$post_date = substr($post_date,1);
			$post_date = trim($post_date);
			
			// if วันสิ้นปี, วันปีใหม่, replace back to normal
			$special_date = array('วันสิ้นปี' => '31 ธ.ค.','วันปีใหม่' => '1 ม.ค.', 'วันนักประดิษฐ์' => '2 ก.พ.', 'วันวาเลนไทน์' => '14 ก.พ.','วันเข้าพรรษา'=>'27 ก.ค.','วันแม่แห่งชาติ'=>'12 ส.ค.','วันเกิด PANTIP.COM'=>'7 ต.ค.','วันปิยมหาราช'=>'23 ต.ค.','วันลอยกระทง'=>'10 พ.ย.','วันพ่อแห่งชาติ'=>'5 ธ.ค.','วันจักรี'=>'6 เม.ย.','วันฉัตรมงคล'=>'5 พ.ค.','วันมาฆบูชา'=>'7 มี.ค.','วันสตรีสากล'=>'8 มี.ค.');
			mb_internal_encoding("UTF-8");
			foreach($special_date as $k=>$v)
			{
				$post_date = preg_replace('/'.$k.'/',$v,$post_date);
			}
			// end if
			
			$date = explode(" ",$post_date);
			
			$yy = thYear_decoder($date[2]);
			$mm = thMonth_decoder($date[1]);
			$dd = $date[0];
			$tt = $date[3];
			$post->post_date = $yy."-".$mm."-".$dd." ".$tt;
			$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
			
			$post->insert();
			unset($post);
			$i++;
		}
		
		$html->clear();
		unset($html);
	}
	
	function run($domain_id=null,$limit=null,$offset=null)
	{
		// log_message('info'," PARSER : Turn all errors into PHP exception");
		// set_error_handler('handle_error');
		// set_error_handler(array(&$this, 'handle_error'));
	    
		//echo "PARSER : Run Page ";
		
		$option = array(
			'parse_post' => 0,
			'outdate' => 0,
			'size !=' => 'NULL'
		);
		
		if($domain_id != null)
		{
			$option['domain_id'] = $domain_id;
		}

		$this->db->order_by('id','asc');
		if($offset!=null && $limit!=null) $this->db->limit($limit,$offset);
		$query = $this->db->get_where('page',$option);
		
		if($query->num_rows() > 0)
		{
			log_message('info', 'Parse : found : '.$query->num_rows()." pages.");
			
			foreach($query->result() as $row)
			{
				// Activate Gabage Collection
				gc_enable();
				$gc_cycles = gc_collect_cycles();
				log_message('info','PARSER : GC : '.$gc_cycles);
				
				// Reset PHP Timeout to 2min
				set_time_limit(5*60);
				
				$page = new Page_model();
				$page->init($row->id);
				
				echo ','.$page->id;
				
				// Check if ROOT PAGE skip
				$pattern = $this->custom_model->get_value('domain','child_pattern',$page->domain_id);
				$sub_comment = $this->custom_model->get_value('domain','sub_comment_pattern',$page->domain_id);
				if($sub_comment == null)
				{
					if(!preg_match($pattern, $page->url))
					{
						echo "(root)";
						//log_message('info','Parse : found ROOT PAGE skip');
						continue;
					}
				}
				else
				if (!preg_match($pattern, $page->url) && !preg_match($sub_comment, $page->url))
				{
					echo "(root)";
					//log_message('info','Parse : found ROOT PAGE skip');
					continue;
				}
				
				$fetch = $page->read_file();
				if($fetch == false)
				{
					log_message('info',"page_model : ".$page->id." : read file error");
					continue;
				}
//				else { echo "file not null"; }
								
				if ($page->domain_id == 15 || $page->domain_id == 29 || $page->domain_id == 16 || $page->domain_id == 21 || $page->domain_id == 18 || $page->domain_id == 10 || $page->domain_id == 13 || $page->domain_id == 4 || $page->domain_id == 25 || $page->domain_id == 26 || $page->domain_id == 27 || $page->domain_id == 20 || $page->domain_id == 28 || $page->domain_id >= 30 || $page->domain_id == 1 || $page->domain_id == 3 || $page->domain_id == 2 || $page->domain_id == 12)
				{
					// load helper file from table domain.name
					$domain = new Domain_model();
					$domain->init($page->domain_id);
					$helper_name = $domain->helper_name;
					$this->load->helper('/parser/parse_'.$helper_name);
					
					log_message('info'," PARSER : domain=".$helper_name);
					$function = 'parse_'.$helper_name;
					
					try
					{
						$res = $function($fetch,$page,false);
						$page->set_parse(1);
					}
					catch (Exception $e)
					{
						// iconv() warning is expected, do not flag as error
						if (stripos($e->getMessage(), "iconv()") !== False)
						{
							// log_message('info', "PARSER : Encounter iconv() error, ignore : page_id " . $page->id
							// 		. " : exception_code : " . $e->getCode()
							// 		. " : exception_message " . $e->getMessage()
							// 		. " : exception_trace : " . $e->getTraceAsString()
							// 	);
							$page->set_parse(1);
						}
						else
						{
							echo stripos($e->getMessage(), "iconv()");
							log_message('error', "PARSER : Parse error : page_id " . $page->id
								. " : exception_code : " . $e->getCode()
								. " : exception_message " . $e->getMessage()
								. " : exception_trace : " . $e->getTraceAsString()
							);
							
							// Set parse_post to -1 to indicate error
							$page->set_parse(-1);
							
							echo 'PARSER : Parse error : page_id '.$page->id.' : exception_message '.$e->getMessage();
						}
					}
				}
				
				if ($page->domain_id == 2)
				{
					log_message('info'," PARSER : domain=blognone");
					
					$this->parse_blognone($fetch,$page);
					$page->set_parse(1);
				}
				if ($page->domain_id == 6)
				{
					log_message('info'," PARSER : domain=lcdtvth_review");
					
					$this->parse_lcdtvth_review($fetch,$page);
					$page->set_parse(1);
				}

				if ($page->domain_id == 11)
				{
					log_message('info'," PARSER : domain=monavista_forum");
					
					$this->parse_monavista_forum($fetch,$page);
					$page->set_parse(1);
				}
				if ($page->domain_id == 14)
				{
					log_message('info'," PARSER : domain=siamphone_forum");

					$this->parser_siamphone_forum($fetch,$page);
					$page->set_parse(1);
				}

				if ($page->domain_id == 17)
				{
					log_message('info'," PARSER : domain=notebook4game_forum");

					$this->parse_notebook4game_forum($fetch,$page);
					$page->set_parse(1);
				}

				if ($page->domain_id == 19)
				{
					log_message('info'," PARSER : domain=parse_thaimobilecenter_article");

					$this->parse_thaimobilecenter_article($fetch,$page);
					$page->set_parse(1);
				}

				if ($page->domain_id == 22)
				{
					log_message('info'," PARSER : domain=parse_bloggang_blog");

					$this->parse_bloggang_blog($fetch,$page);
					$page->set_parse(1);
				}
				if ($page->domain_id == 23)
				{
					log_message('info'," PARSER : domain=parse_mxphone_article");

					$this->parse_mxphone_article($fetch,$page);
					$page->set_parse(1);
				}
				if ($page->domain_id == 24)
				{
					log_message('info'," PARSER : domain=parse_vmodtech_forum");

					$this->parse_vmodtech_forum($fetch,$page);
					$page->set_parse(1);
				}

				if ($page->domain_id == 26)
				{
					log_message('info'," PARSER : domain=parse_manager_news");

					$this->parse_manager_news($fetch,$page);
					$page->set_parse(1);
				}
				
				unset($page);
				unset($fetch);
			}
		}
		
		unset($query);
	}
	
	function total_post($id)
	{
		$page = new Page_model();
		$page->init($id);
		
		$posts = $page->get_posts();
		if($posts)
		{
			echo "total posts : ".count($posts);
			echo "<br>";
			foreach($posts as $p)
			{
				echo "page: ".$p->page_id.":title:".$p->title;
				echo "<br>";
			}
		} 
		else echo "total posts : 0";
		echo "<br>";
		
		unset($post);

	}
	
	function test2($id)
	{
		$page = new Page_model();
		$page->init($id);
		
		$fetch = $page->read_file();
		if($fetch == false)
		{
			log_message('info',"page_model : ".$this->id." : read file error");
			return false;
		}
		
		$this->parse_lcdtvth_review($fetch,$page);
		unset($post);
	}
	
	function test($id,$type=null)
	{
		$page = new Page_model();
		$page->init($id);
		
		$fetch = $page->read_file();
		if($fetch == false)
		{
			echo 'page_model : '.$id.' : read file error';
			return false;
		}
		
		// $current_posts = $page->get_posts();
		// if($current_posts) $parsed_posts_count = count($current_posts);
		// else $parsed_posts_count = 0;
		$parsed_posts_count = 0;
		log_message('info',' parsed_posts_count : '.$parsed_posts_count);
		
		// Check if ROOT PAGE skip
		$pattern = $this->custom_model->get_value('domain','child_pattern',$page->domain_id);
		$sub_comment = $this->custom_model->get_value('domain','sub_comment_pattern',$page->domain_id);
		if (!preg_match($pattern, $page->url) && !preg_match($sub_comment, $page->url))
		{
			echo 'Parse : found ROOT PAGE skip';
		}
		else
		{
			$html = str_get_html($fetch);
			
			if($page->domain_id == 15 || $page->domain_id == 29 || $page->domain_id == 16 || $page->domain_id == 21 || $page->domain_id == 18 || $page->domain_id == 10 || $page->domain_id == 13 || $page->domain_id == 25 || $page->domain_id == 26 || $page->domain_id == 27 || $page->domain_id == 4 || $page->domain_id == 20 || $page->domain_id == 28 || $page->domain_id >= 30 || $page->domain_id == 1 || $page->domain_id == 3 || $page->domain_id == 2 || $page->domain_id == 12)
			{
				// load helper from domain id of page.
				// load helper file from table domain.name
				$domain = new Domain_model();
				$domain->init($page->domain_id);
				$helper_name = $domain->helper_name;
				$this->load->helper('/parser/parse_'.$helper_name);
				
				$function = 'parse_'.$helper_name;
				$function($fetch,$page,true);
				
				unset($domain);
				$html->clear();
				unset($html);
			}
			
			
			if($type == 4) // Dek-d
			{
				$dead_page = $html->find('div[id=main_contentRed]',0);
				if($dead_page != null) 
				{
					echo "Page is dead.";
				}
				else
				{
					if($parsed_posts_count == 0) // No early post
					{
						// Post Title at div.maincontent, 1st h1 element
						$main_content = $html->find('div[class=maincontent] h1');
						$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));
			
						// Post Body at div.boardmsg
						$board_msg = $html->find('div[class=boardmsg]');
						$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));
			
						// Post Meta at ul#ownerdetail
						$author = $html->find('ul[id=ownerdetail] li b');
						$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));
			
						$date_time = $html->find('ul[id=ownerdetail] li',2);
						$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));
			
						// View Count
						$page_info = $html->find('div[class=maincontent] p span strong');
						$page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));
			
						$date = explode(" ",$post_date);
						$yy = thYear_decoder($date[4]);
						$mm = thMonth_decoder($date[3],'full');
						$dd = $date[2];
						$tt = $date[6];
			
						echo "PostTitle:".$post_title;
						echo "<br>";
						echo "PostBody:".$post_body;
						echo "<br>";
						echo "PostAuthor:".$post_author;
						echo "<br>";
						echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
						echo "<br>";
						echo "PageView:".$page_view;
						echo "<br>";
						echo "<hr>";
					}
			
					// Comments at 
					$comments = $html->find('div[id=boardListComment] ul[id=listComment] li[class=bd1soCCC]');
					echo "CommentCount:".count($comments);
					echo "<hr>";
			
					$i=0;
					foreach($comments as $c)
					{
						if($i < $parsed_posts_count-1)
						{
							$i++;
							continue;
						}
				
						$c_title = $c->find('div[class=listCommentHead]',0);
						$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));
						$c_body = $c->find('div[class=commentBox] div[class=boardmsg]',0);
						$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));
						$c_author = $c->find('ul[id=ownerdetail] li b',0);
						$comment_author = trim(iconv("tis-620","utf-8//IGNORE",$c_author->plaintext));
						$c_date_time = $c->find('ul[id=ownerdetail] li',2);
						$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));
				
						$date = explode(" ",$comment_date);
						$yy = thYear_decoder($date[4]);
						$mm = thMonth_decoder($date[3],'full');
						$dd = $date[2];
						$tt = $date[6];
				
						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
						echo "<br>";
						echo "<hr>";
				
						$i++;
					}
				}
				$html->clear();
				unset($html);
			}
			if($type == 2) // Blognone
			{
				// Post
				$post = $html->find('div[class=content-container] div[class=node]',0);
				$post_title = $post->find('h2',0)->plaintext;
				$post_body = $post->find('div[itemprop=description]',0)->plaintext;
				$post_meta = $post->find('div[class=meta] span',0)->plaintext;
				$meta = explode(" ",$post_meta);
//				var_dump($meta);
				$date_index = 0;
				for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
				$date = explode("/",$meta[$date_index]);
				$yy = (int)$date[2] + 2000;
				$mm = $date[1];
				$dd = $date[0];
				$tt = $meta[$date_index+1];
				$author = $meta[1];
				for($i=2;$i<$date_index-2;$i++) { $author = $author.' '.$meta[$i]; }
				
				
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$author;
				echo "<br/>";
				echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
				echo "<hr/>";
				
				// Comments Array
				$comments = $html->find('div[id=comments] div[id^=cid-]');
				$count = count($comments);
				if($count > 0)
				{
					foreach($comments as $c)
					{
						$comment_meta = $c->find('div[class=comment-info]',0)->plaintext;
						$comment_body = $c->find('div[class=comment-body]',0)->plaintext;
						
						$meta = explode(" ",$comment_meta);
//						var_dump($meta);
						$date_index = 0;
						for($i=0;$i<count($meta);$i++) { if($meta[$i] == "on") $date_index=$i+1; }
						$date = explode("/",$meta[$date_index]);
						$yy = (int)$date[2] + 2000;
						$mm = $date[1];
						$dd = $date[0];
						$tt = $meta[$date_index+1];
						$author = $meta[6];
						for($i=7;$i<$date_index-2;$i++) { $author = $author.' '.$meta[$i]; }
						
						
						echo "CommentTitle:".null;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$author;
						echo "<br/>";
						echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
						echo "<hr/>";
					}
				}
				
				$html->clear();
				unset($html);
			}
			if($type == 6) // LCD TV Thailand - Review
			{
				// Page view td[colspan=3]
				$read_write = $html->find('td[colspan=3]',0);
				$read_write_str = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$read_write->plaintext)));
				var_dump($read_write_str);
				for($i=0,$j=0;$i<count($read_write_str) && $j<2;$i++) // Post Author is at 3rd element
				{
					// Skip those white space, looking for something with chars
					if(strlen(trim($read_write_str[$i])) > 0) $j++;
				}
				$post_view = $read_write_str[$i-1];
				echo "PostView:".$i.":".$post_view;
				echo "<hr>";
				
				// Post td[height=25] span[class=font-2]
				$title = $html->find('table[width=97%] span[class=font-2]',0);
				$post_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$title->plaintext));
				$body = $html->find('table[width=550]',0);
				$meta = $body->find('div',0);
				$post_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$body->plaintext));
				$post_meta = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$meta->plaintext)));
				
				for($i=0,$j=0;$i<count($post_meta) && $j<3;$i++) // Post Author is at 3rd element
				{
					// Skip those white space, looking for something with chars
					if(strlen(trim($post_meta[$i])) > 0) $j++;
				}
				//var_dump($post_meta);
				
				echo "PostTitle:".$post_title;
				echo "<br/>";
				echo "PostBody:".$post_body;
				echo "<br/>";
				echo "PostAuthor:".$post_meta[$i-1];
				echo "<br/>";
				echo "PostDate:".null;
				echo "<hr/>";
				
				// Comment div[class=style1]
				$comments = $html->find('div[class=style1]');
				if(count($comments) > 0)
				{
					foreach($comments as $c)
					{
						$title = $c->find('table[height=27]',0);
						$comment_title = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$title->plaintext));
						$body = $c->find('table[class=text2] tbody tr td[align=left]',0)->first_child()->first_child();
						$comment_body = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$body->plaintext));
						$meta = $c->find('table[class=text2] tbody tr td[align=left]',0)->first_child()->last_child();
						$author = $meta->find('strong',0);
						$comment_meta = explode(" ",trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$meta->plaintext)));
						$comment_author = trim(iconv("windows-874","utf-8//TRANSLIT//IGNORE",$author->plaintext));
						$date = explode("/",$comment_meta[2]);
						$yy = $date[2];
						$mm = $date[1];
						$dd = $date[0];
						$tt = $comment_meta[3];
						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br/>";
						echo "CommentDate:".$yy."-".$mm."-".$dd." ".$tt;
						echo "<hr/>";
					}
				}
				
				$html->clear();
				unset($html);
			}
			if($type == 10) // ADSL Thailand
			{
				$parsed_posts_count = 0;
				
				if($parsed_posts_count == 0) // No early post and not a sub comment page
				{
					// Post Title at div.maincontent, 1st h1 element
					$main_content = $html->find('title');
					$post_title = trim($main_content[0]->plaintext);

					// Post Body at div.boardmsg
					$board_msg = $html->find('div[class=content]');
					$post_body = trim($board_msg[0]->plaintext);

					// Post Meta at ul#ownerdetail
					$author = $html->find('div[class=popupmenu memberaction] a');
					$post_author = trim($author[0]->plaintext);

					$date_time = $html->find('span[class=date]',0);
					$post_date = trim($date_time->plaintext);

					// View Count
					// $page_info = $html->find('div[class=maincontent] p span strong');
					// $page_view = trim($page_info[1]->plaintext);

					$str0 = explode("&nbsp;",$post_date);
					$date = $str0[0];
					$tt = substr($str0[1],0,5).':00';
					$str = explode("-",$date);
					$yy = $str[2];
//					$mm = $str[1];
					$month = $str[1]; //or whatever 

					for($i=1;$i<=12;$i++)
					{ 
						if(date("M", mktime(0, 0, 0, $i, 1, 0)) == $month)
						{ 
							if($i<10) $mm = '0'.$i;
							else $mm = $i;
							break; 
						} 
					}
					$dd = $str[0];


					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
					echo "<hr/>";

				}

				// Comments at ul#listComment li.bd1soCCC
				$comments = $html->find('li[id^=post_]');

				$i = 0;
		        foreach($comments as $c)
		        {    
		            if($i > 0){ 
					//Comment Title as div.listCommentHead
					$c_title = $c->find('span a[name^=post]',0);
					$comment_title = trim($c_title->plaintext);

					//Comment Body as div.commentBox div.boardmsg
					$c_body = $c->find('div[id^=post_message_] blockquote',0);
					$comment_body = trim($c_body->plaintext);

					//Comment Author as ui#ownerdetail li b
					$c_author = $c->find('div[class=popupmenu memberaction] a',0);
					$comment_author = trim($c_author->plaintext);

					//Comment Date ul#ownerdetail li
					$c_date_time = $c->find('div span span',0);
					$comment_date = trim($c_date_time->plaintext);

					$str0 = explode("&nbsp;",$comment_date);
					$date = $str0[0];
					$tt = substr($str0[1],0,5).':00';
					$str = explode("-",$date);
					$yy = $str[2];
//					$mm = $str[1];
					$month = $str[1]; //or whatever 

					for($i=1;$i<=12;$i++)
					{ 
						if(date("M", mktime(0, 0, 0, $i, 1, 0)) == $month)
						{ 
							if($i<10) $mm = '0'.$i;
							else $mm = $i;
							break; 
						} 
					}
					
					$dd = $str[0];


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
		            $i++;
				}

				$html->clear();
				unset($html);
			}
			if($type == 11) // Monavista Forum
			{
				$parsed_posts_count = 0;

				if($parsed_posts_count == 0) // No early post and not a sub comment page
				{
					// Post Title at div.maincontent, 1st h1 element
					$main_content = $html->find('title');
					$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

					// Post Body at div.boardmsg
					$board_msg = $html->find('div[class=content]');
					$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

					// Post Meta at ul#ownerdetail
					$author = $html->find('div[class=postdetails] a');
					$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

					$date_time = $html->find('li div span span',0);
					$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

					// View Count
					// $page_info = $html->find('div[class=maincontent] p span strong');
					// $page_view = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$page_info[1]->plaintext));

					$str = explode(" ",$post_date);
					$yy0 = $str[2];
	//				$mm = $str[1];
					$month = $str[1]; //or whatever 

					for($i=1;$i<=12;$i++)
					{ 
						if(date("F", mktime(0, 0, 0, $i, 1, 0)) == $month)
						{ 
							if($i<10) $mm = '0'.$i;
							else $mm = $i;
							break; 
						} 
					}
					$dd0 = $str[0];
					$dd = preg_replace("/[^0-9]/", '',$dd0);
					$str1 = explode("&nbsp;",$yy0);
					$yy = $str1[0];
					$tt = $str1[1];


					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$yy."-".$mm."-".$dd." ".$tt;
					echo "<hr/>";

				}

				// Comments at ul#listComment li.bd1soCCC
				$comments = $html->find('li[class=postbitlegacy]');
				$i = 0;
		        foreach($comments as $c)
		        {    
		            if($i > 0)
					{
					//Comment Title as div.listCommentHead
					$c_title = $c->find('span a[name^=post]',0);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					//Comment Body as div.commentBox div.boardmsg
					$c_body = $c->find('div[id^=post_message_] blockquote',0);
					$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

					//Comment Author as ui#ownerdetail li b
					$c_author = $c->find('div[class=postdetails] a',0);
					$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

					//Comment Date ul#ownerdetail li
					$c_date_time = $c->find('div span span',0);
					$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));

					$str = explode(" ",$comment_date);
					$yy0 = $str[2];

	//				$mm = $str[1];
					$month = $str[1]; //or whatever 
					for($i=1;$i<=12;$i++)
					{ 
						if(date("F", mktime(0, 0, 0, $i, 1, 0)) == $month)
						{ 
							if($i<10) $mm = '0'.$i;
							else $mm = $i;
							break; 
						} 
					}

					$dd0 = $str[0];
					$dd = preg_replace("/[^0-9]/", '',$dd0);
					$str1 = explode("&nbsp;",$yy0);
					$yy = $str1[0];
					$tt = $str1[1];

					/*$date = explode(" ",$comment_date);
					$yy = thYear_decoder($date[4]);
					$mm = thMonth_decoder($date[3],'full');
					$dd = $date[2];
					$tt = $date[6];*/

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

		            $i++;
				}

				$html->clear();
				unset($html);
			}
			if($type == 14) // Siamphone
			{
				$parsed_posts_count = 0;

				if($parsed_posts_count == 0) 
				{
					$post = $html->find('table[width=620]',0);
					
					$main_content = $post->find('span[class=postdetails] font[size=1] a',0);
					$post_title = trim($main_content->plaintext);
					
					$board_msg = $post->find('span[class=postbody]',0);
					$post_body = trim($board_msg->plaintext);

					$meta = $post->parent()->prev_sibling();
					$author = $meta->find('span[class=name] b',0);
					$post_author = trim($author->plaintext);

					$date_time = $post->find('font[size=1]',1);
					$post_date = trim($date_time->plaintext);

					$date = explode(" ",$post_date);
	                $post_date = $date[4]."-".enMonth_decoder($date[3],'cut')."-".$date[2]." ".$date[5];
					
					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";

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
					unset($post);

				}

				$comments = $html->find('table[width=620]');
				$i = 0;

				foreach($comments as $c){ 	
					if($i > 0){
						
						$c_title = $c->find('span[class=postdetails] font[size=1] a',0);
						$comment_title = null;

						$c_body = $c->find('span[class=postbody]',0);
						$comment_body = trim($c_body->plaintext);

						$meta = $c->parent()->prev_sibling();
						$c_author = $meta->find('span[class=name] b',0);
						$comment_author = trim($c_author->plaintext);

						$c_date_time = $c->find('font[size=1]',1);
						$comment_date = trim($c_date_time->plaintext);

						$date = explode(" ",$comment_date);
		                $comment_date = $date[4]."-".enMonth_decoder($date[3],'cut')."-".$date[2]." ".$date[5];

						echo "CommentTitle:".$comment_title;
						echo "<br/>";
						echo "CommentBody:".$comment_body;
						echo "<br/>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<br/>";
						echo "<hr/>";

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
						unset($post);
					}
					$i++;
				}
				$html->clear();
				unset($html);
			}
			if($type == 17)
			{
				$parsed_posts_count = 0;

				if($parsed_posts_count == 0){

					$main_content = $html->find('div[id=contentMain] div[id=postlist] h2');
					$post_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$main_content[0]->plaintext));

					$board_msg = $html->find('div.content');
					$post_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$board_msg[0]->plaintext));

					$author = $html->find('div.userinfo a');
					$post_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$author[0]->plaintext));

					$date_time = $html->find('div.posthead span.date',0);
					$post_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$date_time->plaintext));

					$date = explode("&nbsp;",$post_date);
					$day = explode("-",$date[0]);
					$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
					$post_date = $y."-".$day[1]."-".$day[0]." ".$date[1];

					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";

					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_date;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					//$post->insert();
					unset($post);
				}

				$comments = $html->find('li[id^=post_]');

					$i = 0;
					foreach($comments as $c){

						if($i > 0){
							$c_title = $c->find('div.posthead span.nodecontrols a',0);
							$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

							$c_body = $c->find('div.content',0);
							$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body->plaintext));

							$c_author = $c->find('div.userinfo a',0);
							$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

							$c_date_time = $c->find('div.posthead span.date',0);
							$comment_date = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_date_time->plaintext));	
							$date = explode("&nbsp;",$comment_date);
							$day = explode("-",$date[0]);
							$y = date('Y',mktime(0,0,0,0,0,$day[2]+1));
							$comment_date = $y."-".$day[1]."-".$day[0]." ".$date[1];
							
							echo "CommentTitle:".$comment_title;
							echo "<br/>";
							echo "CommentBody:".$comment_body;
							echo "<br/>";
							echo "CommentAuthor:".$comment_author;
							echo "<br>";
							echo "CommentDate:".$comment_date;
							echo "<br/>";
							echo "<hr/>";

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
							unset($post);
						}
						$i++;
					}
				$html->clear();
				unset($html);
			}
			if($type == 19)
			{
				$html = str_get_html($fetch);

				$parsed_posts_count = 0;

				if($parsed_posts_count == 0){

					$main_content = $html->find('td[class=16] font',0);
					$post_title = trim(iconv("tis-620","utf-8",$main_content->plaintext));

					$board_msg = $html->find('td[class=16] span[class=text12]',0);
					$post_body = trim(iconv("tis-620","utf-8",$board_msg->plaintext));

					$author = $html->find('td[class=16] span[class=text12] font[color=#000000]',0);
					$post_author = trim(iconv("tis-620","utf-8",$author->plaintext));

					$date_time = $html->find('table[width=778] table[width=96%] table[cellpadding=1] td[class=text10]',1);
					$post_date = trim(iconv("tis-620","utf-8",$date_time->plaintext));

					$date = explode("/",$post_date);
					$dd = explode(":",$date[0]);
					$yy = thYear_decoder($date[2]);

					$post_date = $yy."-".$date[1]."-".$dd[1];

					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";

					$post = new Post_model();
					$post->init();
					$post->page_id = $page->id;
					$post->type = "post";
					$post->title = $post_title;
					$post->body = $post_date;
					$post->post_date = $post_date;
					$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
					$post->author_id = $post->get_author_id(trim($post_author));
					//$post->insert();
					unset($post);
				}

				$comments = $html->find('table[width=96%] table[width=95%]');

				$i = 0;
				foreach($comments as $c){ 	

					$c_title = $c->find('td[width=83%] td[class=text10] font',1);
					$comment_title = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_title->plaintext));

					if(!empty($comment_title)){

						$c_body = $c->find('td[width=83%] td[class=text12]');
						$comment_body = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_body[0]->plaintext));	

						$c_author = $c->find('td[width=73%] font',0);
						$comment_author = trim(iconv("tis-620","utf-8//TRANSLIT//IGNORE",$c_author->plaintext));

						$c_date_time = $c->find('td[width=83%] td[class=text10] font',2);
						$comment_date = trim(iconv("tis-620","utf-8",$c_date_time->plaintext));

						$cdate = explode("/",$comment_date);
						$cd = explode("&nbsp;",$cdate[0]);
						$cy = explode(" ",$cdate[2]);
						$yy = thYear_decoder($cy[0]);
						$dd = onlyNum($cd[1]);
						$comment_date = $yy."-".$cdate[1]."-".$dd." ".$cy[2];

						echo "CommentTitle:".$comment_title;
						echo "<br>";
						echo "CommentBody:".$comment_body;
						echo "<br>";
						echo "CommentAuthor:".$comment_author;
						echo "<br>";
						echo "CommentDate:".$comment_date;
						echo "<br>";
						echo "<hr>";

						$post = new Post_model();
						$post->init();
						$post->page_id = $page->id;
						$post->type = "comment";
						$post->title = $comment_title;
						$post->body = trim($comment_body);
						$post->post_date = $comment_date;
						$post->parse_date = mdate('%Y-%m-%d %H:%i',time());
						$post->author_id = $post->get_author_id(trim($author));
						//$post->insert();
						unset($post);
					}
				}
				$html->clear();
				unset($html);
			}
			if($type == 20)
			{
				$parsed_posts_count = 0;

				if($parsed_posts_count == 0){

					$main_content = $html->find('#topic_title');
					$post_title = trim($main_content[0]->plaintext);

					$board_msg = $html->find('#topic_body');
					$post_body = trim($board_msg[0]->plaintext);

					$author = $html->find('#topic_author',0);
					$split_author = explode(" ",trim($author->plaintext));
					$post_author = trim($split_author[count($split_author)-1]);

					$date_time = $html->find('#topic_date',0);
					$post_date = trim($date_time->plaintext);

					$date = explode(" ",trim(str_replace("ตั้งเมื่อ:","",$post_date)));
					var_dump($date);
					if(preg_match("/^[a-zA-Z]/",$date[1])){
						$mm = enMonth_decoder($date[1],"cut");
					}else{
						$mm = thMonth_decoder($date[1],"cut");
					}

					$post_date = $date[2]."-".$mm."-".$date[0];

					echo "PostTitle:".$post_title;
					echo "<br/>";
					echo "PostBody:".$post_body;
					echo "<br/>";
					echo "PostAuthor:".$post_author;
					echo "<br/>";
					echo "PostDate:".$post_date;
					echo "<hr/>";

				}

				$comments = $html->find('div[id^=comment_entry]');

				foreach($comments as $c){ 	

					$c_title = $c->find('.comment_seq',0);
					$comment_title = trim($c_title->plaintext);

					$c_body = $c->find('.comment_body p',0);
					$comment_body = trim($c_body->plaintext);

					$c_author = $c->find('.comment_author',0);
					$comment_author = trim($c_author->plaintext);

					$c_date_time = $c->find('.comment_time',0);
					$comment_date = trim($c_date_time->plaintext);

					$date = explode(" ",trim(str_replace("เขียนเมื่อ","",$comment_date)));

					if(preg_match("/^[a-zA-Z]/",$date[1])){
						$mm = enMonth_decoder($date[1],"cut");
					}else{
						$mm = thMonth_decoder($date[1],"cut");
					}

					$comment_date = $date[2]."-".$mm."-".$date[0];

					echo "CommentTitle:".$comment_title;
					echo "<br>";
					echo "CommentBody:".$comment_body;
					echo "<br>";
					echo "CommentAuthor:".$comment_author;
					echo "<br>";
					echo "CommentDate:".$comment_date ;
					echo "<br>";
					echo "<hr>";
				}

				$html->clear();
				unset($html);
			}
			if($type == 24)
			{
				$this->parse_vmodtech_forum($fetch,$page,true);
				$html->clear();
				unset($html);
			}
		}
	}
}
