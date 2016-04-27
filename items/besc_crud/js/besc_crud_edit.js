var bc_and_go_back = false;


$(document).ready(function()
{	
	if(bc_edit_or_add == 0)
		bc_bindAddListeners();
	else
		bc_bindEditListeners();
	
	bc_addMNRelationListeners();
	bc_addImageListeners();
	//bc_addMultilineListeners();
	bc_addDatepickerListeners();
	bc_addComboboxListeners();
	bc_positionMNRelationSearchbox();
	bc_addFileListeners();
	bc_igniteCKEditor();
	bc_igniteColorpicker();
	
});

function bc_igniteColorpicker()
{
	$('.bc_col_colorpicker input').each(function()
	{
		$(this).spectrum(
		{
			preferredFormat: "hex",
			showInput: $(this).attr('hexinput') == "1",
		});
	});
}


function bc_igniteCKEditor()
{
	$('.bc_ck_editor').each(function()
	{
		CKEDITOR.config.height = $(this).css('height');
		CKEDITOR.replace($(this).attr('name'));
	});
}


function bc_addComboboxListeners()
{
	(function( $ ) {
	    $.widget( "custom.combobox", {
	      _create: function() {
	        this.wrapper = $( "<span>" )
	          .addClass( "custom-combobox" )
	          .insertAfter( this.element );
	 
	        this.element.hide();
	        this._createAutocomplete();
	        this._createShowAllButton();
	      },
	 
	      _createAutocomplete: function() {
	        var selected = this.element.children( ":selected" ),
	          value = selected.val() ? selected.text() : "";
	 
	        this.input = $( "<input>" )
	          .appendTo( this.wrapper )
	          .val( value )
	          .attr( "title", "" )
	          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
	          .autocomplete({
	            delay: 0,
	            minLength: 0,
	            source: $.proxy( this, "_source" )
	          })
	          .tooltip({
	            //tooltipClass: "ui-state-highlight"
	          });
	 
	        this._on( this.input, {
	          autocompleteselect: function( event, ui ) {
	            ui.item.option.selected = true;
	            this._trigger( "select", event, {
	              item: ui.item.option
	            });
	          },
	 
	          autocompletechange: "_removeIfInvalid"
	        });
	      },
	 
	      _createShowAllButton: function() {
	        var input = this.input,
	          wasOpen = false;
	 
	        $( "<a>" )
	          .attr( "tabIndex", -1 )
	          .attr( "title", "Show All Items" )
	          //.tooltip()
	          .appendTo( this.wrapper )
	          .button({
	            icons: {
	              primary: "ui-icon-triangle-1-s"
	            },
	            text: false
	          })
	          .removeClass( "ui-corner-all" )
	          .addClass( "custom-combobox-toggle ui-corner-right" )
	          .mousedown(function() {
	            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
	          })
	          .click(function() {
	        	input.focus();
	 
	            // Close if already visible
	            if ( wasOpen ) {
	              return;
	            }
	 
	            // Pass empty string as value to search for, displaying all results
	            input.autocomplete( "search", "" );
	            $('.ui-autocomplete').css({'width': 500, 'max-height': $(window).height() * 0.5, 'overflow-y': 'auto'});
	          });
	      },
	 
	      _source: function( request, response ) {
	        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
	        response( this.element.children( "option" ).map(function() {
	          var text = $( this ).text();
	          if ( this.value && ( !request.term || matcher.test(text) ) )
	            return {
	              label: text,
	              value: text,
	              option: this
	            };
	        }) );
	        $('.ui-autocomplete').css({'width': 500, 'max-height': $(window).height() * 0.5, 'overflow-y': 'auto'});
	      },
	 
	      _removeIfInvalid: function( event, ui ) {
	 
	        // Selected an item, nothing to do
	        if ( ui.item ) {
	          return;
	        }
	 
	        // Search for a match (case-insensitive)
	        var value = this.input.val(),
	          valueLowerCase = value.toLowerCase(),
	          valid = false;
	        this.element.children( "option" ).each(function() {
	          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
	            this.selected = valid = true;
	            return false;
	          }
	        });
	 
	        // Found a match, nothing to do
	        if ( valid ) {
	          return;
	        }
	 
	        // Remove invalid value
	        this.input
	          .val( "" )
	          .attr( "title", value + " didn't match any item" )
	          /*.tooltip( "open" )*/;
	        this.element.val( '' );
	        this._delay(function() {
	          this.input.tooltip( "close" ).attr( "title", "" );
	        }, 2500 );
	        this.input.autocomplete( "instance" ).term = "";
	      },
	 
	      _destroy: function() {
	        this.wrapper.remove();
	        this.element.show();
	      }
	    });
	  })( jQuery );
	 
	$('.bc_combobox').combobox();
}


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
		bc_and_go_back = true;
		bc_edit();
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
        url: bc_validation_url + 'null',
        data: JSON.stringify(bc_getData()),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        dataType: "json",
        success: function(data) 
        {
        	if(!data.success)
        	{
        		bc_validate_errors(data);
        	}
			else
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
        }
    });	
}

