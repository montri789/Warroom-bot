<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test_pattern extends CI_Controller {
	
	var $sub_process = "-- ";
	
	function child()
	{
		$url = "/showthread.php?520";
		$id = 84369;
		$page = new Page_model();
		$page->init($id);
		
		echo $this->page_model->check_url($url,$page);
		
		unset($page);
	}
	
	function domain()
	{
		// lcdtv
		//$url = "./detail.asp?param_id=850";
		$url = "/index.php?board=37.0";
		
		$domain_id = 206;
		
		$group = $this->custom_model->get_value('domain','group_pattern',$domain_id);
		$sub_comment = $this->custom_model->get_value('domain','sub_comment_pattern',$domain_id);
		$child = $this->custom_model->get_value('domain','child_pattern',$domain_id);
		
		echo $url;
		echo "<br>";
		if (preg_match($group, $url)) echo "group";
		else if (preg_match($child, $url)) echo "child";
		else if ($sub_comment!=null && preg_match($sub_comment, $url)) echo "sub_comment";
		else echo "bad";
		echo "<hr>";
		
		$parent_page_id = 67045;
		// if url start with "?", get parent page (which is not also a sub_comment page) and entail
		if($url[0] == "?")
		{
			$parent = new Page_model();
			$parent->init($parent_page_id);
			while($parent->sub_comment) $parent->init($parent->parent_page_id);
			
			$str = explode("?",$parent->url);
			$url = $str[0].$url;
		}
		
	}
}