<?php defined('BASEPATH') OR exit('No direct script access allowed');

define('BC_ADD', 0);
define('BC_EDIT', 1);

class Besc_crud 
{
	protected $ci = null;
	
	protected $db_table = "";
	protected $db_primary_key = "";
	protected $db_columns = array();
	protected $db_where = "";
	
	protected $list_columns = array();
	
	protected $states = array('list', 'add', 'insert', 'edit', 'update', 'delete', 'refresh_list', 'imageupload');
	protected $state_info = array();
	protected $base_url = "";
	
	protected $title = "";
	
	protected $custom_actions = array();
	protected $custom_buttons = array();
	
	protected $allow_add = true;
	protected $allow_delete = true;
	protected $allow_edit = true;
	
	protected $custom_upload = null;
	
	
	function __construct()
	{
		$this->ci = &get_instance();
		$this->ci->load->model('Besc_crud_model', 'bc_model');
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
	
	public function list_columns($list_columns = array())
	{
	    $this->list_columns = $list_columns != array() ? $list_columns : $this->list_columns;
	    return $this->list_columns;
	}
	
	public function title($title = null)
	{
		$this->title = $title != array() ? $title : $this->title;
		return $this->title;
	}
	
	public function base_url($url = null)
	{
		$this->base_url = $url != null ? $url : $this->base_url;
		return $this->base_url;		
	}
	
	public function custom_actions($custom_actions = null)
	{
	    $this->custom_actions = $custom_actions != null ? $custom_actions : $this->custom_actions;
	    return $this->custom_actions;	    
	}
	
	public function custom_buttons($custom_buttons = null)
	{
	    $this->custom_buttons = $custom_buttons != null ? $custom_buttons : $this->custom_buttons;
	    return $this->custom_buttons;
	}
	
	public function where($where_string = "")
	{
	    $this->db_where = $where_string != "" ? $where_string : $this->db_where;
	    return $this->db_where;	    
	}
	
	public function unset_add()
	{
	    $this->allow_add = false;
	    return $this->allow_add;
	}

	public function unset_edit()
	{
	    $this->allow_edit = false;
	    return $this->allow_edit;
	}
	
	public function unset_delete()
	{
	    $this->allow_delete = false;
	    return $this->allow_delete;
	}
	
	public function custom_upload($custom_upload = null) 
	{
	    $this->custom_upload = $custom_upload != null ? $custom_upload : $this->custom_upload;
	    return $this->custom_upload;
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
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_refresh_url' => $this->base_url . 'refresh_list/'
					  		);
				break;
			case 'add':
			
				return array(	'bc_insert_url' => $this->base_url . 'insert',
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_upload_url' => $this->base_url . 'imageupload'
					  		);
			case 'edit':
				return array(	'bc_edit_url' => $this->base_url . 'update/',
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_upload_url' => $this->base_url . 'imageupload'
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
		die();
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
			    if($this->allow_delete)
				    $this->delete();
				break;
				
			case 'refresh_list':
				$this->render_list(true);
				break;
				
			case 'add':
			    if($this->allow_add)
				    return $this->render_edit();
				break;

			case 'edit':
			    if($this->allow_edit)
			        return $this->render_edit();
				break;	
				
			case 'insert':
			    if($this->allow_add)
				    $this->insert();
				break;
				
			case 'update':
			    if($this->allow_edit)
				    $this->update();
				break;
				
			case 'imageupload':
			    $this->imageupload();
			    break;
		}
		die();

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
			    case 'date':
			        $col[$column->name] = date('Y-m-d', strtotime($column->value));
			        break;
			}
		}
		$new_id = $this->ci->bc_model->insert($this->db_table, $col);
		if($new_id > 0)
		{
		    $after_success = true;
		    
		    foreach($col_after as $col)
		    {
		        switch($col->type)
		        {
		            case 'm_n_relation':
		                $after_success = $this->saveMNRelation($col, true, $new_id);
		                break;
		        }
		    }
		    
			if($after_success)
			{
			    $result['success'] = true;
			    $result['message'] = $this->title . ' successfully added.';
			}
			else
			{
			    $result['success'] = false;
			    $result['message'] = 'Error while adding ' . $this->title . '.';
			}
		}
		else
		{
			$result['success'] = false;
			$result['message'] = 'Error while adding ' . $this->title . '.';			
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
			    case 'date':
			        $col[$column->name] = date('Y-m-d H:i:s', strtotime($column->value));
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
	
	
	protected function imageupload()
	{
	    $filename = $_POST['filename'];
	    $col_name = $_POST['element'];
	    
	    if(isset($this->custom_upload[$col_name]))
	    {
	        $serverFile = call_user_func($this->custom_upload[$col_name], $this->db_columns[$col_name]['uploadpath']);
	    }   
	    else
	    {
	        $upload_path = $this->db_columns[$col_name]['uploadpath'];
	        
	        if(substr($upload_path, -1) != '/')
	            $upload_path .= '/';
	        
	        $rnd = $this->rand_string(12);
	        $data = explode(',', $_POST['data']);
	        
	        $ext = pathinfo($filename, PATHINFO_EXTENSION);
	        
	        $serverFile = time() . "_" . $rnd . "." . $ext;
	        $fp = fopen(getcwd() . '/' . $upload_path . $serverFile, 'w');
	         
	        fwrite($fp, base64_decode($data[1]));
	        fclose($fp);
	    } 

        echo json_encode(array('success' => true,
                               'filename' => $serverFile));
	}
	
	function rand_string($length)
	{
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str = "";
	    $str = base64_encode(openssl_random_pseudo_bytes($length, $strong));
	    $str = substr($str, 0, $length);
	    $str = preg_replace("/[^a-zA-Z0-9\s]/", "", $str);
	    return $str;
	}
	
	
	public function getFullUrl() 
	{
	    
	    return
	    (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
	    (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
	    (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
	        (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
	            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
	            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}
	
	
	protected function saveMNRelation($column, $delete = false, $pk = null)
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
				$this->ci->bc_model->delete_m_n_relationByID($m['table_mn'], $m['table_mn_col_m'], $pk == null ? $this->state_info->first_parameter : $pk);
			}
			$batch = array();
			foreach($column->selected as $sel)
			{
				$batch[] = array($m['table_mn_col_m'] => $pk == null ? $this->state_info->first_parameter : $pk, $m['table_mn_col_n'] => $sel);
			}
			if(count($batch) > 0)
                return $this->ci->bc_model->insert_m_n_relation($m['table_mn'], $batch);
			else 
			    return true;
		}
		else
			return false;
	}
	

	
	protected function render_list($ajax = false)
	{
		foreach($this->db_columns as $key => $column)
		{
		    $show_in_list = true;
		    if($this->list_columns != array())
		        $show_in_list = in_array($key, $this->list_columns);
		    
		    if($show_in_list)
		        $data['headers'][] = isset($column['display_as']) ? $column['display_as'] : $key;
		        
		}
		
		$get = $this->ci->bc_model->get($this->db_table, $this->db_where);
		
		$rows = array();
		
		foreach($get->result_array() as $row)
		{
			$columns = array();
			$columns['pk'] = $row[$this->db_primary_key];
			foreach($this->db_columns as $key => $column)
			{
			    $show_in_list = true;
			    if($this->list_columns != array())
			    {
			        $show_in_list = in_array($key, $this->list_columns);
			    }
			    
			    if($show_in_list)
			    {
    				switch($column['type'])
    				{
    					case 'hidden':
    					case 'text':
    						$columns[$key] = $this->list_text($row, $column);
    						break;
    					case 'image':
    						$columns[$key] = $this->list_image($row, $column);
    						break;
    					case 'm_n_relation':
    						$columns[$key] = $this->list_m_n_relation($row, $column);
    						break;
    					case 'url':
    					    $columns[$key] = $this->list_url($row, $column);
    					    break;
    					case 'select':
    					    $columns[$key] = $this->list_select($row, $column);
    					    break;
    					case 'image_gallery':
    					    $columns[$key] = $this->list_image_gallery($row, $column);
    					    break;
    					case 'multiline':
    					    $columns[$key] = $this->list_multiline($row, $column);
    					    break;
    					case 'date':
    					    $columns[$key] = $this->list_date($row, $column);
    					    break;
    				}
			    }
			}
			$rows[] = $columns;
		}
		$data['rows'] = $rows;
		$data['title'] = $this->title;
		$data['allow_add'] = $this->allow_add;
		$data['allow_edit'] = $this->allow_edit;
		$data['allow_delete'] = $this->allow_delete;
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
		foreach($this->db_columns as $key => $col)
		{
			$col['num_row'] = $i;
			if(!isset($col['col_info']))
				$col['col_info'] = "";
			
			

			if($this->state_info->first_parameter != null && $col['type'] != 'm_n_relation' && $col['type'] != 'image_gallery')
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
				    if(!isset($col['js_callback_after_upload']))
				        $col['js_callback_after_upload'] = "";
					$columns[$i] = $this->edit_image($col);
					break;
				case 'm_n_relation':
					$columns[$i] = $this->edit_m_n_relation($col);
					break;
				case 'url':
				    $columns[$i] = $this->edit_url($col);
				    break;
				case 'date':
				    $columns[$i] = $this->edit_date($col);
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
		$dummy['table_n_value'] = $column['table_n_value'];
		return $this->ci->load->view('besc_crud/table_elements/m_n_relation', $dummy, true);
	}	
	
	protected function list_url($row, $column)
	{
	    $dummy = array('url' => $row[$column['db_name']]);
	    return $this->ci->load->view('besc_crud/table_elements/url', $dummy, true);
	}	
	
	protected function list_image_gallery($row, $column)
	{
	    $items = $this->ci->bc_model->get_image_gallery_items($column['gallery_table'], $column['gallery_table_fk'], $row[$this->db_primary_key]);
	    $dummy = array('items' => ($items == false ? array() : $items),
	                   'uploadpath' => $column['uploadpath'],
	                   'fname' => $column['gallery_fname']);
	    
	    return $this->ci->load->view('besc_crud/table_elements/image_gallery', $dummy, true);
	}
	
	protected function list_multiline($row, $column)
	{
	    $dummy = array('text' => nl2br($row[$column['db_name']]));
	    return $this->ci->load->view('besc_crud/table_elements/text', $dummy, true);
	}
	
	protected function list_date($row, $column)
	{
	    if($row[$column['db_name']] != null && $row[$column['db_name']] != "0000-00-00 00:00:00" && $row[$column['db_name']] != "" && $row[$column['db_name']] != "1970-01-01 01:00:00")
	        $dummy = array('date' => date($column['list_format'], strtotime($row[$column['db_name']])));
	    else 
            $dummy = array('date' => "");
	    return $this->ci->load->view('besc_crud/table_elements/date', $dummy, true);
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
		
		return $this->ci->load->view('besc_crud/edit_elements/image', $col, true);
	}

	protected function edit_select($col)
	{
		return $this->ci->load->view('besc_crud/edit_elements/select', $col, true);
	}
	
	protected function edit_multiline($col)
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
	
	protected function edit_date($col)
	{
	    return $this->ci->load->view('besc_crud/edit_elements/date', $col, true);
	}
	

}