var bc_delete_dialog_phasing = 150;
var bc_row_to_be_deleted = false;

$(document).ready(function()
{	
	bc_bindListListeners();
});


function bc_bindListListeners()
{
	$('.bc_delete_ok').click(function()
	{
		$.ajax(
		{
	        url: bc_delete_url + bc_row_to_be_deleted,
	        type: "POST",
	        dataType: "json",
	        success: function(data) 
	        {
	        	if(data.success)
	        	{
					bc_refresh();
					$('.bc_delete_dialog').fadeOut(bc_delete_dialog_phasing);
	        	}
				else
				{
					showMessage('error', data.message);
				}
	        }
	    });			
	});

	$('.bc_delete_cancel').click(function()
	{
		$('.bc_delete_dialog').fadeOut(bc_delete_dialog_phasing);
		bc_toggle_fade(false);
	});
	
	$('.bc_row_action.delete').click(function()
	{
		bc_row_to_be_deleted = $(this).attr('row_id');
		bc_toggle_fade(true);
		$('.bc_delete_dialog').fadeIn(bc_delete_dialog_phasing);
	});
}

function bc_toggle_fade(toggle)
{
	if(toggle)
		$('.bc_fade').fadeIn(bc_delete_dialog_phasing);
	else
		$('.bc_fade').fadeOut(bc_delete_dialog_phasing);
}

function bc_refresh()
{
	$.ajax(
	{
        url: bc_refresh_url,
        type: "POST",
        dataType: "json",
        beforeSend: function()
        {
        	bc_toggle_fade(true);
        },
        success: function(data)
        {
        	if(data.success)
        	{
        		bc_toggle_fade(false);
				$('.bc_table').replaceWith(data.data);
				bc_bindListListeners();
        	}
			else
			{
				showMessage('error', 'ERROR!!!');
			}
        },
        error: function(data)
        {
        	showMessage('error', "ERROR!!!");
        }
    });	
}
