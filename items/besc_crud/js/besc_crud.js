var bc_message_phasing = 200;
var bc_message_id = 0;

function showMessage(type, message)
{
	switch(type)
	{
		case 'error':
			var bg_class = "bc_message_error";
			break;
		case 'success':
			var bg_class = "bc_message_success";
			break;
	}
	
	/*$('.bc_message_container').prepend('<div id="bc_message_' + bc_message_id + '" class="bc_message ' + bg_class + '">');
	$('#bc_message_' + bc_message_id).animate({'line-height': 40}, bc_message_phasing, function()
	{
		
	});*/
}
