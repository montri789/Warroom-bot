<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Words
{
	var $skip=array();
	var $emo=array();
	var $amp=array();
	var $neg=array();
	var $ign=array();
	
	protected $_ci;
	
	public function __construct()
    {
        $this->_ci =& get_instance();
		$this->_ci->load->database();

		$query = $this->_ci->db->get('words');
		foreach($query->result() as $p)
		{
			switch ($p->type) {
				case 'emo':
					$this->emo[]=$p;
					break;
				
				case 'amp':
					$this->amp[]=$p;
					break;
					
				case 'skip':
					$this->skip[]=$p;
					break;
					
				case 'neg':
					$this->neg[]=$p;
					break;
					
				case 'ign':
					$this->ign[]=$p;
					break;
			}
		}
    }
}

?>