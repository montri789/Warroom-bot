<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Segmenter extends CI_Controller {
	
	var $start = 0;
	
	function test_swath($id=null)
	{
		if($id==null) { echo "invalid id."; exit;}
		
		$post = new Post_model();
		$post->init($id);
		
		$body = $post->body;
		$segmented = $this->swath($body);
		$post->segmented = $segmented;
		$post->update();
	}
	
	function update($start=0)
	{
		$update=true;
		$this->start = $start;
		$this->all($update);
	}
	
	function all($update=false)
	{
		// Reset PHP Timeout to 5min
		set_time_limit(5*60);
		
		if($update)
		{
			$option = array("is_segmented" => 0);
			$this->db->select('id');
			$this->db->limit(1000,$this->start);
			$query = $this->db->get_where('post',$option);
		}
		else
		{
			$this->db->select('id');
			$query = $this->db->get('post');
		}
		
		echo PHP_EOL.'found posts: '.$query->num_rows();
		
		if($query->num_rows() > 0)
		{
			$post = new Post_model();
			foreach($query->result() as $row)
			{
				echo ','.$row->id;
				$post->init($row->id);

				$body = $post->body;
				if($post->type == 'post') { $body = $post->title.' '.$body; }
				
				// Reset PHP Timeout to 1min
				set_time_limit(60);
				
				$segmented = $this->swath($body);
				$post->segmented = $segmented;
				$post->is_segmented = 1;
				$post->update();
			}
			unset($post);
			echo "destroy model";
		}
	}
	
	function swath($str)
	{
		$str = iconv('UTF-8', 'TIS-620', trim($str));
		write_file('src\in_'.$this->start.'.txt',$str,'w+');
		
		system('swath.exe -b " " -d data -m long < src\in_'.$this->start.'.txt > src\long5_'.$this->start.'.txt');
		
		$read = read_file('src\long5_'.$this->start.'.txt');
		$read = iconv('TIS-620', 'UTF-8', rtrim($read));
		$read = preg_replace('/  /', '', $read);

		return $read;
	}
}