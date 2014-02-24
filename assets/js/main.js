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
			$('.custom-checkbox').iCheck({
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
		init: function (x, nette) {
			var self = this;
			self.originalUrl = window.location.href;
		},

		//	Volá se těsně před zahájením Ajaxového požadavku. Jako argumenty 
		//	dostává objekt jqXHR a objekt vlastností požadavku.
		start: function (jqXHR, settings, x) {
			this.doShowModal();
		},
		
		//	Volá se při úspěšném dokončení Ajaxového požadavku. 
		//	Ekvivalentní s $.ajax( ... ).done( ....
		success: function (payload, status, jqXHR, settings, x) {
			if (this.isModalOpened()) {
				this.changeCount -= 1;
			}

			var self = this;
			$(document).on('click', '#modal-window [data-dismiss="modal"]', function(e) {
			///$('#modal-window [data-dismiss="modal"]').click(function(e) {
				self.doCloseModal();
				history.replaceState(null, null, self.originalUrl);
				self.changeCount = 0;
				self.replacingContent('<p>Loading...</p>');
				e.preventDefault();
				return false;
			});
		},
		
	}, {
		//	Původní adresa, ze které se otevíralo okno.
		originalUrl: null,
		changeCount: 0,
		//	Vlastní otevření okna.
		doShowModal: function ()
		{
			$('#cbp-overlay')
				.addClass('show');
			$('body')
				.css('overflow', 'hidden');
			$('#modal-window')
				.addClass('modal-opened')
				.fadeIn();
		},
		//	Je okno otevřeno?
		isModalOpened: function ()
		{
			return $('#modal-window')
				.hasClass('modal-opened');
		},
		//	Replacing content
		doLoadContent: function (data)
		{
			for(var key in data.snippets) break;
			this.replacingContent(data.snippets[key]);
		},
		//	Nahrazení obsahu modalu
		replacingContent: function (s)
		{
			$('#modal-window .modal-body').html(s);
		},
		//	Vlastní zavření okna.
		doCloseModal: function ()
		{
			$('#cbp-overlay')
				.removeClass('show');
			$('body')
				.css('overflow', 'visible');
			$('#modal-window')
				.removeClass('modal-opened')
				.fadeOut();
		},
		//	Uložíme do historie
		pushState: function(url)
		{
			history.pushState({'url': url, changeCount: this.changeCount}, 
					null, 
					url);
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
	 * Initialize JS methods and AJAX
	 */
//	$.nette.ext('init').linkSelector = 'a.ajax';
	$.nette.ext('init').linkSelector = 'a[data-toggle="modal"]';
	
	
	$.nette.init();
	budget.init();

	$.nette.ext('snippets').getElement = function (id) {
		//~ console.log('snippet', id);
		return $('#' + this.escapeSelector(id));
		return $('#modal-window .modal-body');
	},
/*
*/

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


});
