<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Spider extends CI_Controller {
	
	public function index()
	{
	}
	
	public function reset_domain()
	{
		$list = $this->domain_model->list_domain("fetching");
		
		echo "found domains: ".count($list)."\n";
		foreach($list as $d)
		{
			$d->status = "idle";
			$d->update();
			echo "idle domain: ".$d->name."\n";
		}
	}
	
	public function test_fetch2()
	{
		$timer_start = microtime(true);

		$url = "http://pantip.com/cafe/mbk/listerT.php?";
//		$url = "http://pantip.com/cafe/mbk/topic/T11424628/T11424628.html";
		
		$options = array( 
		        CURLOPT_RETURNTRANSFER => true,         // return web page 
		        CURLOPT_HEADER         => false,        // don't return headers 
		        CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
		        CURLOPT_ENCODING       => "",           // handle all encodings 
		        CURLOPT_USERAGENT      => "Googlebot",     // who am i 
		        CURLOPT_AUTOREFERER    => true,         // set referer on redirect 
		        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect 
		        CURLOPT_TIMEOUT        => 120,          // timeout on response 
		        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
		        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl 
		        CURLOPT_SSL_VERIFYPEER => false,        // 
		        CURLOPT_VERBOSE        => 1                // 
		    ); 

		    $ch      = curl_init($url); 
		    curl_setopt_array($ch,$options); 
		    $content = curl_exec($ch); 
		    $err     = curl_errno($ch); 
		    $errmsg  = curl_error($ch) ; 
		    $header  = curl_getinfo($ch); 
		    curl_close($ch);

		$this->load->helper('simple_html_dom');
		$html = str_get_html($content);
		$links = $html->find('a');

		$timer_stop = microtime(true);
		$exec_time = $timer_stop-$timer_start;
		$size = $header["size_download"]/1024;

		$html->clear();
		unset($html);
		
		echo "links: ".count($links).", ";
		echo "execution: ".$exec_time."sec. , ";
		echo "size:".$size."KB , ";
		echo "<hr>";
		
		foreach($links as $element)
		{
			echo $element->href . '<br />';
		}
	}
}