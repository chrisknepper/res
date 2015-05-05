jQuery.fn.exists = function () {
	return jQuery(this).length > 0;
}

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

Dropzone.autoDiscover = false;

jQuery(document).ready(function($) {

	//Parse page for new metabox order and send it via ajax to be saved in postmeta
	function updateMediasMetaboxOrder() {
		var order = {};
		//Get the metabox id and label for each regular, non-default metabox
		$('#normal-sortables div').each(function(index, element) {
			var elId = $(element).attr('id');
			if(elId !== 'postexcerpt' && elId !== 'slugdiv') {
				var title = $(element).children('.hndle').first().text();
				if(title) {
					order[elId] = title; //push it (real good) into our object
				}
			}
			
		});
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mediasGroupOrder',
				orderArray: order,
				postID: $('#post_ID').val()

			}
		}).success(function(data) {
			//console.log(data);
		});
	}

	//Override WP metabox drag-and-drop order callback with our own
	/*
	window.postboxes.save_order = function(){
		updateMediasMetaboxOrder();
	}
	*/

	$('#normal-sortables div > .hndle').off('click');
	$(document).on('click', '#normal-sortables div > .hndle', function(e) {
		var newText = window.prompt('Enter a new title', '');
		if(newText !== null && newText.length > 0) {
			$(this).text(newText);
			updateMediasMetaboxOrder();
		}
	});

	$(document).on('keyup', '.imageWidth', function() {
		$(this).parent().siblings('.twerkItOut').data('image-width', $(this).val());
	});

	$(document).on('keyup', '.imageHeight', function() {
		$(this).parent().siblings('.twerkItOut').data('image-height', $(this).val());
	});

	$('.mediasGroupType').on('click', function() {
		var mother = $('#postexcerpt').prev();
		var daughter = mother.clone();
		var innerMother = daughter.find('.inside table tr').first();
		var finalDestination = daughter.find('tbody');
		var type = $(this).data('type');
		var parsedResponse;
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'mediasGetNewMetabox',
				postID: $('#post_ID').val(),
				type: type

			}
		}).success(function(data) {
			parsedResponse = JSON.parse(data);
			if("table" in parsedResponse) {
				daughter.attr('id', parsedResponse.id);
				daughter.children('.hndle').first().text(parsedResponse.outerLabel);
				daughter.find('tbody').empty();
				for (var i = 0; i < parsedResponse.table.length; i++) {
					innerMother.find('td').first().html(parsedResponse.table[i]);
					innerMother.find('th').first().text(parsedResponse.innerLabels[i]);
					innerMother.clone().appendTo(finalDestination);
				};
				daughter.insertAfter(mother); //This should put it above the Excerpt metabox
				updateMediasMetaboxOrder();
				window.initMetaboxSortables();
				window.autosave(); //WordPress function, ajax equivalent of user clicking "Update" in editor UI
			}

		});
	});

	$('.addGroupButton').on('click', function(e) {
		e.preventDefault();
		$('.addGroupPicker').toggleClass('visible');
	});

	$('.twerkItOut').each(function(index) {
		var temp = $(this);
		var fieldName = temp.data('image-field');
		var imgElement = temp.find('.dropPreview').children('img');
		var formAction = temp.data('action');
		var postID = temp.data('post-id');
		var metaRow = temp.data('meta-row');
		var primary = temp.siblings().find('.primaryCheck').val();
		var imageHidden = temp.siblings().find('.imageHidden');
		temp.dropzone({
			paramName: 'image',
			url: ajaxurl,
			previewsContainer: '.dropPreviewImage',
			init: function() {
				this.on('sending', function(file, xhr, formData) {
					formData.append('imageWidth', temp.data('image-width'));
					formData.append('imageHeight', temp.data('image-height'));
					formData.append('action', formAction);
					formData.append('imageField', fieldName);
					formData.append('postID', postID);
					formData.append('metaRow', metaRow);
					formData.append('caption', temp.siblings().find('.caption').val());
					formData.append('primary', primary);
				});
			},
			accept: function(file, done) {
				done();
			},
			success: function(file, response) {
				console.log(response);
				var res = JSON.parse(response);
				if(res.success == true) {
					imgElement.prop('src', res.image);
					$(imageHidden).val(res.imageID);
				}
			}
		});
	});

	//Portfolio Dashboard Event Listeners
	if($('.repeatableMetaAdd').exists()) {
		$('.repeatableMetaAdd').on('click', function(e) {
			e.preventDefault();
			var fieldLocation = $(this).prev('.repeatableContainer');
			var newField = fieldLocation.clone(true); //Clone is maybe filled with data
			//So we clear the inputs in the clone
			$(newField).find('input').not('.repeatableImageAdd').val('').attr('name', function(index, name) {
				return name.replace(/(\d+)/, function(fullMatch, n) {
					return Number(n) + 1; //And add 1 to the array index, stored in the name attribute
				});
			});
			$(newField).find('img').attr('src', '');
			newField.insertAfter(fieldLocation, $(this));
		});	
	}

	if($('.repeatableMetaRemove').exists()) {
		$('.repeatableMetaRemove').on('click', function(e) {
			e.preventDefault();
			var fieldLocation = $(this).parent();
			if(fieldLocation.prev('.repeatableContainer').length > 0 || fieldLocation.next('.repeatableContainer').length > 0) { //Only remove if there's another of this kind left
				fieldLocation.empty().remove();
			}
			else { //Otherwise empty this one
				fieldLocation.find('input').not('.repeatableImageAdd').val('');
				fieldLocation.find('img').prop('src', '');
			}
		});
	}

	if($('.dropUpload').exists()) {
		$('.dropUpload').on('change', function() {
			console.log('we should upload that');
		});
	}

	//Only one primary image can be set, so uncheck all other primary image checkboxes when one is checked
	$('.primaryCheck').on('change', function() {
		$('.primaryCheck').not(this).prop('checked', false);
	});

	$('.repeatableImageRemove').click(function(e) {
		e.preventDefault();
		var fieldLocation = $(this).parent();
		if(fieldLocation.prev('.repeatableContainer').length > 0 || fieldLocation.next('.repeatableContainer').length > 0) { //Only remove if there's another of this kind left
				fieldLocation.empty().remove();
			}
			else { //Otherwise empty this one
				fieldLocation.find('input').not('.repeatableImageAdd').val('');
			}
	});

	$('.repeatableImageAdd').click(function(e) {
		e.preventDefault();
		var fieldLocation = $(this).prev('.repeatableContainer');
		var newField = fieldLocation.clone(true);
		$(newField).find('input').attr('name', function(index, name) {
			return name.replace(/(\d+)/, function(fullMatch, n) {
				return Number(n) + 1; //And add 1 to the array index, stored in the name attribute
			});
		});
		var dropzoneDiv = $(newField).children('.twerkItOut');
		var fieldName = $(dropzoneDiv).data('image-field');
		var imgElement = $(newField).find('.dropPreview').children('img');
		var formAction = $(dropzoneDiv).data('action');
		var postID = $(dropzoneDiv).data('post-id');
		var metaRow = parseInt($(dropzoneDiv).data('meta-row')) + 1;
		$(dropzoneDiv).data('meta-row', metaRow);
		var caption = $(newField).find('.caption').val();
		var primary = $(newField).find('.primary_image').val();
		var imageHidden = $(dropzoneDiv).siblings().find('.imageHidden');
		$(dropzoneDiv).dropzone({
			paramName: 'image',
			url: ajaxurl,
			previewsContainer: '.dropPreviewImage',
			init: function() {
				this.on('sending', function(file, xhr, formData) {
					formData.append('imageWidth', $(dropzoneDiv).data('image-width'));
					formData.append('imageHeight', $(dropzoneDiv).data('image-height'));
					formData.append('action', formAction);
					formData.append('imageField', fieldName);
					formData.append('postID', postID);
					formData.append('metaRow', metaRow);
					formData.append('caption', caption);
					formData.append('primary', primary);
				});
			},
			accept: function(file, done) {
				done();
			},
			success: function(file, response) {
				console.log(response);
				var res = JSON.parse(response);
				if(res.success == true) {
					imgElement.prop('src', res.image);
					$(imageHidden).val(res.imageID);
				}
			}
		});
		newField.insertAfter(fieldLocation, $(this));
	});

});
