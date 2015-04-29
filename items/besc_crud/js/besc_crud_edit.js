var bc_and_go_back = false;

$(document).ready(function()
{	
	if(bc_edit_or_add == 0)
		bc_bindAddListeners();
	else
		bc_bindEditListeners();
	
	bc_addMNRelationListeners();
	bc_addImageListeners();
});


function bc_bindAddListeners()
{
	$('.bc_add').click(function()
	{
		bc_add();
	});
	
	$('.bc_add_and_go_back').click(function()
	{
		bc_add();
		bc_and_go_back = true;
	});
	
	$('.bc_add_cancel').click(function()
	{
		window.location.href = bc_list_url;
	});
}

function bc_bindEditListeners()
{
	$('.bc_update').click(function()
	{
		bc_edit();
	});
	
	$('.bc_update_and_go_back').click(function()
	{
		bc_edit();
		bc_and_go_back = true;
	});
	
	$('.bc_update_cancel').click(function()
	{
		window.location.href = bc_list_url;
	});	
}


function bc_add()
{
	$.ajax(
	{
        url: bc_insert_url,
        data: JSON.stringify(bc_getData()),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        dataType: "json",
        success: function(data) 
        {
        	if(data.success)
        	{
        		showMessage('success', data.message);
        		if(bc_and_go_back)
				{
					window.location.href = bc_list_url;
				}
				else
				{
					bc_resetData();
				}
        	}
			else
			{
				showMessage('error', data.message);
			}
        }
    });	
}

function bc_edit()
{
	$.ajax(
	{
        url: bc_edit_url + bc_pk_value,
        data: JSON.stringify(bc_getData()),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        dataType: "json",
        success: function(data) 
        {
        	if(data.success)
        	{
        		showMessage('success', data.message);
        		if(bc_and_go_back)
				{
					window.location.href = bc_list_url;
				}
        	}
			else
			{
				showMessage('error', data.message);
			}
        }
    });	
}



function bc_resetData()
{
	// text
	$('.bc_col_text').each(function()
	{
		$(this).find('input').val('')
	});
	
	// select
	$('.bc_col_select').each(function()
	{
		$(this).find('select option:first').attr('selected', 'selected');		
	});
	
	// image upload
	$('.bc_col_image').each(function()
	{
		$(this).find('.col_image_delete').click();
	});	
	
	// hidden -- will be skipped
	
	// textarea
	$('.bc_col_multiline').each(function()
	{
		$(this).find('textarea').val('');
	});	
}


function bc_getData()
{
	var elements = [];
	
	// text
	$('.bc_edit_table').find('.bc_col_text').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('input').attr('name').replace('col_', ''),
				'value': $(this).find('input').val(),
				'type': 'text'
			}
		);
	});
	
	// select
	$('.bc_edit_table').find('.bc_col_select').each(function()
	{
		elements.push
		(
			{
				'name': $(this).find('select').attr('name').replace('col_', ''),
				'value': $(this).find('select option:selected').attr('value'),
				'type': 'select'		
			}
		);
	});
	
	// image upload
	$('.bc_edit_table').find('.bc_col_image').each(function()
	{
		elements.push
		(
			{
				'name': $(this).find('.bc_col_fname').attr('name').replace('col_', ''),
				'value': $(this).find('.bc_col_fname').val(),
				'type': 'image'		
			}
		);
	});	
	
	// hidden
	$('.bc_edit_table').find('.bc_col_hidden').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('input').attr('name').replace('col_', ''),
				'value': $(this).find('input').val(),
				'type': 'hidden'
			}
		);
	});	
	
	// textarea
	$('.bc_edit_table').find('.bc_col_multiline').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('textarea').attr('name').replace('col_', ''),
				'value': $(this).find('textarea').val(),
				'type': 'multiline'
			}
		);		
	});
	
	// m_n_relation
	$('.bc_edit_table').find('.bc_col_m_n').each(function()
	{
		var selected = [];
		$(this).find('.bc_m_n_sel').each(function()
		{
			selected.push($(this).attr('n_id'));
		});
		
		elements.push
		( 
			{
				'relation_id': $(this).attr('m_n_relation_id'),
				'selected': selected,
				'type': 'm_n_relation'
			}
		);		
	});	
	
	// url
	$('.bc_edit_table').find('.bc_col_url').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('input').attr('name').replace('col_', ''),
				'value': $(this).find('input').val(),
				'type': 'url'
			}
		);
	});	
	
	return elements;		
}


function bc_addMNRelationListeners()
{
	$('.bc_m_n_sel').click(function()
	{
		bc_MNRelationClick($(this));
	});
	
	$('.bc_m_n_av').click(function()
	{
		bc_MNRelationClick($(this));
	});
}

function bc_MNRelationClick(element)
{
	var clone = element.clone();
	clone.fadeOut(0);
	if(element.hasClass('bc_m_n_sel'))
	{
		element.removeClass('bc_m_n_sel');
		clone.removeClass('bc_m_n_sel');
		clone.addClass('bc_m_n_av');
		var target = element.parent().parent().find('.bc_m_n_avail');
	}	
	else
	{
		element.removeClass('bc_m_n_av');
		clone.removeClass('bc_m_n_av');
		clone.addClass('bc_m_n_sel');	
		var target = element.parent().parent().find('.bc_m_n_selected');
	}
	
	element.remove();
	target.append(clone);
	clone.click(function()
	{
		bc_MNRelationClick($(this));
	});
}


function bc_addImageListeners()
{
	$('.bc_col_image_upload_btn').click(function()
	{
		$(this).parent().find('input[type="file"]').click();
	});
	
	$('.bc_col_image_file').change(function()
	{
		bc_uploadFile($(this).attr('id'), $(this).attr('uploadpath'));
	});		
	
	$('.bc_col_image_delete').click(function()
	{
		bc_resetUpload($(this).parent());
	});
}

function bc_uploadFile(element, u)
{
	var file = document.getElementById(element).files[0];
	var uploadpath = u;
	var reader = new FileReader();
	var url;
	reader.readAsDataURL (file);
	reader.onload = function(event)
	{
		var result = event.target.result;
		var elem = $('#' + element);
		var element_name = elem.attr('name').substr(4, elem.attr('name').length -9);
		$.ajax(
		{
			url: bc_upload_url,
			data: { filename: file.name, element: element_name, data: result },
			method: 'POST',
			success: function(data)
			{
				var ret = $.parseJSON(data);
				
				if(ret.success)
				{
					var col = $('#' + element).parent();
					col.find('.bc_col_image_upload_btn').fadeOut(150, function()
					{
						col.find('.bc_col_image_preview').attr('src', rootUrl + '/' + uploadpath + '/' + ret.filename);
						col.find('a').attr('href', rootUrl + '/' + uploadpath + '/' + ret.filename);
						col.find('.bc_col_image_preview').fadeIn(150);
						col.find('.bc_col_image_delete').fadeIn(150);
						col.find('.bc_col_fname').val(ret.filename);						
					});
				}
				else
				{
					show_message('error', 'Error while uploading!');
				}
			}
		});			
	};
}

function bc_resetUpload(col)
{
	col.find('.bc_col_fname').val('');
	col.find('.bc_col_image_delete').fadeOut(150);
	col.find('.bc_col_image_preview').fadeOut(150, function()
	{
		col.find('.bc_col_image_preview').attr('src', '');
		col.find('a').attr('src', '');
		col.find('.bc_col_image_upload_btn').fadeIn(150);
	});
}


