$(function(){

	/**
	 * Firefox fix to connect label with file input form
	 */
	// if($.browser.mozilla) {
	// 	$(document).on('click', 'label', function(e) {
	// 		if(e.currentTarget === this && e.target.nodeName !== 'INPUT') {
	// 		$(this.control).click();
	// 		}
	// 	});
	// }

	/**
	 * Calculate task costs and inform the user about it
	 */
	var budget = budget || {
		
		/** 
		 * Commission Fees SETUP
		 * @todo  get values from config.neon 
		 */
		fees: {
			fixFee: 0.50,
			commissionFee: 0.05,
			promotionsFee: [0.03, 0.05, 0.07],
		},

		/** Input values */
		budgetType: function () { return $('#budget_type option:selected').val(); },
		salary: 	function () { return $('#salary').val(); },
		promotion: 	function () { return $('input[name=promotion]:checked', '#promotion').val(); },
		workers: 	function () { return $('#workers').val(); },		


		/** Budget methods */
		getNettoBudget: function () {

			switch (budget.budgetType()) 
			{
				// Pay only the best
				case '1':
					finalPrice = 1 * budget.salary();
					break;

				// Pay the best 10
				case '2':
					finalPrice = 10 * budget.salary();
					break;

				// Pay all
				default:
					finalPrice = budget.workers() * budget.salary();
					break;
			}

			return finalPrice;
		},

		/** Calculate commission costs */
		getCommission: function () {
			return budget.getNettoBudget() * budget.fees.commissionFee;
		},

		/** Calculate promotion costs */
		getPromotion: function () {
			if (budget.promotion() > 0) {
				return budget.getNettoBudget() * budget.fees.promotionsFee[budget.promotion() - 1];				
			} else {
				return 0;
			}
		},

		/** Calculate final budget costs */
		calculateBudget: function () {
			budget.finalPrice =  budget.getNettoBudget() 
								+ budget.getCommission() 
								+ budget.getPromotion() 
								+ budget.fees.fixFee;
			// $('#workers').prop('disabled', false);
			$('#budget .value').html( Math.round( budget.finalPrice * 100 ) / 100 );
		},

		init: function() {

			// Calulate on load
			budget.calculateBudget();

			// Calculate budget on typing
			$('#salary, #workers').keyup(function() {
				budget.calculateBudget();
			});

			// Calculate on budget_type change
			$('#budget_type').change(function(evt) {
				budget.calculateBudget();
			});

			// Calculate budget on promotions change
			$('#promotion input').change(function(evt) {
				budget.calculateBudget();
			});
		}
	};

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

	/** 
	 * Automatic flash messages dismissal
	 */
	$.nette.ext('SuccessfullFlashHide', {
		load: function(){
			setTimeout(function (){ $('.alert').fadeOut(3500); },1000);
		}
	});


	/**
	 * Twitter tagsmanager
	 */
	$.nette.ext('tagsmanager', {
		load: function () {

			var tagApi = $('.tm-input').tagsManager({
				tagsContainer: $('#tags-container'),
				prefilled: $('.tm-input').val()
			});

			// $('.tm-input').typeahead({
			// 	name: 'countries',
			// 	prefetch: '/homepage/countries'
			// }).on('typeahead:selected', function (e,d) {
			// 	tagApi.tagsManager("pushTag", d.value);
			// });
		}
	});


	/**
	 * Responsive layout for tasks
	 */
	$.nette.ext('masonry', {
		load: function () {
			$('.jobs-grid').masonry({
				itemSelector: '.box-holder'
			});
		}
	});


	/**
	 * Infinite AJAX scroll
	 */
	$.nette.ext('ias', {
		load: function () {
			jQuery.ias({
				container : '#jobs-table',
				item: '.job-row',
				pagination: '.paginator',
				next: '.next',
				trigger: 'Show more',
				loader: '<div class="preloader"></div>',
				triggerPageThreshold: 2,
				onRenderComplete: function(items) {
					// hide new items while they are loading
					var $newElems = $(items).show().css({ opacity: 0 });
					var $checkBox = $('.job-checkbox');

					$checkBox.iCheck({
						checkboxClass: 'icheckbox_large'
					});	

					$checkBox.on('ifChecked', function() {
						$(this).closest('.job').addClass('job-checked');
					});

					$checkBox.on('ifUnchecked', function() {
						$(this).closest('.job').removeClass('job-checked');
					});

					// show elems now they're ready
					$newElems.animate({ opacity: 1 });
					return true;
				}
			});
		}
	});


	/**
	 * Custom checkbox
	 * @author  kodujeme.sk
	 * ! Toto znefunkčnilo checkboxy u mazání soborů.
	 */
	$.nette.ext('customCheck', {
		load: function () {

			// default checkboxex
			$('.custom-checkbox, input[type="radio"]').iCheck({
				checkboxClass: 'icheckbox_default',
				radioClass: 'iradio_default',
				increaseArea: '20%'
			});

			// Job overview checkboxes
			$('.job-checkbox').iCheck({
				checkboxClass: 'icheckbox_large'
			});


			$('.job-checkbox').on('ifChecked', function() {
				$(this).closest('.job').addClass('job-checked');
			});

			$('.job-checkbox').on('ifUnchecked', function() {
				$(this).closest('.job').removeClass('job-checked');
			});

			$('.custom-select').customSelect();
		}
	});


	/**
	 * Modal window behaviour. Loading from nette snippets.
	 * @author  Taco
	 */
	$.nette.ext('modals', {
		load: function (x, nette) {

			//	Původní adresa, ze které se otevíralo okno.
			var originalUrl;
			var originalHistory;
			var changeCount = 0;
			var deep = 0;
			
			
			//	Je okno otevřeno?
			function isModalOpened()
			{
				return $('#modal-window')
					.hasClass('modal-opened');
			}


			//	Vlastní otevření okna.
			function doShowModal()
			{
				$('#cbp-overlay')
					.addClass('show');
				$('body')
					.css('overflow', 'hidden');
				$('#modal-window')
					.addClass('modal-opened')
					.fadeIn();
			}


			//	Vlastní zavření okna.
			function doCloseModal()
			{
				$('#cbp-overlay')
					.removeClass('show');
				$('body')
					.css('overflow', 'visible');
				$('#modal-window')
					.removeClass('modal-opened')
					.fadeOut();
			}


			//	Nahrazení obsahu modalu
			function replacingContent(s)
			{
				$('#modal-window .modal-body').html(s);
			}


			//	Nahrání obsahu
			function doLoadContent(url)
			{
				$.ajax({ url: url })
					.done(function(data, status, xhr) {
						try {
							//	Replacing content
							for(var key in data.snippets) break;
							replacingContent(data.snippets[key]);
						}
						catch (e) {
							doCloseModal();
							//	@FIXME
							history.go(changeCount);
							changeCount = 0;
						}
					});
			}
			

			//	Handler pro otevření okna.
			function onOpenModal(e) 
			{
				if (! isModalOpened()) {
					originalUrl = window.location.href;
					doShowModal();
					originalHistory = window.history;
					deep += 1;
				}
				doLoadContent($(this).attr('href'));
				changeCount -= 1;
				history.pushState({'url': $(this).attr('href'), deep: deep, changeCount: changeCount}, null, $(this).attr('href'));
				e.preventDefault();
				return false;
			}


			//	Handler pro zavření okna
			function onCloseModal(e)
			{
				doCloseModal();
				history.go(changeCount);
				changeCount = 0;
				replacingContent('<p>Loading...</p>');
				e.preventDefault();
				return false;
			}

			
			//	Odkazy mající modal
			$(document).on('click', 'a[data-toggle="modal"]', onOpenModal);
			$(document).on('click', '#modal-window [data-dismiss="modal"]', onCloseModal);

			//	Aktualizace stránky během historie.
			window.onpopstate = function(e) {
				if (e.state) {
					if (isModalOpened()) {
						doLoadContent(e.state.url)
						changeCount = e.state.changeCount;
						deep = e.state.deep;
					}
					else {
						window.location.href = e.state.url;
					}
				}
				else {
					if (isModalOpened()) {
						doCloseModal();
					}
				}
			};


		}
	});




	/**
	 * Searchbox routines
	 * @author  kodujeme.sk
	 */
	$.nette.ext('searchbox', {
		load: function () {
			$('.searchinput-holder').on('click', function(e) {
				$(this).addClass('searchinput-expanded');
			});
			$('.searchinput').on('blur', function(e) {

				if ($(this).val() === "") {
					$(this).closest('.searchinput-holder').removeClass('searchinput-expanded');
				}
			});


			$('.menu-search input').on('click', function(e) {
				$(this).addClass('nobgr');
			});
			$('.menu-search input').on('blur', function(e) {

				if ($(this).val() === "") {
					$(this).removeClass('nobgr');
				}
			});
		}
	});


	/**
	 * Custom checkbox
	 * @author  kodujeme.sk
	 * ! Toto znefunkčnilo checkboxy u mazání soborů.
	 */
	// $.nette.ext('autoGrowInput', {
	// 	load: function () {
	// 		$('input[name="title"]').autoGrowInput({
	// 			comfortZone: 50,
	// 			// minWidth: 20,
	// 			// maxWidth: 2000
	// 		});
	// 	}
	// });

	/**
	 * Datepicker
	 * @author  crowdyze.me
	 */
	$.nette.ext('datepicker', {
		load: function () {
			var	picker = new Pikaday({ 
				field: $('.input-date')[0],
				format: 'DD/MM/YY',
				position: 'Bottom right'
			});
		}
	});


	/**
	 * Initialize JS methods and AJAX
	 */
	$.nette.ext('init').linkSelector = 'a.ajax';
	$.nette.init();
	budget.init();


    $('#showLeft').on('click', function(e) {
        $(this).toggleClass('active');
        $('#cbp-spmenu-s1').toggleClass('cbp-spmenu-open');
        $('#cbp-overlay').toggleClass('show');
        if ($(this).hasClass('active')) {
            $('body').css('overflow', 'hidden');
        } else {
            $('body').css('overflow', 'visible');
        }

    });


    $('.filter-close, .cbp-overlay').on('click', function(e) {
        $('#showLeft').click();
    });

	$(".loader").css('background-image', 'none').fadeOut(1000);

});
