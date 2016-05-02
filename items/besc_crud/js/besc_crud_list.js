var bc_delete_dialog_phasing = 150;
var bc_row_to_be_deleted = false;

$(document).ready(function()
{	
	bc_bindListListeners(true);
	bc_bindPagingListeners(true);
	bc_bindFilterListeners(true);
});


function bc_bindListListeners(toggle)
{
	if(toggle)
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
						bc_refresh(bc_paging_active);
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
		
		$('.bc_sortable').on('click', function()
		{
			bc_sorting($(this));//$(this).toggleClass('bc_table_sort_asc');
		});
	}
	else
	{
		$('.bc_delete_ok').unbind('click');
		$('.bc_delete_cancel').unbind('click');
		$('.bc_row_action.delete').unbind('click');
	}
}

function bc_toggle_fade(toggle)
{
	if(toggle)
		$('.bc_fade').fadeIn(bc_delete_dialog_phasing);
	else
		$('.bc_fade').fadeOut(bc_delete_dialog_phasing);
}


function bc_sorting(th)
{
	if(th.hasClass('bc_table_sort_asc'))
	{
		th.removeClass('bc_table_sort_asc').addClass('bc_table_sort_desc');
	}
	else if(th.hasClass('bc_table_sort_desc'))
	{
		th.removeClass('bc_table_sort_desc').addClass('bc_table_sort_asc');
	}
	else
	{
		$('.bc_sortable').removeClass('bc_table_sort_asc').removeClass('bc_table_sort_desc');
		th.addClass('bc_table_sort_asc');
	}
	
	bc_refresh(0);
}

function bc_refresh(page)
{
	if(page === undefined)
		page = bc_paging_active;
	
	$.ajax(
	{
        url: bc_refresh_url,
        data: {
        	'page': page, 
        	'filter': getFilterSettings(),
        	'sorting': getSorting(),
    	},
        type: "GET",
        dataType: "json",
        beforeSend: function()
        {
        	bc_toggle_fade(true);
        	bc_bindListListeners(false);
        	bc_bindPagingListeners(false);
        	bc_bindFilterListeners(false);
        },
        success: function(data)
        {
			
        	if(data.success)
        	{
        		bc_toggle_fade(false);
				$('.bc_table').replaceWith(data.data);
				$('.bc_paging_and_filtering').replaceWith(data.paging_and_filtering);
				bc_paging_active = $('.bc_current_page').attr('page');
        	}
			else
			{
				showMessage('error', 'ERROR!!!');
			}
        	bc_bindPagingListeners(true);
			bc_bindListListeners(true);
			bc_bindFilterListeners(true);
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
        	showMessage('error', errorThrown);
        }
    });	
}


function bc_bindPagingListeners(toggle)
{
	if(toggle)
	{
		$('.bc_paging_page').click(function()
		{
			bc_refresh($(this).attr('page'));
		});		
		
		$('.bc_paging_button').click(function()
		{
			bc_refresh($(this).attr('target'));
		});
	}
	else
	{
		$('.bc_paging_page').unbind('click');
		$('.bc_paging_button').unbind('click');
	}

}


function bc_bindFilterListeners(toggle)
{
	if(toggle)
	{
		$('.bc_filter select').change(function()
		{
			bc_refresh(0);
		});
		
		$('.bc_filter input').keyup(function(e)
		{
			var code = e.which;
			if(code == 13)
				bc_refresh(0);
		});
		
		$('.bc_filter_reset').click(function()
		{
			$('.bc_filter input').val('');
			$('.bc_filter select').each(function()
			{
				$(this).find('option:selected').removeAttr('selected');
				$(this).val('null');
			});
			
			bc_refresh(0);
		});
		
		$('.bc_filter_search').click(function()
		{
			bc_refresh(0);
		});
	}
	else
	{
		$('.bc_filter select').unbind('change');
		$('.bc_filter input').unbind('keyup');
		$('.bc_filter_reset').unbind('click');
		$('.bc_filter_search').unbind('click');
	}
}

function getFilterSettings()
{
	var bc_filter = [];
	
	$('.bc_filter').each(function()
	{
		switch($(this).attr('type'))
		{
			case 'select':
			case 'combobox':
				bc_filter.push
				(
					{
						'name': $(this).find('select').attr('name'),
						'value': $(this).find('select').val(),
						'type': 'select'		
					}
				);
				break;
				
			case 'text':
			case 'm_n_relation':
				bc_filter.push
				(
					{
						'name': $(this).find('input').attr('name'),
						'value': $(this).find('input').val(),
						'type': $(this).attr('type')		
					}
				);
				break;
		}
		
	});
	
	return bc_filter;
}

function getSorting()
{
	var col = $('.bc_table_sort_asc').length == 1 ? $('.bc_table_sort_asc') : $('.bc_table_sort_desc');
	
	if(col.length != 0)
		var ret = {'col_id': col.attr('col'), 'direction': col.hasClass('bc_table_sort_asc') ? 0 : 1};
	else
		var ret = {};
	
	return ret;
}