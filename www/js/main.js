$(function(){

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
			setTimeout(function (){ $('.alert-success').fadeOut(3500); },1000);
		}
	});

	$.nette.ext('tagsmanager', {
		load: function () {

			var tagApi = $('.tm-input').tagsManager({});

			$('.tm-input').typeahead({
				name: 'countries',
				prefetch: '/homepage/countries'
			}).on('typeahead:selected', function (e,d) {
				tagApi.tagsManager("pushTag", d.value);
			});
		}
	})

	$.nette.ext('masonry', {
		load: function () {
			$('#container').masonry({
				columnWidth: '.item',
				gutter: 30,
				itemSelector: '.item'
			});
		}
	});

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

	$.nette.ext('jeditable', {
		load: function () {
			$('.editable').editable(function(value, settings) {
				var element = $(this);
				$.nette.ajax({
					url: element.data('handle'),
					data: {
						elementId: element.attr('id'),
						elementValue: value
					},
					// start: function (xhr, settings) {
					// 	var ico = element.prev('span').find('i');
					// 	ico.removeClass('icon-pencil');
					// 	ico.addClass('icon-upload');
					// }
				});
				return value;
			}, {
				// tooltip	: "Upraviť...",
				// submit	: 'OK',
				style  	: "inherit"
			});
		}
	});

	$.nette.ext('datepicker', {
		load: function() {
			$("input.deadline").datepicker();
		}
	});

	$.nette.ext('init').linkSelector = 'a.ajax';
	$.nette.init();


	$("input[type='file']").on('change', function () {
		$("input[name='upload[]']").each( function () {
			var fileName = $(this).val().split('/').pop().split('\\').pop();
			// console.log($(this));
			$(this).before('<p>' + fileName + '</p>');
		});
	});


});
