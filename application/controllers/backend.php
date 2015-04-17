<?php defined('BASEPATH') OR exit('No direct script access allowed');

class backend extends CI_Controller 
{
	protected $user;
	protected $base_url;

	
    function __construct()
    {
        parent::__construct();
		
		if(!$this->logged_in())
			redirect('authentication/showLogin');
		
		$this->load->model('authentication_model');
		$this->user = $this->authentication_model->getUserdataByID($this->session->userdata('user_id'))->row();
		$this->load->library('besc_crud');
		
    }  

	public function index()
	{
		$this->page('backend/home', array());
	}

	public function support()
	{
		$this->page('backend/support', array());
	}	
	
	public function news_metatag()
	{
		$bc = new besc_crud();
		$bc->table('news_metatag');
		$bc->primary_key('id');
		$bc->title('News metatag');
		
		$bc->columns(array(
				array(  'db_name' => 'name',
						'type' => 'text',
						'display_as' => 'Metatag'),
				array(  'db_name' => 'modified_date',
						'type' => 'hidden',
						'display_as' => 'Modified date',
						'value' => date('Y-m-d H:i:s')),
				array(  'db_name' => 'modified_by',
						'type' => 'hidden',
						'display_as' => 'Modified by',
						'value' => 2)
		));
		
		$data['crud_data'] = $bc->execute();
		$this->page('backend/crud', $data);		
	}
	
	public function news_article()
	{
		$bc = new besc_crud();
		$bc->table('news_article');
		$bc->primary_key('id');
		$bc->title('News article');
		
		$bc->columns(array(
			    array(  'db_name' => 'headline',
				        'type' => 'text',
				        'display_as' => 'Headline'),
				
				array(  'relation_id' => 'article_metatag',
				        'type' => 'm_n_relation',
				        'table_mn' => 'news_article_metatag',
				        'table_mn_pk' => 'id',
				        'table_mn_col_m' => 'news_article_id',
				        'table_mn_col_n' => 'news_metatag_id',
				        'table_m' => 'headline',
				        'table_n' => 'news_metatag',
				        'table_n_pk' => 'id',
				        'table_n_value' => 'name',
				        'display_as' => 'Metatags'),
		    
		        array(  'db_name' => 'url',
		                'type' => 'url',
		                'display_as' => 'URL'),		    
				
				array(  'db_name' => 'modified_date',
				        'type' => 'hidden',
				        'display_as' => 'Modified date',
				        'value' => date('Y-m-d H:i:s')),
				
				array(  'db_name' => 'modified_by',
				        'type' => 'hidden',
				        'display_as' => 'Modified by',
				        'value' => $this->user->id)
		));
		
		$data['crud_data'] = $bc->execute();
		$this->page('backend/crud', $data);		
	}
	

	
	/***********************************************************************************
	 * DISPLAY FUNCTIONS
	 **********************************************************************************/	
	
    public function page($content_view, $content_data)
    {
    	$data = array();
		$data['username'] = $this->user->username;
		$data['additional_css'] = isset($content_data['additional_css']) ? $content_data['additional_css'] : array();
		$data['additional_js'] = isset($content_data['additional_js']) ? $content_data['additional_js'] : array();

		
		$this->load->view('backend/head', $data);
		$this->load->view('backend/menu', $data);
        $this->load->view($content_view, $content_data);
		$this->load->view('backend/footer', $data);
    }	
	
	
	public function show_edit($params)
	{
		$columns = array();
		
		$data = $params;
		
		$i = 0;
		foreach($params['columns'] as $col)
		{
			$col['num_row'] = $i;
			if(!isset($col['col_info']))
				$col['col_info'] = "";
			
			
			switch($col['type'])
			{
				
				case 'select':
					$columns[] = $this->load->view('backend/edit_elements/select', $col, true);
					break;
				case 'text':
					$columns[] = $this->load->view('backend/edit_elements/text', $col, true);
					break;
				case 'multiline':
					if(!isset($col['formatting']))
						$col['formatting'] = array();
					$columns[] = $this->ci->load->view('backend/edit_elements/multiline', $col, true);
					break;
				case 'hidden':
					$columns[] = $this->ci->load->view('backend/edit_elements/hidden', $col, true);
					break;	
				case 'image':
					if(substr($col['uploadpath'], -1) != '/')
						$col['uploadpath'] .= '/';
					$columns[] = $this->load->view('backend/edit_elements/image', $col, true);
					break;					
			}
			$i++;
		}
		
		
		$data['columns'] = $columns;
		
		
		
		if(isset($params['return_html']) && $params['return_html'])
			return $this->load->view('backend/edit', $data, true);
		else
			$this->page('backend/edit', $data);
	}
	
