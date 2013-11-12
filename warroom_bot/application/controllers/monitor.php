<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
class Monitor extends CI_Controller {

	function Index()
	{
		$data['results'] = array();
		
		$monitors = $this->config->item('monitors');
		foreach ($monitors as $monitor)
		{
			$response = shell_exec("ps -aef | grep -v grep | grep " . $monitor['pattern']);
			$count = count(explode("\n", trim($response)));
			$error = $count < $monitor['threshold'];
			
			if ($error)
			{
				$this->_notify($monitor);
			}
			
			$data['results'][] = array(
				'name'			=> $monitor['name'],
				'pattern'		=> $monitor['pattern'],
				'threshold'		=> $monitor['threshold'],
				'response'		=> $response,
				'count'			=> $count,
				'error'			=> $error
			);	
		}
		
		// var_dump($data['results']);
		$this->load->view('monitor', $data);
	}
	
	function _notify($monitor)
	{
		# Email admin
	}

}
// End File Monitor.php
// File Source /system/application/controllers/Monitor.php