function bc_edit()
{
	$.ajax(
	{
        url: bc_validation_url + bc_pk_value,
        data: JSON.stringify(bc_getData()),
        contentType: "application/json; charset=utf-8",
        type: "POST",
        dataType: "json",
        success: function(data) 
        {
        	if(!data.success)
        	{
        		bc_validate_errors(data);
        	}
			else
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
	
	// colorpicker
	$('.bc_col_colorpicker').each(function()
	{
		$(this).find('input').val('#000000');
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
				'name': $(this).attr('m_n_relation_id'),
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
	
	// date
	$('.bc_edit_table').find('.bc_col_date').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('input').attr('name').replace('col_', ''),
				'value': $(this).find('input').val() == '' ? 'null' : $(this).find('input').val(),
				'type': 'date'
			}
		);
	});		
	
	// combobox
	$('.bc_edit_table').find('.bc_col_combobox').each(function()
	{
		elements.push
		(
			{
				'name': $(this).find('select').attr('name').replace('col_', ''),
				'value': $(this).find('select option:selected').attr('value'),
				'type': 'combobox'		
			}
		);
	});
	
	// CKEDITOR
	$('.bc_edit_table').find('.bc_col_ckeditor').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('textarea').attr('name').replace('col_', ''),
				'value': CKEDITOR.instances[$(this).find('textarea').attr('name')].getData(),
				'type': 'ckeditor'
			}
		);		
	});
	
	// colorpicker
	$('.bc_edit_table').find('.bc_col_colorpicker').each(function()
	{
		elements.push
		( 
			{
				'name': $(this).find('input').attr('name').replace('col_', ''),
				'value': $(this).find('input').val(),
				'type': 'colorpicker'
			}
		);
	});
	
	return elements;		
}


function bc_addDatepickerListeners()
{
	$('.bc_col_date').each(function()
	{
		$(this).find('input').datepicker(
		{
			dateFormat: $(this).find('input').attr('format'),
			changeMonth: true,
	        changeYear: true,
		});
		
		$(this).find('.bc_col_date_calendar').click(function()
		{
			$(this).parent().find('input').focus();
		});
		
		$(this).find('.bc_col_date_reset').click(function()
		{
			$(this).parent().find('input').datepicker('setDate', null);
		});
	});
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
	
	$('.bc_m_n_filterbox input[type="text"]').keyup(function()
	{
		bc_MNRelationFilter($(this).val(), $(this).parent().attr('parent'));
	});
}

function bc_MNRelationFilter(filter, parent)
{
	$('.bc_m_n_' + parent).each(function()
	{
		if($(this).text().toUpperCase().indexOf(filter.toUpperCase()) == -1)
			$(this).hide();
		else
			$(this).show();
	});
}

