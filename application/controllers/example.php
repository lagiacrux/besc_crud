<?php defined('BASEPATH') OR exit('No direct script access allowed');

class example extends CI_Controller 
{
	
    function __construct()
    {
        parent::__construct();
        $this->load->library('besc_crud');
    }  

	public function index()
	{
        echo "hi!";
	}
	
	public function example()
	{
	    $bc = new besc_crud();
	    $bc->table('example_table');
	    $bc->primary_key('id');
	    $bc->title('Example table');
	     
	    $bc->columns(array(
	        array( 'db_name' => 'name',
	            'type' => 'text',
	            'display_as' => 'text test'),
	        
	        array( 'db_name' => 'select',
	            'type' => 'select',
	            'options' => array(array('key' => 0, 'value' => 'FALSE'), array('key' => 1, 'value' => 'TRUE')),
	            'display_as' => 'select test'),

	        array( 'db_name' => 'url',
	               'type' => 'url',
	               'display_as' => 'url test'),
	         
	        array( 'db_name' => 'modified_date',
	            'type' => 'hidden',
	            'display_as' => 'Modified date',
	            'value' => date('Y-m-d H:i:s')),
	         
	        array( 'db_name' => 'modified_by',
	            'type' => 'hidden',
	            'display_as' => 'Modified by',
	            'value' => 2)
	    ));
	     
	    $data['crud_data'] = $bc->execute();
	    echo $data['crud_data'];	    
	}
	
}