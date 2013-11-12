<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Custom_model extends CI_Model
{
	function get_value($table,$field,$id)
	{
		$this->db->where('id',$id);
		$this->db->select($field);
		$query = $this->db->get($table);

		if ($query->result()) return $query->row()->$field;
		$query->free_result();
	}
	function get_multi_value($table,$field,$id)
	{
		$this->db->where('id',$id);
		$this->db->select($field);
		$query = $this->db->get($table);

		if ($query->result()) return $query->row();
		$query->free_result();
	}
	function list_table($table,$fields=null,$order=null,$limit=null,$offset=null)
	{	
		if($order != null) $this->db->order_by($order, "asc");
		$query = $this->db->get_where($table,$fields,$limit,$offset);
		if($query->num_rows())
		{
			foreach ($query->result() as $row)
			{
				$res[] = $row;
			}
			$query->free_result();
			return $res;
		}else
		{
			$query->free_result();
			return 0;
		}
	}
}
?>