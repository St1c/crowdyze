$(function(){

	/* Firefox fix to connect label with file input form  */
	// if($.browser.mozilla) {
	// 	$(document).on('click', 'label', function(e) {
	// 		if(e.currentTarget === this && e.target.nodeName !== 'INPUT') {
	// 		$(this.control).click();
	// 		}
	// 	});
	// }

	/**	
	* Remove _fid parameter from URL
	*/
	if(window.history.replaceState) {
		l = window.location.toString();
		uri = l.indexOf('_fid=');
		if(uri != -1) {
			uri = l.substr(0, uri)+l.substr(uri+10);
			if( (uri.substr(uri.length-1) == '?') || (uri.substr(uri.length-1) == '&') ) {
				uri = uri.substr(0, uri.length-1);
			}
			window.history.replaceState('', document.title, uri);
		}
	}

	// Automatic flash messages dismissal
	$.nette.ext('SuccessfullFlashHide', {
		load: function(){
			setTimeout(function (){ $('.alert').fadeOut(3500); },1000);
		}
	});


	// Twitter tagsmanager
	$.nette.ext('tagsmanager', {
		load: function () {

			var tagApi = $('.tm-input').tagsManager({
				tagsContainer: $('#tags-container')
			});

			$('.tm-input').typeahead({
				name: 'countries',
				prefetch: '/homepage/countries'
			}).on('typeahead:selected', function (e,d) {
				tagApi.tagsManager("pushTag", d.value);
			});
		}
	})


	// Responsive layout for tasks
	$.nette.ext('masonry', {
		load: function () {
			$('#container').masonry({
				columnWidth: '.item',
				gutter: 30,
				itemSelector: '.item'
			});
		}
	});


	// Infinite AJAX scroll
	$.nette.ext('ias', {
		load: function () {
			jQuery.ias({
				container : '#container',
				item: '.item',
				pagination: '.paginator',
				next: '.next',
				trigger: 'Show more',
				loader: '<img src="/images/loader.gif"/>',
				triggerPageThreshold: 2,
				onLoadItems: function(items) {
					// hide new items while they are loading
					var $newElems = $(items).show().css({ opacity: 0 });
					// ensure that images load before adding to masonry layout
					$newElems.imagesLoaded(function(){
						// show elems now they're ready
						$newElems.animate({ opacity: 1 });
						$('#container').masonry( 'appended', $newElems, true );
					});
					return true;
				}
			});
		}
	});

	$.nette.ext('datepicker', {
		load: function() {
			$("input.deadline").datepicker();
		}
	});

	$.nette.ext('budgetcalc', {
		load: function() {
			$('#salary, #workers').keyup(function() {
				calculateBudget();
			});

			$('#budget_type').change(function(evt) {
				calculateBudget();
			});
		}
	});

	$.nette.ext('init').linkSelector = 'a.ajax';
	$.nette.init();


	function calculateBudget() {
		var budgetType 	= $('#budget_type').val();
		var salary		= $('#salary').val();
		var workers 	= $('#workers');
		var finalPrice;

		switch (budgetType) 
		{
			case '1':
				// Pay only the best
				workers.prop('disabled', true);
				finalPrice = salary * 1.05 + 0.50;
				break;
			case '2':
				// Pay the best 10
				workers.prop('disabled', true);
				finalPrice = 10 * salary * 1.05 + 0.50;
				break;
			default:
				// Pay all
				workers.prop('disabled', false);
				finalPrice = workers.val() * salary * 1.05 + 0.50;
				break;
		}
		$('#budget .value').html(Math.round(finalPrice*100)/100);
	}

	// $("input[type='file']").on('change', function (evt) {
		
	// 	// Loop through the FileList and render image files as thumbnails.
	// 	var files = evt.target.files; // FileList object

	// 	// files is a FileList of File objects. List some properties.
	// 	var output = [];
	// 	for (var i = 0, f; f = files[i]; i++) {

	// 		output.push('<p>', escape(f.name), ' (', f.type || 'n/a', ') - ',
	// 					f.size, ' bytes</p>');
	// 	}
	// 	$(this).before('<div>' + output.join('') + '</div>');

	// });

	// token = $('input[name="token"]').val();

	// uploader = new qq.FileUploaderBasic({
	// 	// pass the dom node (ex. $(selector)[0] for jQuery users)
	// 	element: document.getElementById('upload'),
	// 	// path to server-side upload script
	// 	action: '/upload/temp/' + token,
	// 	debug: false,
	// 	button: document.getElementById('upload-btn'),  

	// 	onSubmit: function(id, fileName) {
	// 		$('#file-list').append('<div><span class="loader"></span><div>');
	// 	},
	//     onProgress: function(id, fileName, uploadedBytes, totalBytes) {

	//     	var el = $('#file-list div').eq(id).find('.loader');
	//     	var text;
	// 		var percent = Math.round(uploadedBytes / totalBytes * 100);

	//         if (uploadedBytes != totalBytes) {
	// 			// If still uploading, display percentage
	//             text = percent + '%';	
	//         } else {
	// 			// If complete, just display final size
	//             text = totalBytes;
	//         }
	//         el.html('<p>' + text + '</p>');
	//     },
	//     onComplete: function(id, fileName, responseJSON) {
	//     	var el = $('#file-list div').eq(id);

	//     	el.find('.loader').remove();
	//     	el.append('<span class="' + getType(fileName) + '">' + responseJSON.file + '</span>');
	//     	$('form').append('<input type="hidden" value="' + responseJSON.file + '" name="upload[]">')
	//     },
	//     onError: function(id, fileName, xhr){
	//     	console.log(xhr);
	//     }
	// });

	// getType = function(fileName) {

	// 	var images 	= ['jpg', 'JPG', 'png', 'PNG', 'bmp', 'gif'];
	// 	var video 	= ['avi', 'mp4', 'flv'];
	// 	var docs 	= ['xml', 'doc', 'xls', 'docx', 'xlsx'];

	// 	var ext = fileName.split('.').pop();

	// 	if (jQuery.inArray(ext, images) !== -1 ) {
	// 		return 'image';
	// 	} else if (jQuery.inArray(ext, video) !== -1 ) {
	// 		return 'video';
	// 	} else if (jQuery.inArray(ext, docs) !== -1 ) {
	// 		return 'docus';
	// 	} else {
	// 		return 'other';
	// 	}
	// }

});


