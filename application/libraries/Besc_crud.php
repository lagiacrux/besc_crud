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
	protected $db_order_by_field = '';
	protected $db_order_by_direction = '';
	
	protected $list_columns = array();
	protected $filter_columns = array();
	protected $filters = array(
	    'select' => array(),
	    'text' => array(),
	    'm_n_relation' => array(),
	);
	protected $ordering = array();
	
	protected $sortableTypes = array('text', 'select', 'combobox');
	
	protected $states = array('list', 'add', 'insert', 'edit', 'update', 'delete', 'refresh_list', 'imageupload', 'validation', 'filter', 'ordering', 'save_ordering', 'imagecrop');
	protected $state_info = array();
	protected $base_url = "";
	
	protected $title = "";
	protected $hashname = "";
	
	protected $custom_actions = array();
	protected $custom_buttons = array();
	
	protected $allow_add = true;
	protected $allow_delete = true;
	protected $allow_edit = true;
	
	protected $paging_perpage = 30;
	protected $paging_offset = 0;
	
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
	
	public function order_by_field($order_by_string = "")
	{
	    $this->db_order_by_field = $order_by_string != "" ? $order_by_string : $this->db_order_by_field;
	    return $this->db_order_by_field;	    
	}
	
	public function order_by_direction($order_by_string = "")
	{
	    $this->db_order_by_direction = $order_by_string != "" ? $order_by_string : $this->db_order_by_direction;
	    return $this->db_order_by_direction;
	}
	
	public function ordering($ordering = array())
	{
	    $this->ordering = $ordering != "" ? $ordering : $this->ordering;
	    return $this->ordering;	    
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
	
	public function filter_columns($filter_columns = array())
	{
	    $this->filter_columns = $filter_columns != array() ? $filter_columns : $this->filter_columns;
	    return $this->filter_columns;
	}
	
	
	public function get_state_info_from_url()
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
			case 'refresh_list':
				return array(	'bc_delete_url' => $this->base_url . 'delete/',
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_refresh_url' => $this->base_url . 'refresh_list/',
								'bc_edit_url' => $this->base_url . 'edit/',
					  		);
				break;
			case 'add':
			
				return array(	'bc_insert_url' => $this->base_url . 'insert',
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_upload_url' => $this->base_url . 'imageupload',
								'bc_validation_url' => $this->base_url . 'validation/',
								'bc_crop_url' => $this->base_url . 'imagecrop',
					  		);
			case 'edit':
				return array(	'bc_edit_url' => $this->base_url . 'update/',
								'bc_list_url' => substr($this->base_url, 0, -1),
								'bc_upload_url' => $this->base_url . 'imageupload',
								'bc_validation_url' => $this->base_url . 'validation/',
								'bc_crop_url' => $this->base_url . 'imagecrop',
				);
				break;
			case 'ordering':
			    return array(	
                    'bc_list_url' => substr($this->base_url, 0, -1),
			        'bc_ordering_url' => $this->base_url . 'save_ordering/',
			    );
			    break;
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
	    $this->hashname = hash_hmac("md5", $this->db_table . $this->title, $this->ci->session->session_id);
		$this->state_info = $this->get_state_info_from_url();
		$this->base_url = $this->get_base_url();
	}
	
	public function execute()
	{
		$this->prepare();
		
		switch($this->state_info->operation)
		{
			case 'list':
			    $this->getSortingFromSession();
			    $this->getFiltersFromSession();
				return $this->render_list();
				break;

			case 'delete':
			    if($this->allow_delete)
				    $this->delete();
				break;
				
			case 'refresh_list':
			    $this->paging_offset = $this->ci->input->get('page') * $this->paging_perpage;
			    $this->getFiltersFromAjax($this->ci->input->get('filter'));
			    $this->getSortingFromAjax($this->ci->input->get('sorting'));
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
			    
			case 'validation':
			    $this->validate();
			    break;
			    
			case 'ordering':
			    if($this->ordering != array())
                    return $this->render_ordering();
			    break;
			    
			case 'save_ordering':
			    if($this->ordering != array())
			        $this->save_ordering();
			    break;
			    
			case 'imagecrop':
			    $this->imagecrop();
			    break;    
		}
		die();

	}
	
	
	protected function validate()
	{
	    $form_validation = new Besc_Form_validation();
	    
	    $validate_array = array();
	    $content = json_decode(file_get_contents('php://input'));
	    $validate_columns = array();
	    foreach($content as $column)
	    {
	        if(isset($this->db_columns[$column->name]['validation']) && $this->db_columns[$column->name]['validation'] != '')
	        {
                $validate_columns[] = $column->name;	            
    	        switch($column->type)
    	        {
    	            case 'text':
    	            case 'hidden':
    	            case 'image':
    	            case 'multiline':
    	            case 'select':
    	            case 'combobox':
    	            case 'colorpicker':
    	                $validate_array[$column->name] = $column->value;
    	                break;
    	            case 'm_n_relation':
    	                break;
    	            case 'url':
    	                $validate_array[$column->name] = prep_url($column->value);
    	                break;
    	            case 'date':
    	                $validate_array[$column->name] = date('Y-m-d', strtotime($column->value));
    	                break;
    	            case 'colorpicker':
    	                //$this->db_columns[$column->name]['validation'] .= '|/#([a-fA-F0-9]{3}){1,2}\b/';
    	                $validate_array[$column->name] = $column->value;
    	                break;
    	        }
    	        
    	        $rules = $form_validation->fix_is_unique_rule($this->db_primary_key, $this->state_info->first_parameter, $this->db_columns[$column->name]['validation']);
    	        
    	        $form_validation->set_rules($column->name, isset($this->db_columns[$column->name]['display_as']) ? $this->db_columns[$column->name]['display_as'] : $this->db_columns[$column->name]['db_name'], $rules);
	        }
	    }
	    if(count($validate_array) > 0)
	    {
	        $form_validation->set_data($validate_array);
	        $form_validation->set_message('required', '{field} is mandatory.');
	        $form_validation->set_error_delimiters('', '');
	         
	        if ($form_validation->run() == FALSE)
	        {
	            $error_columns = array();
	            foreach($validate_columns as $col)
	            {
	                if($form_validation->error($col) != '')
	                    $error_columns[] = array(
	                        'name' => $col,
	                        'error' => $form_validation->error($col),
	                    );
	            }
	             
	            echo json_encode(array(
	                'success' => false,
	                'error_columns' => $error_columns,
	            ));
	        }
	        else
	        {
	            echo json_encode(array(
	                'success' => true,
	                'message' => 'success validation',
	            ));
	        }
	    }
	    else
	        echo json_encode(array(
	            'success' => true,
	            'message' => 'success validation',
	            'dummy' => 'no_validation',
	        ));
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
			    case 'ckeditor':
				case 'combobox':
				case 'colorpicker':
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
			    case 'ckeditor':
				case 'combobox':
				case 'colorpicker':
					$col[$column->name] = $column->value;
					break;
				case 'm_n_relation':
					$col_after[] = $column;
					break;
				case 'url':
				    $col[$column->name] = prep_url($column->value);
				    break;
			    case 'date':
			        $col[$column->name] =  $column->value == 'null' ? NULL : date('Y-m-d H:i:s', strtotime($column->value));
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
	    $filename = $this->ci->input->post('filename');
	    $col_name = $this->ci->input->post('element');
	    
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
	        $ext = pathinfo($filename, PATHINFO_EXTENSION);
	        $serverFile = time() . "_" . $rnd . "." . $ext;
	         
	        $error = move_uploaded_file($_FILES['data']['tmp_name'], getcwd() . "/$upload_path/$serverFile");
	    } 

	    $crop = isset($this->db_columns[$col_name]['crop']) ? $this->db_columns[$col_name]['crop'] : null;
	    
	    
        echo json_encode(array('success' => true,
                               'filename' => $serverFile,
                               'crop' => $crop,
        ));
	}
	
	protected function imagecrop()
	{
	    $filename = $this->ci->input->post('filename');
	    $col = $this->ci->input->post('col');
	    $ratio = $this->ci->input->post('ratio');
	    
	    $x1 = intval($this->ci->input->post('x1') * $ratio);
        $y1 = intval($this->ci->input->post('y1') * $ratio);
        $x2 = intval($this->ci->input->post('x2') * $ratio);
        $y2 = intval($this->ci->input->post('y2') * $ratio);
	    
	    
	    $uploadpath = $this->db_columns[$col]['uploadpath'];
	    $cropdata = $this->db_columns[$col]['crop'];
	    
	    if(substr($uploadpath, -1) != '/')
	        $uploadpath .= '/';
	     
	    $width = $destWidth = $x2-$x1;
	    $height = $destHeight = $y2-$y1;
	    
	    if($destWidth > $cropdata['maxWidth'])
	        $destWidth = $cropdata['maxWidth'];
	    
	    if($destHeight > $cropdata['maxHeight'])
	        $destHeight = $cropdata['maxHeight'];
	    
	    $new_img = imagecreatetruecolor( $destWidth, $destHeight );
	    $col=imagecolorallocatealpha($new_img,255,255,255,127);
	    imagefill($new_img, 0, 0, $col);
	    
	    $ext = pathinfo($filename, PATHINFO_EXTENSION);
	    
	    switch($ext)
	    {
	        case 'png':
	            $img = imagecreatefrompng(getcwd() . '/' . $uploadpath . $filename);
	            break;
	        case 'jpg':
	        case 'jpeg':
	            $img = imagecreatefromjpeg(getcwd() . '/' . $uploadpath . $filename);
	            break;
	    }
	    
	    imagecopyresampled($new_img, $img, 0, 0, $x1, $y1, $destWidth, $destHeight, $width, $height);
	    imagepng($new_img, getcwd() . '/' . "$uploadpath/$filename");
	    
	    echo json_encode(array('success' => true));
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
		    
		    if($column['type'] == 'hidden')
		        $show_in_list = false;
		    
		    if($show_in_list)
		        $data['headers'][] = array(
		            'display_as' => isset($column['display_as']) ? $column['display_as'] : $key,
		            'sortable' => in_array($column['type'], $this->sortableTypes),
		            'id' => $key,
		        );
		}
		
		$get = $this->getData();
		
		$rows = array();
		
		foreach($get['data']->result_array() as $row)
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
    					    break;
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
    					case 'ckeditor':
    					    $columns[$key] = $this->list_multiline($row, $column);
    					    break;
    					case 'date':
    					    $columns[$key] = $this->list_date($row, $column);
    					    break;
					    case 'combobox':
					        $columns[$key] = $this->list_combobox($row, $column);
					        break;  
					    case 'colorpicker':
					        $columns[$key] = $this->list_colorpicker($row, $column);
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
		$data['paging_and_filtering'] = $this->paging_and_filtering($get);
		$data['sorting_col'] = $this->db_order_by_field;
		$data['sorting_direction_class'] = $this->db_order_by_direction == 'asc' ? 'bc_table_sort_asc' : 'bc_table_sort_desc';
		$data['ordering'] = $this->ordering; 
		
		$data['ajax'] = $ajax;
			
		if(!$ajax)
		{
			return $this->ci->load->view('besc_crud/table_view', $data, true);		
		}
		else
		{
			echo json_encode(array(	'success' => true,
							 		'data' => $this->ci->load->view('besc_crud/table_view', $data, true),
			                        'paging_and_filtering' => $data['paging_and_filtering'],
			));
		}
	}
	
	protected function getFiltersFromAjax($ajaxfilter)
	{
	    $select = array();
	    $text = array();
	    $m_n = array();
	    
	    if($ajaxfilter != null)
	    {
	        foreach($ajaxfilter as $filter)
    	    {
    	        switch($filter['type'])
    	        {
    	            case 'select':
    	                if($filter['value'] != 'null')
    	                {
                            $select[$filter['name']] = $filter['value'];
    	                }
    	                break;
    	                
    	            case 'text':
    	                if($filter['value'] != '')
    	                {
    	                    $text[$filter['name']] = $filter['value'];
    	                }
    	                break;
    	                
    	            case 'm_n_relation':
    	                if($filter['value'] != '')
    	                {
    	                    $col = $this->db_columns[$filter['name']];
                            
                            $elements = array(); 
                            foreach($this->ci->bc_model->get_m_n_relation_filter_ids($col['table_mn'], $col['table_mn_col_m'], $col['table_mn_col_n'], $this->db_primary_key, $col['table_n'], $col['table_n_value'], $col['table_n_pk'], $filter['value'])->result_array() as $row)
                            {
                                $elements[] = $row[$col['table_mn_col_m']];
                            }
                            $m_n[$filter['name']] = array(
                                'id' => $this->db_primary_key,
                                'values' => $elements == array() ? array(null) : $elements,
                                'val' => $filter['value'],
                            );
    	                }
    	                break;
    	        }
    	    }
	    }
	    
	    $this->filters = array(
	        'select' => $select,
	        'text' => $text,
	        'm_n_relation' => $m_n,
	    );
	    
	    if($select != array() || $text != array() || $m_n != array())
	        $this->ci->session->set_userdata($this->hashname . '_filter', $this->filters);
	    else
	        $this->ci->session->unset_userdata($this->hashname . '_filter');
	}
	
	protected function getSortingFromAjax($ajaxSorting)
	{
	    if($ajaxSorting != null)
        {
            $direction = '';
            if(intval($ajaxSorting['direction']) == 0)
                $direction = 'asc';
            
            if(intval($ajaxSorting['direction']) == 1)
                $direction = 'desc';
            
            if($this->columnExists($ajaxSorting['col_id']) && $direction != '')
            {
                $this->db_order_by_direction = $direction;
                $this->db_order_by_field = $ajaxSorting['col_id'];
                
                $this->ci->session->set_userdata($this->hashname . '_sortcol', $this->db_order_by_field);
                $this->ci->session->set_userdata($this->hashname . '_sortdir', $this->db_order_by_direction);
            }
            else
            {
                $this->ci->session->unset_userdata($this->hashname . '_sortcol');
                $this->ci->session->unset_userdata($this->hashname . '_sortdir');
            }
        } 
        else
        {
            $this->ci->session->unset_userdata($this->hashname . '_sortcol');
            $this->ci->session->unset_userdata($this->hashname . '_sortdir');
        }
	}
	
	protected function getSortingFromSession()
	{
	    $sortfield = $this->ci->session->userdata($this->hashname . '_sortcol');
	    $sortdir = $this->ci->session->userdata($this->hashname . '_sortdir');
	    
	    if(($sortdir == 'asc' || $sortdir == 'desc') && $this->columnExists($sortfield))
	    {
	        $this->db_order_by_direction = $sortdir;
	        $this->db_order_by_field = $sortfield;
	    }
	}
	
	protected function getFiltersFromSession()
	{
	    $filters = $this->ci->session->userdata($this->hashname . '_filter'); 
	    if($filters != NULL)
	        $this->filters = $filters;
	}
	
	protected function getData()
	{
	    return array(
	        'data' => $this->ci->bc_model->get($this->db_table, $this->db_where, $this->paging_perpage, $this->paging_offset, $this->filters, $this->db_order_by_field, $this->db_order_by_direction),
	        'total' => $getTotal = $this->ci->bc_model->get_total($this->db_table, $this->db_where, $this->filters)->num_rows(), 
	    );
	}
	
	protected function columnExists($col)
	{
	    $exists = false;
	    foreach($this->db_columns as $key => $column)
	    {
	        if($col == $key)
	            $exists = true;
	    }
	    
	    return $exists;
	}
	
	protected function paging_and_filtering($get)
	{
	    $data['paging'] = $this->paging($get);
	    $data['filtering'] = $this->filtering($get);
	    
	    return $this->ci->load->view('besc_crud/paging_and_filtering', $data, true);
	}
	
	protected function filtering($get)
	{
	    $html = '';
	    foreach($this->filter_columns as $filter)
	    {
	        
	        switch($this->db_columns[$filter]['type'])
	        {
	            case 'select':
	            case 'combobox':
	                $data['filter_value'] = isset($this->filters['select'][$filter]) ? $this->filters['select'][$filter] : '';
	                $data['options'] = $this->db_columns[$filter]['options'];
	                $data['db_name'] = $this->db_columns[$filter]['db_name'];
	                $data['display_as'] = $this->db_columns[$filter]['display_as']; 
	                $data['type'] = $this->db_columns[$filter]['type'];
	                $html .= $this->ci->load->view('besc_crud/filters/select', $data, true);
	                break;
	            case 'm_n_relation':
	                $data['filter_value'] = isset($this->filters['m_n_relation'][$filter]['val']) ? $this->filters['m_n_relation'][$filter]['val'] : '';
	                $data['display_as'] = $this->db_columns[$filter]['display_as'];
	                $data['db_name'] = $filter;
	                $data['type'] = $this->db_columns[$filter]['type'];
	                $html .= $this->ci->load->view('besc_crud/filters/text', $data, true);
	                break;
                case 'text':
                case 'multiline':
                    $data['filter_value'] = isset($this->filters['text'][$filter]) ? $this->filters['text'][$filter] : '';
	                $data['display_as'] = $this->db_columns[$filter]['display_as'];
	                $data['db_name'] = $this->db_columns[$filter]['db_name'];
	                $data['type'] = 'text';
	                $html .= $this->ci->load->view('besc_crud/filters/text', $data, true);
	                break;    
	        }
	    }
	    
	    return $html;
	}
	
	protected function paging($get)
	{
	    
	    $paging = array(
		    'total' => $get['total'],
		    'currentPage' => ($this->paging_offset / $this->paging_perpage),
		    'totalPages' => ceil($get['total'] / $this->paging_perpage),
		    'list_start' => ($this->paging_offset / $this->paging_perpage),
		    'list_end' => ($this->paging_offset / $this->paging_perpage),
		);
	    
	    $i = 1;
	    while ($i < 3 && $paging['currentPage'] - $i >= 0)
	    {
	        $paging['list_start'] = $paging['currentPage'] - $i;
	        $i++;
	    }
	    
	    $i = 1;
	    while ($i < 3 && $paging['currentPage'] + $i < $paging['totalPages'])
	    {
	        $paging['list_end'] = $paging['currentPage'] + $i;
	        $i++;
	    }
	    
	    return $paging;
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
		
		if($this->state_info->first_parameter != null)
		    $data['edit_or_add'] = BC_EDIT;
		else
		    $data['edit_or_add'] = BC_ADD;
		
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
				    $columns[$i] = $this->edit_date($col, $data['edit_or_add']);
				    break;
			    case 'combobox':
			        $columns[$i] = $this->edit_combobox($col);
			        break;
                case 'file':
                    if(!isset($col['js_callback_after_upload']))
                        $col['js_callback_after_upload'] = "";
                    $columns[$i] = $this->edit_image($col);
                    break;
                case 'ckeditor':
                    $columns[$i] = $this->edit_ckeditor($col);
                    break;
                case 'colorpicker':
                    $columns[$i] = $this->edit_colorpicker($col);
                    break;
			}
			$i++;
		}

		ksort($columns, SORT_NUMERIC);
		
		$data['columns'] = $columns;
		$data['title'] = $this->title;
		
		
		$data['bc_urls'] = $this->get_urls();
		
		
		return $this->ci->load->view('besc_crud/edit_view', $data, true);
	}
	
	
	/***************************************************************************************************************************************************************************************
	 *
	 * RENDER LIST FUNCTIONS
	 *
	 **************************************************************************************************************************************************************************************/
	
	
	
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
	
	protected function list_combobox($row, $column)
	{
	    $dummy = array('options' => $column['options'],
	        'value' => $row[$column['db_name']] );
	    return $this->ci->load->view('besc_crud/table_elements/combobox', $dummy, true);
	}
	
	protected function list_colorpicker($row, $column)
	{
	    $dummy = array(
	        'value' => $row[$column['db_name']] 
        );
	    return $this->ci->load->view('besc_crud/table_elements/colorpicker', $dummy, true);
	}
	
	
	/***************************************************************************************************************************************************************************************
	 * 
	 * RENDER EDIT FUNCTIONS
	 * 
	 **************************************************************************************************************************************************************************************/
	
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
		
		$col['avail'] = $this->ci->bc_model->get_m_n_relation_n_values($col['table_n'], $col['table_n_pk'], $selected, $col['table_n_value']);

		$col['filter'] = isset($col['filter']) ? $col['filter'] : false;
		
		return $this->ci->load->view('besc_crud/edit_elements/m_n_relation', $col, true);
	}	
	
	protected function edit_url($col)
	{
	    return $this->ci->load->view('besc_crud/edit_elements/url', $col, true);
	}	
	
	protected function edit_date($col, $add_or_edit)
	{
	    $col['corr_value'] = '';
	    if($add_or_edit == BC_ADD)
	    {
	        if(isset($col['defaultvalue']))
	        {
	            $col['corr_value'] = $col['defaultvalue'];
	        }
	    }
	    else
	    {
	        if(isset($col['value']) && $col['value'] != "" && $col['value'] != 1 && $col['value'] != "0000-00-00 00:00:00" && $col['value'] != null && $col['value'] != '1970-01-01 01:00:00')
	        {
	            $col['corr_value'] = $col['value'];
	        }
	    }
	    
	    return $this->ci->load->view('besc_crud/edit_elements/date', $col, true);
	}
	
    protected function edit_combobox($col)
	{
		return $this->ci->load->view('besc_crud/edit_elements/combobox', $col, true);
	}
	
	protected function edit_file($col)
	{
	    if(substr($col['uploadpath'], -1) != '/')
	        $col['uploadpath'] .= '/';
	
	    return $this->ci->load->view('besc_crud/edit_elements/file', $col, true);
	}
	
	protected function edit_ckeditor($col)
	{
	    return $this->ci->load->view('besc_crud/edit_elements/ckeditor', $col, true);
	}
	
	protected function edit_colorpicker($col)
	{
	    $col['hexinput'] = isset($col['hexinput']) && $col['hexinput']; 
	    return $this->ci->load->view('besc_crud/edit_elements/colorpicker', $col, true);
	}
	
	
	protected function render_ordering()
	{
	    $data['title'] = $this->title;
	    $data['bc_urls'] = $this->get_urls();
	    $data['items'] = array();
	    
	    $value_col = $this->db_columns[$this->ordering['value']];
	    
	    foreach($this->ci->bc_model->get_ordering($this->db_table, $this->db_where, $this->ordering['ordering'])->result_array() as $column)
	    {
	        switch($value_col['type'])
	        {
    	        case 'select':
                case 'combobox':
                    foreach($value_col['options'] as $col)
                    {
                        if($col['key'] == $column[$this->ordering['value']])
                        {
                            $val = $col['value'];
                            break;
                        }
                    }
                    break;
                case 'text':
                case 'url':
                case 'date':
                case 'multiline':
                    $val = $column[$this->ordering['value']];
                    break;
                default:
                    $val = null;
                    break;
            }
            
            if($val != null)
            {
                $data['items'][] = array(
                    'id' => $column[$this->db_primary_key],
                    'value' => $val,
                    'ordering' => $column[$this->ordering['ordering']],
                );
            }
	    } 
	    
	    return $this->ci->load->view('besc_crud/ordering_view', $data, true);
	}
	
	protected function save_ordering()
	{
	    $content = json_decode(file_get_contents('php://input'));
	    $batch = array();
	    foreach($content as $column)
	    {
	        $batch[] = array(
                $this->db_primary_key => $column->id,
	            $this->ordering['ordering'] => $column->ordering, 
	        );
	    }
	    
	    if($this->ci->bc_model->save_ordering($this->db_table, $batch, $this->db_primary_key))
		{
		    $result['success'] = true;
		    $result['message'] = $this->title . ' sorting successfully updated.';
		}
		else
		{
		    $result['success'] = false;
		    $result['message'] = 'Error while updating ' . $this->title . '.';
		}
		
		echo json_encode($result);
	}
}

require_once SYSDIR . '/libraries/Form_validation.php';

class Besc_Form_validation extends CI_Form_validation
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function is_unique($str, $field) 
    {
    
        if (substr_count($field, '.') == 3) {
            list($table, $field, $id_field, $id_val) = explode('.', $field);
            $query = $this->CI->db->limit(1)->where($field, $str)->where($id_field . ' != ', $id_val)->get($table);
        } else {
            list($table, $field) = explode('.', $field);
            $query = $this->CI->db->limit(1)->get_where($table, array($field => $str));
        }
    
        return $query->num_rows() === 0;
    }    
    
    public function fix_is_unique_rule($pk, $id, $rules)
    {
        if($id != 'null')
        {
            $new_rule = $rules;
            $rules = explode('|', $rules);
            foreach($rules as $rule)
            {
                if(preg_match('/is_unique/', $rule))
                {
                    $replace = str_replace(']', ".$pk.$id]", $rule);
                    $new_rule = str_replace($rule, $replace, $new_rule);
                }
            }
        }   
        else 
            $new_rule = $rules; 
            
        return $new_rule;        
    }
}