<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['fetch_file_path']	= './tmp_files/';

$config['monitors'] = array(
	array(
		'name'		=> '[Twitter] Harvester',
		'pattern'	=> '/harvester.py',
		'threshold'	=> 1 // Notify if matches < threshold
	),
	array(
		'name'		=> '[Twitter] Reach Calculator',
		'pattern'	=> 'reach_calculator.py',
		'threshold'	=> 2 // Notify if matches < threshold
	),
	array(
		'name'		=> '[Facebook] Page Harvester',
		'pattern'	=> 'page_harvester.py',
		'threshold'	=> 4 // Notify if matches < threshold
	),
	array(
		'name'		=> '[Facebook] Post Harvester',
		'pattern'	=> 'post_harvester.py',
		'threshold'	=> 2 // Notify if matches < threshold
	),
	array(
		'name'		=> '[Spider] Parser',
		'pattern'	=> 'php',
		'threshold'	=> 0 // Notify if matches < threshold
	)
);

?>