	public function table($params)
	{
		
		foreach($params['columns'] as $column)
		{
			$data['headers'][] = $column['display_as'];
		}
		
		$rows = array();
		
		foreach($params['data']->result_array() as $row)
		{
			$columns = array();
			$columns['pk'] = $row[$params['pk']];
			foreach($params['columns'] as $column)
			{
				switch($column['type'])
				{
					case 'text':
						$dummy = array('text' => $row[$column['db_name']]);
						$columns[$column['db_name']] = $this->load->view('backend/table_elements/text', $dummy, true);
						break;
					case 'bool':
						$dummy = array('value' => $row[$column['db_name']],
 									   'options' => $column['options']);
						$columns[$column['db_name']] = $this->load->view('backend/table_elements/bool', $dummy, true);		
						break;
					case 'image':
						$dummy = array('uploadpath' => $column['uploadpath'],
									   'filename' => $row[$column['db_name']]);
						$columns[$column['db_name']] = $this->load->view('backend/table_elements/image', $dummy, true);
						break;
				}
			}
			$rows[] = $columns;
		}
		$data['rows'] = $rows;
		$data['title'] = $params['title'];
		$data['hide_add'] = (isset($params['hide_add']) && $params['hide_add'] == true) ? true : false;
		$data['custom_button'] = (isset($params['custom_button'])) ? $params['custom_button'] : array();
		$data['custom_action'] = (isset($params['custom_action'])) ? $params['custom_action'] : array();
		
		$this->page('backend/table', $data);
	}


	/***********************************************************************************
	 * EXTRA FUNCTIONS
	 **********************************************************************************/
	function createReport($data)
	{

		require_once APPPATH."/third_party/PHPExcel.php";
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $data['title']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		
		$col = 0;
		foreach ($data['headers'] as $header)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 3, $header);
			$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . '3')->getFont()->setBold(true);
			$col++;
		}
		
		$r = 4;
		$col = 0;
		
		
		foreach ($data['rows']->result_array() as $row)
		{
		 	$col = 0; 
			foreach ($row as $item)
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $r, $item);
				$col++;
			}
			$r++;
		}	
		
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: private",false);
	    header("Content-Type: application/vnd.ms-excel");
	    header("Content-Disposition: attachment; filename=\"" . $data['filename']);
	    header("Content-Transfer-Encoding: binary");
	    ob_clean();
	    flush();
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		
		$objWriter->save('php://output');
	}


	/***********************************************************************************
	 * CRUD FUNCTIONS
	 **********************************************************************************/
	public function delete($id)
	{
		if($this->backend_model->delete($this->uri->segment(2), $id) > 0)
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
	
	public function imageupload()
	{
		$upload_path = purify($_POST['uploadpath']);	
		$filename = purify($_POST['filename']);
		
		if(substr($upload_path, -1) != '/')
			$upload_path .= '/';
		
		if(substr($_SERVER['DOCUMENT_ROOT'], -1) != '/')
			$upload_path = '/' . $upload_path;
		
		$rnd = rand_string(12);
		$data = explode(',', $_POST['data']);

		$ext = pathinfo($filename, PATHINFO_EXTENSION); 
				
		$serverFile = time() . "_" . $rnd . "." . $ext;
		$fp = fopen($_SERVER['DOCUMENT_ROOT'] . $upload_path . $serverFile, 'w'); 
					
		fwrite($fp, base64_decode($data[1]));
		fclose($fp);
		
		echo json_encode(array('success' => true,
							   'filename' => $serverFile));
	}
	
	public function save($id = null)
	{
		$content = json_decode(file_get_contents('php://input'));
		$col = array();
		foreach($content as $column)
		{
			$col[purify($column->name)] = purify($column->value); 
		}

		if($id == null)
		{
			$col['created_by'] = $this->user->id;
			$col['created_date'] = date('Y-m-d H:i:s');
			
			if($this->backend_model->create($this->uri->segment(2), $col) > 0)
			{
				$result['success'] = true;
				$result['message'] = 'Dataset successfully created.';
			}
			else
			{
				$result['success'] = false;
				$result['message'] = 'Error while creating dataset.';			
			}			
		}
		else 
		{
			$col['modified_by'] = $this->user->id;
			$col['modified_date'] = date('Y-m-d H:i:s');
			
			if($this->backend_model->update($this->uri->segment(2), $id, $col) > 0)
			{
				$result['success'] = true;
				$result['message'] = 'Dataset successfully updated.';
			}
			else
			{
				$result['success'] = false;
				$result['message'] = 'Error while updating dataset.';			
			}	
		}
		
		
		echo json_encode($result);
	}

	/************************************************************************************
	 * AUTH
	 ************************************************************************************/
	public function logged_in()
	{
		return (bool) $this->session->userdata('user_id');
	}	
	
}