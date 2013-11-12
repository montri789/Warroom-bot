<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test_memcache extends CI_Controller {
	
	public function page_domain($domain_id)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		echo "memcache activated\n";

		$exist_url = array();
		$key_pattern = '/\Apage\-'.$domain_id.'\-\d+.\d+\z/';

		$allSlabs = $memcache->getExtendedStats('slabs');
		foreach($allSlabs as $server => $slabs)
		{
			foreach($slabs as $slabId => $slabMeta)
			{
				if (!is_numeric($slabId)){continue;}

				$cdump = $memcache->getExtendedStats('cachedump',(int)$slabId,0);
				//echo count($cdump);

				foreach($cdump as $keys => $arrVal) 
				{
					if (!is_array($arrVal)) continue;

					//echo count($arrVal);

					foreach($arrVal as $k => $v) 
					{                   
//						echo $k .' - '.date('H:i d.m.Y',$v[1]).' , ';
						$res = preg_match($key_pattern,$k);
						if(!$res) continue;

						$obj = $memcache->get($k);
						if(!$obj) continue;

						// echo $k.' : ';
						// echo 'collect : ';
						// echo $obj->url;
						// echo "\n";
						
						$exist_url[] = $obj->url;
					}
				}
			}
		}
		
		print_r($exist_url);

		$memcache->close();
		echo "memcache closed\n";
	}
	
	public function post_pattern()
	{
		$key_pattern = "/\Apage\-\d+\-\d+.\d+\z/";
		
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		$allSlabs = $memcache->getExtendedStats('slabs');
		foreach($allSlabs as $server => $slabs)
		{
			foreach($slabs as $slabId => $slabMeta)
			{
				if (!is_numeric($slabId)){continue;}

				$cdump = $memcache->getExtendedStats('cachedump',(int)$slabId,0);
				//echo count($cdump);

				foreach($cdump as $keys => $arrVal) 
				{
					if (!is_array($arrVal)) continue;

					//echo count($arrVal);

					foreach($arrVal as $k => $v) 
					{
						echo 'key : '.$k.' : ';
						$res = preg_match($key_pattern,$k);
						if($res) echo "match\n";
						else echo "un-match\n";
						
					}
				}
			}
		}
		
		$memcache->close();
	}
	
	public function collect($id=9)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		$time = new Datetime();
		$time->sub(new DateInterval('PT60S'));
		echo 'time='.$time->format('Y-m-d H:i:s');
		echo "\n";
		echo 'time='.$time->getTimestamp();
		echo "\n";
		
		$list = array();
		$allSlabs = $memcache->getExtendedStats('slabs');
		foreach($allSlabs as $server => $slabs)
		{
			foreach($slabs as $slabId => $slabMeta)
			{
				if (!is_numeric($slabId)){continue;}
				
				$cdump = $memcache->getExtendedStats('cachedump',(int)$slabId,0);
				//echo count($cdump);
				
				foreach($cdump as $keys => $arrVal) 
				{
					if (!is_array($arrVal)) continue;
					
					//echo count($arrVal);
					
					foreach($arrVal as $k => $v) 
					{                   
//						echo $k .' - '.date('H:i d.m.Y',$v[1]).' , ';
						$obj = $memcache->get($k);
						if(!$obj) continue;
						
						echo $k.' : ';
						$key_time = substr($k,5);
						if((float)$key_time >= (float)$time->getTimestamp()) { echo "not yet\n"; continue; }
//						var_dump($obj);
						echo 'delete';
						$memcache->delete($k) or die ("Could not delete");
						echo "\n";
					}
				}
			}
		}
		
		$memcache->close();
	}
	
	public function add_period($duration=600,$interval=10)
	{
		$end = new Datetime();
		$end->add(new DateInterval('PT600S'));
		echo 'end-time='.$end->format('Y-m-d H:i:s');
		echo "\n";
		
		$time = new Datetime();
		
		while($time<=$end)
		{
			unset($time);
			$sleep = rand(0,$interval);
			echo 'sleep='.$sleep.'secs ; ';

			sleep($sleep);

			$key = $this->add();
			echo 'key='.$key;
			echo "\n";
			$time = new Datetime();
		}
	}
	
	public function add($max = 1)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		$res = null;
		
		for($i=0;$i<$max;$i++)
		{
			$obj = new stdClass;
			$obj->timestamp = microtime(true);
			$obj->name = 'temp';
			
			// add obj to memcache
			$key = rand(1000,9999).'-'.$obj->timestamp;
			$memcache->set($key, $obj, false, 12*60*60) or die ("Failed to save OBJECT at the server");
			
			unset($obj);
		}
		
		$memcache->close();
		
		return $key;
	}
	
	public function flush()
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		
		$memcache->flush();
		$memcache->close();
	}
	
	public function getstats()
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
	
		$var = $memcache->getStats();
		$memcache->close();

		print_r ($var);
	}

	public function general()
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");

		$version = $memcache->getVersion();
		echo "Server's version: ".$version."<br/>\n";

		$tmp_object = new stdClass;
		$tmp_object->str_attr = 'key';
		$tmp_object->int_attr = 123;

		echo '.';
		$tmp_object->str_attr .= '1';
		$memcache->add('key1', $tmp_object, false, 10) or die ("Failed to save data at the server");
		echo '.';
		$tmp_object->str_attr .= '2';
		$memcache->add('key2', $tmp_object, false, 10) or die ("Failed to save data at the server");
		echo '.';
		$tmp_object->str_attr .= '3';
		$memcache->add('key3', $tmp_object, false, 10) or die ("Failed to save data at the server");
		echo '.';
		$tmp_object->str_attr .= '4';
		$memcache->add('key4', $tmp_object, false, 10) or die ("Failed to save data at the server");
		echo '.';
		echo "Store data in the cache (data will expire in 10 seconds)<br/>\n";

		$get_result = $memcache->get('key2');
		echo "Data from the cache:<br/>\n";
		var_dump($get_result);
	
		$get_result = $memcache->get('key3');
		echo "Data from the cache:<br/>\n";
		var_dump($get_result);
	
		$get_result = $memcache->get('key2');
		echo "Data from the cache:<br/>\n";
		var_dump($get_result);
		
		$get_result = $memcache->get('key23445');
		echo "Data from the cache:<br/>\n";
		var_dump($get_result);
	
		$memcache->close();
	}
}
?>