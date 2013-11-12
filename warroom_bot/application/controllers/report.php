<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Report extends CI_Controller {
	
	public function index()
	{
		$submit = $this->input->post('submit');
		if($submit)
		{
			$title = $this->input->post('title');
			if($title == 'devider')
			{
				echo 'bad report title';
				exit;
			}
			
			$option['type'] = $this->input->post('view_type');
			$option['client'] = $this->input->post('client');
			$option['month'] = $this->input->post('month');
			$option['week'] = $this->input->post('week');
			$option['year'] = date('Y');
			$option['from_date'] = null;
			$option['to_date'] = null;
			
			$this->title($title,$option);
		}
		else
		{
			$report_name = "ThothConnect Report";
			$report_title = "ThothConnect Report System";

			$data["report_name"] = $report_name;
			$data["report_title"] = $report_title;

			// Load to view
			$data["content"] = $this->load->view("report/index",null,true);
			$this->load->view('report/template_index',$data);
		}
	}
	
	public function title($report_name,$option=null)
	{
		// Reset PHP Timeout to 20min
		set_time_limit(20*60);
		
		// report title
		//$report_name = "vol_comment_brand";
		
		// test client = Samsung
		if($option['client']==0) $client_id = 7;
		else $client_id = $option['client'];
		$month = $option['month'];
		$year = $option['year'];
		
		// get date period
		if($month==null || $month < 1 || $month > 12) $month = date('n');
		if($year==null || $year < 2012 || $year > date('Y')) $year = date('Y');
		
		// get client
		if($client_id==null)
		{
			echo "invalid client";
			return false;
		}
		else
		{
			$query = $this->db->get_where('clients',array('client_id'=>$client_id));
			$client_name = $query->row()->client_name;
		}
		
		// Call Helpers
		$this->load->helper('/report/'.$report_name);
		$data = $report_name($month,$year,$client_id,$option);
		$data["client_name"] = $client_name;
		$data["from_date"] = date('j F Y',mktime(0,0,0,$month,1,$year));
		$days_in_month = cal_days_in_month(CAL_GREGORIAN,$month,$year);
		$data["to_date"] = date('j F Y',mktime(0,0,0,$month,$days_in_month,$year));
		$data["days"] = $days_in_month;
		$data["month"] = $month;
		$data["year"] = $year;
		
		$data["option"] = $option;
		
		// Load to view
		$data["content"] = $this->load->view("report/".$report_name,$data,true);
		$this->load->view('report/template',$data);
	}
}
