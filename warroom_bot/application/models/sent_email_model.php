<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sent_email_model extends CI_Model{
    
    function __constuctor()
	{
		// Call the Model constructor
		parent::__construct();
	}
    
    
    function send(){
           
                     
                    $config = Array(
                        'protocol' => 'smtp',
                        'smtp_host' => 'ssl://smtp.googlemail.com',
                        'smtp_port' => 465,
                        'smtp_user' => 'sumet@thothmedia.com',
                        'smtp_pass' => 'mote123456!',
                        'mailtype'  => 'html',
                        'charset'   => 'utf-8'
                    );
                           
                        $this->load->library('email', $config);
                        $this->email->set_newline("\r\n");                
                           
                        /*$name   =        $this->input->post("name");
                        $org    =        $this->input->post("organization");
                        $email  =        $this->input->post("email");
                        $tel    =        $this->input->post("tel");
                        $desc   =        $this->input->post("desc");*/
                           
                        $date = date("d-m-Y H:i:s");
                           
                        $message = "Test Email";
                           
                        $this->email->from('thothconnect.com','thothconnect.com');
                        $this->email->to('kanonkung@gmail.com');  //info@thothmedia.com
        
                        $this->email->subject('[do-not-reply] contact from thothconnect.com');
                        $this->email->message($message);
                           
                        $this->email->send();
                           
            
                   
                        
    }  
}
?>