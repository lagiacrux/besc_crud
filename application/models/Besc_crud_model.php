<?php

class Besc_crud_model extends CI_Model  
{
	public function get($table, $where, $limit, $offset, $filter_select, $filter_text, $order_by_col, $order_by_direction)
	{
	    if($where != '')
            $this->db->where($where);
	    
	    if($filter_select != array())
	        $this->db->where($filter_select);
	    
	    if($filter_text != array())
	        $this->db->like($filter_text);
	    
	    if($order_by_col != '' && $order_by_direction != '')
	        $this->db->order_by($order_by_col, $order_by_direction);
	    
		return $this->db->get($table, $limit, $offset);
	}
	
	public function get_ordering($table, $where, $ordering)
	{
	    if($where != '')
	        $this->db->where($where);
	    
	    $this->db->order_by($ordering, 'asc');
	    
	    return $this->db->get($table);
	}
	
	public function get_total($table, $where, $filter_select, $filter_text)
	{
	    if($where != '')
	        $this->db->where($where);
	    
	    if($filter_select != array())
	        $this->db->where($filter_select);
	    
	    if($filter_text != array())
	        $this->db->like($filter_text);
	    	    
	    return $this->db->get($table);	    
	}
	
	public function insert($table, $data)
	{
		$this->db->insert($table, $data);
		return $this->db->insert_id();	
	}
	
	public function delete($table, $pk_column, $pk_value)
	{
	    $this->db->trans_start();
		$this->db->where($pk_column, $pk_value);
		$this->db->delete($table);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
		    return false;
	    else
	        return true;
	}
	
	public function getByID($table, $pk_column, $pk_value)
	{
		$this->db->where($pk_column, $pk_value);
		return $this->db->get($table);
	}
	
	public function update($table, $pk_column, $pk_value, $data)
	{
	    $this->db->trans_start();
		$this->db->where($pk_column, $pk_value);
		$this->db->update($table, $data);
		$this->db->trans_complete();
		if( $this->db->affected_rows() == 1)
		    return true;
	    else
	        if ($this->db->trans_status() === FALSE)
	            return false;
	        else
	            return true;
	}	
	
	
	
	public function get_m_n_relation($table_mn, $table_mn_col_m, $table_mn_col_n, $table_m_value, $table_n, $table_n_value, $table_n_pk)
	{
		$this->db->select($table_n . '.' . $table_n_value);
		$this->db->from($table_mn);
		$this->db->join($table_n, $table_n . '.' . $table_n_pk . '=' . $table_mn . '.' . $table_mn_col_n);
		$this->db->where($table_mn . '.' . $table_mn_col_m, $table_m_value);
		return $this->db->get();
	}
	
	public function get_m_n_relation_m_values($table_mn, $table_mn_col_m, $table_m_value, $table_n, $table_n_pk, $table_mn_col_n, $table_n_value)
	{
		$this->db->select("$table_mn.$table_mn_col_n, $table_n.$table_n_value");
		$this->db->where($table_mn_col_m, $table_m_value);
		$this->db->join($table_n, $table_n . '.' . $table_n_pk .'=' . $table_mn . '.' . $table_mn_col_n);
		$this->db->order_by("$table_n.$table_n_value", 'asc');
		return $this->db->get($table_mn);
	}
	
	public function get_m_n_relation_n_values($table_n, $table_n_pk, $already_selected, $table_n_value)
	{
		$this->db->where_not_in($table_n_pk, $already_selected);
		$this->db->order_by($table_n_value, 'asc');
		return $this->db->get($table_n);
	}
	
	public function delete_m_n_relationByID($table_mn, $table_mn_col_m, $table_mn_pk)
	{
	    $this->db->trans_start();
		$this->db->where($table_mn_col_m, $table_mn_pk);
		$this->db->delete($table_mn);
		$this->db->trans_complete();
		return $this->db->trans_status() === false ? false : true; 
	}
	
	public function insert_m_n_relation($table_mn, $batchdata)
	{
	    $this->db->trans_start();
	    $this->db->insert_batch($table_mn, $batchdata);
	    $this->db->trans_complete();
	    return $this->db->trans_status() === false ? false : true;
	}
	
	
	public function get_image_gallery_items($table, $fk, $key)
	{
	    $this->db->trans_start();
	    $this->db->where($fk, $key);
	    $get = $this->db->get($table);
	    $this->db->trans_complete();
	    if($this->db->trans_status() === false)
	        return false;
	    else 
	        return $get;
	}
	
	function save_ordering($table, $batchdata, $key)
	{
	    $this->db->trans_start();
	    $this->db->update_batch($table, $batchdata, $key);
	    $this->db->trans_complete();
	    return $this->db->trans_status() === false ? false : true;
	}
}
?>