function bc_positionMNRelationSearchbox()
{
	$('.bc_m_n_filterbox input[type="text"]').css({'width': '-=18px'});
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


function bc_addFileListeners()
{
	$('.bc_col_file_upload_btn').click(function()
	{
		$(this).parent().find('input[type="file"]').click();
	});
	
	$('.bc_col_file_file').change(function()
	{
		bc_uploadFile($(this).attr('id'), $(this).attr('uploadpath'), this.files);
	});		
	
	$('.bc_col_file_delete').click(function()
	{
		bc_resetUpload($(this).parent());
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
		bc_uploadFile($(this).attr('id'), $(this).attr('uploadpath'), this.files);
	});		
	
	$('.bc_col_image_delete').click(function()
	{
		bc_resetUpload($(this).parent());
	});
}

function bc_uploadFile(element, u, files)
{
	var elem = $('#' + element);
	var element_name = elem.attr('name').substr(4, elem.attr('name').length -9);
	var uploadpath = u;
	var xhr = new XMLHttpRequest();		
	var fd = new FormData;
	fd.append('data', files[0]);
	fd.append('filename', files[0].name);
	fd.append('element', element_name);
	
	xhr.addEventListener('load', function(e) 
	{
		var ret = $.parseJSON(this.responseText);
		
		if(ret.success)
		{
			var col = $('#' + element).parent();
			
			if(col.attr('callback_after_upload') !== undefined)
			{
				window[col.attr('callback_after_upload')](ret.filename, uploadpath, element, result);
			}
			
			if(ret.crop != null)
			{
				bc_cropUpload(ret.filename, uploadpath, element, element_name, ret.crop);
			}
			else
				bc_updateImageElement(col, uploadpath, ret.filename);
		}
		else
		{
			show_message('error', 'Error while uploading!');
		}
    });
	
	xhr.open('post', bc_upload_url);
	xhr.send(fd);
}

function bc_updateImageElement(col, uploadpath, filename)
{
	
	col.find('.bc_col_image_upload_btn').fadeOut(150, function()
	{
		col.find('.bc_col_image_preview').attr('src', rootUrl + '/' + uploadpath + filename);
		col.find('a').attr('href', rootUrl + '/' + uploadpath + '/' + filename);
		col.find('.bc_col_image_preview').fadeIn(150);
		col.find('.bc_col_image_delete').fadeIn(150);
		col.find('.bc_col_fname').val(filename);						
	});
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

/*function bc_addMultilineListeners()
{
	$('.bc_col_multiline_formatting_button').click(function()
	{
		addTags($(this).parent().parent(), $(this).attr('tag'));
	});	
}

function addTags(multiline, tag)
{
	var ta = multiline.find('textarea');
	
	var sel = ta.getSelection();
	var text = ta.val();
	if(sel.start == sel.end)
	{
		var before = text.substring(0, sel.start);
		var after = text.substring(sel.start, text.length);
		
		var newtext = before + '<' + tag + '></' + tag + '>' + after;
	}
	else
	{
		var before = text.substring(0, sel.start);
		var after = text.substring(sel.end, text.length);
		var selection = text.substring(sel.start, sel.start + sel.length);
		var newtext = before + '<' + tag + '>' + selection + '</' + tag + '>' + after;
	}

	ta.val(newtext);
}*/

function bc_cropUpload(filename, uploadpath, element, elementname, cropoptions)
{
	var html = '<div id="bc_upload_crop"><img id="bc_upload_crop_img" src="' + rootUrl + uploadpath + filename + '" /><div id="bc_upload_crop_btn">CROP</div></div><div id="bc_upload_crop_fade"></div>';
	$('body').append(html);
	
	var wWidth = $(document).width();
	var wHeight = $(document).height();
	var padding = 60;
	
	imagesLoaded($('#bc_upload_crop'), function()
	{
		var iWidth = $('#bc_upload_crop_img').get(0).naturalWidth;
		var iHeight = $('#bc_upload_crop_img').get(0).naturalHeight;
		var ratio = iWidth / iHeight;
		var cWidth = iWidth + padding;
		var cHeight = iHeight + padding + $('bc_#upload_crop_btn').height();
		
		if(cWidth > wWidth * 0.8)
		{
			iWidth = wWidth * 0.8 - padding;
			iHeight = iWidth / ratio;
			cWidth = iWidth + padding;
			cHeight = iHeight + padding;
		}
		
		if(cHeight > wHeight * 0.8)
		{
			iHeight = wHeight * 0.8 - padding - $('#bc_upload_crop_btn').height();
			iWidth = iHeight * ratio;
			cWidth = iWidth + padding;
			cHeight = iHeight + padding;			
		}
		
		$('#bc_upload_crop').css({'left': (wWidth - cWidth)/2, 'top': (wHeight - cHeight)/2});
		$('#bc_upload_crop_img').css({'width': iWidth, 'height': iHeight});
		
		var select_ratio = $('#bc_upload_crop_img').get(0).naturalWidth / parseInt($('#bc_upload_crop_img').css('width'));
		
		areaselect = $('#bc_upload_crop_img').imgAreaSelect(
		{ 
			aspectRatio: cropoptions.ratio, 
			handles: true,
			x1: 0,
			y1: 0,
			x2: parseInt(cropoptions.minWidth) / select_ratio,
			y2: parseInt(cropoptions.minHeight) / select_ratio,
			minWidth: parseInt(cropoptions.minWidth) / select_ratio,
			minHeight: parseInt(cropoptions.minHeight) / select_ratio,
			parent: '#bc_upload_crop',
			instance: true,
		});

	});		

	$('#bc_upload_crop_btn').on('click', function()
	{
		$.ajax(
		{
			url: bc_crop_url,
			data: { 
				filename: filename, 
				x1: areaselect.getSelection().x1, 
				y1: areaselect.getSelection().y1, 
				x2: areaselect.getSelection().x2, 
				y2: areaselect.getSelection().y2, 
				'col': elementname,
				'ratio': $('#bc_upload_crop_img').get(0).naturalWidth / parseInt($('#bc_upload_crop_img').css('width')),
			},
			method: 'POST',
			cache: false,
			dataType: 'json',
			success: function(data)
			{
				var ret = data;
				
				if(ret.success)
				{
					$('#bc_upload_crop_fade').remove();
					$('#bc_upload_crop').remove();
					
					bc_updateImageElement($('#' + element).parent(), uploadpath, filename);
				}
				else
				{
					alert('Error while cropping');
				}
			}
		});
	});
	
	$('#bc_upload_crop_fade').on('click', function()
	{
		$('#bc_upload_crop_fade').remove();
		$('#bc_upload_crop').remove();
		bc_updateImageElement($('#' + element).parent(), uploadpath, filename);
	});
}


function bc_validate_errors(data)
{
	var error_color = getCSS('background-color', 'bc_error_highlight');
	var scroll_to = 99999;
	
	for (var key in data.error_columns)
	{
		var new_scroll_to = bc_validate_error(data.error_columns[key], error_color);
		if(new_scroll_to < scroll_to)
			scroll_to = new_scroll_to;
	}
	
	$(".bc_edit_table").parent().scrollTop(scroll_to);
}

function bc_validate_error(data, error_color)
{
	var col = $('.bc_column[col_name="' + data.name + '"]');
	col.animate({'background-color': error_color}, 150, "swing", function()
	{
		col.find('.bc_error_text').text(data.error);
		col.find('input').css({'border': 'solid 2px ' + error_color});
		col.find('textarea').css({'border': 'solid 2px ' + error_color});
		col.find('select').css({'border': 'solid 2px ' + error_color});
		setTimeout(function()
		{
			col.animate({'background-color': ''}, 150, "swing");
		}, 100);
	});
	
	return Math.abs(parseInt(col.position().top));
}

function getCSS(prop, fromClass) 
{
    var $inspector = $("<div>").css('display', 'none').addClass(fromClass);
    $("body").append($inspector); // add to DOM, in order to read the CSS property
    try {
        return $inspector.css(prop);
    } finally {
        $inspector.remove(); // and remove from DOM
    }
}



function setTimeoutMessageDisappear(message)
{
	setTimeout(function()
	{
		message.click();
	}, bc_message_lingering);
}