<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Collector extends CI_Controller {
	
	public function single($type=null,$skip=null)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		echo "memcache activated\n";
		
		$key_pattern = null;
		if($type == 'page') $key_pattern = "/\Apage\-\d+\-\d+.\d+\z/";
		if($type == 'post') $key_pattern = "/\A\d\d\d\d-\d+.\d+\z/";
		if($key_pattern == null) { echo "Type : post || page\n"; return null;}
		
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

						echo $k.' : ';
						if($skip!=null && $skip == $k) echo 'skip : ';
						else if(preg_match('/\Apage\-83\-\d+.\d+\z/',$k)) echo 'skip 83 : ';
						else
						{
							echo 'collect : ';
							$res = $this->db->insert($type,(array)$obj);
						}
						if($res)
						{
							echo "OK!";
							$memcache->delete($k) or die ("Could not delete : $k\n");
						}
						
						echo "\n";
					}
				}
			}
		}
		
		$memcache->close();
		echo "memcache closed\n";
	}
	
	public function page($interval=60)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		echo "memcache activated\n";

		echo "collect past = $interval seconds\n";

		$time = new Datetime();
		$time->sub(new DateInterval('PT'.$interval.'S'));
		echo 'time='.$time->format('Y-m-d H:i:s');
		echo "\n";
		echo 'time='.$time->getTimestamp();
		echo "\n";

		$key_pattern = "/\Apage\-\d+\-\d+.\d+\z/";

		$lists = array();
		$lists_key = array();
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
						$res = preg_match($key_pattern,$k);
						if(!$res) continue;

						$obj = $memcache->get($k);
						if(!$obj) continue;

						echo $k.' : ';
						$k_explode = explode('-',$k);
						$key_time = $k_explode[2];
						if((float)$key_time >= (float)$time->getTimestamp()) { echo "not yet\n"; continue; }
						$lists_key[] = $k;
						$lists[] = (array)$obj;
						echo 'collect : ';
						echo "\n";
					}
				}
			}
		}

		echo count($lists).' collected';
		echo "\n";
		if(count($lists))
		{
			echo "batch insert : ";
			$res = $this->db->insert_batch('page',$lists);
		}
		
		if(!$res) { echo " FAILED\n"; }
		else
		{
			echo " SECCESS\n";
			echo "Delete Key im memcache : \n";
			foreach($lists_key as $lk)
			{
				$memcache->delete($lk) or die ("Could not delete : $k\n");
			}
			echo "SUCCESS\n";
		}

		$memcache->close();
		echo "memcache closed";
	}
	
	public function post($interval=600)
	{
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect");
		echo "memcache activated\n";
		
		echo "collect past = $interval seconds\n";
		
		$time = new Datetime();
		$time->sub(new DateInterval('PT'.$interval.'S'));
		echo 'time='.$time->format('Y-m-d H:i:s');
		echo "\n";
		echo 'time='.$time->getTimestamp();
		echo "\n";
		
		$key_pattern = "/\A\d\d\d\d-\d+.\d+\z/";
		
		$posts = array();
		$lists_key = array();
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
						
						echo $k.' : ';
						$key_time = substr($k,5);
						if((float)$key_time >= (float)$time->getTimestamp()) { echo "not yet\n"; continue; }
						$lists_key[] = $k;
						$posts[] = (array)$obj;
						echo 'collect : ';
						echo "\n";
					}
				}
			}
		}
		
		echo count($posts).' collected';
		echo "\n";
		if(count($posts))
		{
			echo "batch insert : ";
			$res = $this->db->insert_batch('post',$posts);
		}
		
		if(!$res) { echo " FAILED\n"; }
		else
		{
			echo " SECCESS\n";
			echo "Delete Key im memcache : ";
			foreach($lists_key as $lk)
			{
				$memcache->delete($lk) or die ("Could not delete : $k\n");
			}
			echo "SUCCESS\n";
		}
		
		$memcache->close();
		echo "memcache closed\n";
	}
}
?>