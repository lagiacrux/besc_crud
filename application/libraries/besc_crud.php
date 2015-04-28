<?php defined('BASEPATH') OR exit('No direct script access allowed');

define('BC_ADD', 0);
define('BC_EDIT', 1);

class besc_crud 
{
	protected $ci = null;
	
	protected $db_table = "";
	protected $db_primary_key = "";
	protected $db_columns = array();
	
	protected $states = array('list', 'add', 'insert', 'edit', 'update', 'delete', 'refresh_list');
	protected $state_info = array();
	protected $base_url = "";
	
	protected $title = "";
	protected $hidden_action = array();
	
	protected $custom_actions = array();
	protected $custom_buttons = array();
	
	
	function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->model('besc_crud_model', 'bc_model');
	}	
	
	
	public function table($table = null)
	{
		$this->db_table = $table != null ? $table : $this->db_table;
		return $this->db_table;
	}
	
	public function primary_key($pk = null)
	{
		$this->db_primary_key = $pk != null ? $pk : $this->db_primary_key;
		return $this->db_primary_key;
	}	
	
	public function columns($columns = array())
	{
		$this->db_columns = $columns != array() ? $columns : $this->db_columns;
		return $this->db_columns;
	}
	
	public function title($title = null)
	{
		$this->title = $title != array() ? $title : $this->title;
		return $this->title;
	}
	
	public function hide_action($actions = array())
	{
		$this->hidden_action = $actions != array() ? $actions : $this->hidden_action;
		return $this->hidden_action;
	}
	
	public function base_url($url = null)
	{
		$this->base_url = $url != null ? $url : $this->base_url;
		return $this->base_url;		
	}
	
	
	
	
	
	protected function get_state_info_from_url()
	{
		$segment_position = count($this->ci->uri->segments) + 1;
		$operation = 'list';
	
		$segements = $this->ci->uri->segments;
		foreach($segements as $num => $value)
		{
			if($value != 'unknown' && in_array($value, $this->states))
			{
				$segment_position = (int)$num;
				$operation = $value; //I don't have a "break" here because I want to ensure that is the LAST segment with name that is in the array.
			}
		}
	
		$function_name = $this->ci->router->method;
	
		if($function_name == 'index' && !in_array('index',$this->ci->uri->segments))
			$segment_position++;
	
		$first_parameter = isset($segements[$segment_position+1]) ? $segements[$segment_position+1] : null;
		$second_parameter = isset($segements[$segment_position+2]) ? $segements[$segment_position+2] : null;
	
		return (object)array('segment_position' => $segment_position, 'operation' => $operation, 'first_parameter' => $first_parameter, 'second_parameter' => $second_parameter);
	}
	
	protected function get_urls()
	{
		switch($this->state_info->operation)
		{
			case 'list':
				return array(	'bc_delete_url' => $this->base_url . 'delete/',
								'bc_list_url' => $this->base_url,
								'bc_refresh_url' => $this->base_url. 'refresh_list/'
					  		);
				break;
			case 'add':
			
				return array(	'bc_insert_url' => $this->base_url . 'insert',
								'bc_list_url' => $this->base_url
					  		);
			case 'edit':
				return array(	'bc_edit_url' => $this->base_url . 'update/',
								'bc_list_url' => $this->base_url
				);
				break;
			default:
				return array();
		}
	}
	
	protected function die_with_error($message = 'ERROR!!!')
	{
		echo json_encode(array('success' => false,
							   'message' => $message));
	}
	
	protected function get_base_url()
	{
		$i = 1;
		$url = site_url();
		while($i < $this->state_info->segment_position)
		{
			$url .= $this->ci->uri->segment($i). '/';
			$i++;
		}
		return $url;
	}
	
	
	protected function prepare()
	{
		$this->state_info = $this->get_state_info_from_url();
		$this->base_url = $this->get_base_url();
	}
	
	public function execute()
	{
		$this->prepare();
		
		switch($this->state_info->operation)
		{
			case 'list':
				return $this->render_list();
				break;

			case 'delete':
				$this->delete();
				break;
				
			case 'refresh_list':
				return $this->render_list(true);
				break;
				
			case 'add':
				return $this->render_edit();
				break;

			case 'edit':
				return $this->render_edit();
				break;	
				
			case 'insert':
				$this->insert();
				break;
				
			case 'update':
				$this->update();
				break;
		}
		die();
		//return true;
	}
	
	
	protected function delete()
	{
		if($this->ci->bc_model->delete($this->db_table, $this->db_primary_key, $this->state_info->first_parameter) > 0)
		{
			$result['success'] = true;
			$result['message'] = "Dataset deleted.";
		}
		else
		{
			$result['success'] = false;
			$result['message'] = "Error while deleting dataset";
		}
	
		echo json_encode($result);
	}
	
	protected function insert()
	{
		$content = json_decode(file_get_contents('php://input'));
		$col = array();
		foreach($content as $column)
		{
			$col[$column->name] = $column->value;
		}
		
		if($this->ci->bc_model->insert($this->db_table, $col) > 0)
		{
			$result['success'] = true;
			$result['message'] = $this->title . ' successfully inserted.';
		}
		else
		{
			$result['success'] = false;
			$result['message'] = 'Error while inserting ' . $this->title . '.';			
		}		
		
		echo json_encode($result);
	}
	
	protected function update()
	{
		$content = json_decode(file_get_contents('php://input'));
		$col = array();
		$col_after = array();
		
		foreach($content as $column)
		{
			switch($column->type)
			{
				case 'text':
				case 'hidden':
				case 'image':
				case 'multiline':
				case 'select':
					$col[$column->name] = $column->value;
					break;
				case 'm_n_relation':
					$col_after[] = $column;
					break;
				case 'url':
				    $col[$column->name] = prep_url($column->value);
				    break;
			}
		}
		
		if($this->ci->bc_model->update($this->db_table, $this->db_primary_key, $this->state_info->first_parameter, $col))
		{
		    $after_success = true;
			foreach($col_after as $col)
			{
			    switch($col->type)
				{
					case 'm_n_relation':
						$after_success = $this->saveMNRelation($col, true);
						break;
				}
				echo $after_success;
			}
			
			if($after_success)
			{
			    $result['success'] = true;
			    $result['message'] = $this->title . ' successfully updated.';
			}
			else
			{
			    $result['success'] = false;
			    $result['message'] = 'Error while updating ' . $this->title . '.';
			}
			
		}
		else
		{
		    $result['success'] = false;
		    $result['message'] = 'Error while updating ' . $this->title . '.';
		}
		
		echo json_encode($result);		
	}
	
	
	protected function saveMNRelation($column, $delete = false)
	{
		$m = null;
		foreach($this->db_columns as $c)
		{
			if(isset($c['relation_id']) && $c['relation_id'] == $column->relation_id)
			{
				$m = $c;
			}
		}
		if($m != null)
		{
			if($delete)
			{
				$this->ci->bc_model->delete_m_n_relationByID($m['table_mn'], $m['table_mn_col_m'], $this->state_info->first_parameter);
			}
			$batch = array();
			foreach($column->selected as $sel)
			{
				$batch[] = array($m['table_mn_col_m'] => $this->state_info->first_parameter, $m['table_mn_col_n'] => $sel);
			}
			return $this->ci->bc_model->insert_m_n_relation($m['table_mn'], $batch);
		}
		else
			return false;
	}
	

	
	protected function render_list($ajax = false)
	{
		foreach($this->db_columns as $column)
		{
			$data['headers'][] = $column['display_as'];
		}
		
		$get = $this->ci->bc_model->get($this->db_table);
		
		$rows = array();
		
		foreach($get->result_array() as $row)
		{
			$columns = array();
			$columns['pk'] = $row[$this->db_primary_key];
			foreach($this->db_columns as $column)
			{
				switch($column['type'])
				{
					case 'hidden':
					case 'text':
						$columns[$column['db_name']] = $this->list_text($row, $column);
						break;
					case 'image':
						$columns[$column['db_name']] = $this->list_image($row, $column);
						break;
					case 'm_n_relation':
						$columns['m_n_relation'] = $this->list_m_n_relation($row, $column);
						break;
					case 'url':
					    $columns[$column['db_name']] = $this->list_url($row, $column);
					    break;
					case 'select':
					    $columns[$column['db_name']] = $this->list_select($row, $column);
					    break;
				}
			}
			$rows[] = $columns;
		}
		$data['rows'] = $rows;
		$data['title'] = $this->title;
		$data['hide_add'] = (isset($this->hidden_action['hide_add'])) ? true : false;
		$data['custom_button'] = $this->custom_buttons;
		$data['custom_action'] = $this->custom_actions;
		$data['bc_urls'] =  $this->get_urls();
		$data['ajax'] = $ajax;
			
		if(!$ajax)
		{
			
			return $this->ci->load->view('besc_crud/table_view', $data, true);		
		}
		else
		{
			echo json_encode(array(	'success' => true,
							 		'data' => $this->ci->load->view('besc_crud/table_view', $data, true)));
		}
	}
	
	protected function render_edit()
	{
		$columns = array();
		if($this->state_info->first_parameter != null)
		{
			$get = $this->ci->bc_model->getByID($this->db_table, $this->db_primary_key, $this->state_info->first_parameter);
			if($get->num_rows() != 1)
				$this->die_with_error("key not unique!");
			else 
				$get = $get->row_array();
			
			$data['pk_value'] = $this->state_info->first_parameter;	
		}
		
		$i = 0;
		foreach($this->db_columns as $col)
		{
			$col['num_row'] = $i;
			if(!isset($col['col_info']))
				$col['col_info'] = "";

			if($this->state_info->first_parameter != null && $col['type'] != 'm_n_relation')
				$col['value'] = $get[$col['db_name']];
				
			switch($col['type'])
			{
				case 'select':
					$columns[$i] = $this->edit_select($col);
					break;
				case 'text':
					$columns[$i] = $this->edit_text($col);
					break;
				case 'multiline':
					$columns[$i] = $this->edit_multiline($col);
					break;
				case 'hidden':
					$columns[($i *-1)-2] = $this->edit_hidden($col);
					break;
				case 'image':
					$columns[$i] = $this->edit_image($col);
					break;
				case 'm_n_relation':
					$columns[$i] = $this->edit_m_n_relation($col);
					break;
				case 'url':
				    $columns[$i] = $this->edit_url($col);
				    break;
			}
			$i++;
		}

		ksort($columns, SORT_NUMERIC);
		
		$data['columns'] = $columns;
		$data['title'] = $this->title;
		if($this->state_info->first_parameter != null)
			$data['edit_or_add'] = BC_EDIT;
		else
			$data['edit_or_add'] = BC_ADD;
		
		$data['bc_urls'] = $this->get_urls();
		
		
		return $this->ci->load->view('besc_crud/edit_view', $data, true);
	}
	
	
	protected function list_text($row, $column)
	{
		$dummy = array('text' => $row[$column['db_name']]);
		return $this->ci->load->view('besc_crud/table_elements/text', $dummy, true);
	}
	
	protected function list_select($row, $column)
	{
	    $dummy = array('options' => $column['options'],
	                   'value' => $row[$column['db_name']] );
	    return $this->ci->load->view('besc_crud/table_elements/select', $dummy, true);
	}	
	
	protected function list_image($row, $column)
	{
		$dummy = array( 'uploadpath' => $column['uploadpath'],
                        'filename' => $row[$column['db_name']]);
		return $this->ci->load->view('besc_crud/table_elements/image', $dummy, true);
	}
	
	protected function list_m_n_relation($row, $column)
	{
		$dummy['n_values'] = $this->ci->bc_model->get_m_n_relation($column['table_mn'], $column['table_mn_col_m'], $column['table_mn_col_n'], $row[$this->db_primary_key], $column['table_n'], $column['table_n_value'], $column['table_n_pk']);
		return $this->ci->load->view('besc_crud/table_elements/m_n_relation', $dummy, true);
	}	
	
	protected function list_url($row, $column)
	{
	    $dummy = array('url' => $row[$column['db_name']]);
	    return $this->ci->load->view('besc_crud/table_elements/url', $dummy, true);
	}	
	
	protected function edit_text($col)
	{
		return $this->ci->load->view('besc_crud/edit_elements/text', $col, true);
	}
	
	protected function edit_hidden($col)
	{
		return $this->ci->load->view('besc_crud/edit_elements/hidden', $col, true);
	}	
	
	protected function edit_image($col)
	{
		if(substr($col['uploadpath'], -1) != '/')
			$col['uploadpath'] .= '/';
		
		return $this->ci->load->view('v/edit_elements/image', $col, true);
	}

	protected function edit_select($col)
	{
		return $this->ci->load->view('besc_crud/edit_elements/select', $col, true);
	}
	
	protected function edit_multiline()
	{
		if(!isset($col['formatting']))
			$col['formatting'] = array();
		
		return $this->ci->load->view('besc_crud/edit_elements/multiline', $col, true);	
	}
	
	protected function edit_m_n_relation($col)
	{
		$col['selected'] = $this->ci->bc_model->get_m_n_relation_m_values($col['table_mn'], $col['table_mn_col_m'], $this->state_info->first_parameter, $col['table_n'], $col['table_n_pk'], $col['table_mn_col_n'], $col['table_n_value']);
		$selected = array();
		foreach($col['selected']->result() as $sel)
		{
			$selected[] = $sel->$col['table_mn_col_n'];
		}
		if(count($selected) <= 0)
		    $selected[] = -1;
		
		$col['avail'] = $this->ci->bc_model->get_m_n_relation_n_values($col['table_n'], $col['table_n_pk'], $selected);

		return $this->ci->load->view('besc_crud/edit_elements/m_n_relation', $col, true);
	}	
	
	protected function edit_url($col)
	{
	    return $this->ci->load->view('besc_crud/edit_elements/url', $col, true);
	}	
}