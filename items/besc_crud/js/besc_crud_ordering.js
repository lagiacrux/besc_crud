

$(document).ready(function()
{	
	bc_igniteOrdering();
	bc_toggleOrderingListeners(true);
});


function bc_igniteOrdering()
{
	$(".bc_ordering_container").sortable();
    $(".bc_ordering_container").disableSelection();
}


function bc_toggleOrderingListeners(toggle)
{
	if(toggle)
	{
		$('.bc_ordering_save').on('click', function()
		{
			bc_saveOrdering();
		});
		
		$('.bc_ordering_cancel').on('click', function()
		{
			window.location.href = bc_list_url;
		});
	}
	else
	{
		$('.bc_ordering_save').off('click');
		$('.bc_ordering_cancel').off('click');		
	}
}


function bc_saveOrdering()
{
	var elements = [];
	var ordering = 0;
	$('.bc_ordering_item').each(function()
	{
		elements.push
		( 
			{
				'id': $(this).attr('item_id'),
				'ordering': ordering,
			}
		);
		ordering++;
	});
	
	$.ajax(
	{
        url: bc_ordering_url,
        data: JSON.stringify(elements),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        dataType: "json",
        success: function(data) 
        {
        	if(data.success)
        	{
        		showMessage('success', data.message);
        	}
			else
			{
				showMessage('error', data.message);
			}
        }
    });	
}
