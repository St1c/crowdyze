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
	// $.nette.ext('tagsmanager', {
	// 	load: function () {

	// 		var tagApi = $('.tm-input').tagsManager({
	// 			tagsContainer: $('#tags-container'),
	// 			prefilled: $('.tm-input').val()
	// 		});

	// 		// $('.tm-input').typeahead({
	// 		// 	name: 'countries',
	// 		// 	prefetch: '/homepage/countries'
	// 		// }).on('typeahead:selected', function (e,d) {
	// 		// 	tagApi.tagsManager("pushTag", d.value);
	// 		// });
	// 	}
	// });


	/**
	 * Responsive layout for tasks
	 */
	// $.nette.ext('masonry', {
	// 	load: function () {
	// 		$('#container').masonry({
	// 			columnWidth: '.item',
	// 			gutter: 30,
	// 			itemSelector: '.item'
	// 		});
	// 	}
	// });


	/**
	 * Infinite AJAX scroll
	 */
	// $.nette.ext('ias', {
	// 	load: function () {
	// 		jQuery.ias({
	// 			container : '#container',
	// 			item: '.item',
	// 			pagination: '.paginator',
	// 			next: '.next',
	// 			trigger: 'Show more',
	// 			loader: '<img src="/images/loader.gif"/>',
	// 			triggerPageThreshold: 2,
	// 			onLoadItems: function(items) {
	// 				// hide new items while they are loading
	// 				var $newElems = $(items).show().css({ opacity: 0 });
	// 				// ensure that images load before adding to masonry layout
	// 				$newElems.imagesLoaded(function(){
	// 					// show elems now they're ready
	// 					$newElems.animate({ opacity: 1 });
	// 					$('#container').masonry( 'appended', $newElems, true );
	// 				});
	// 				return true;
	// 			}
	// 		});
	// 	}
	// });

	/**
	 * Initialize JS methods and AJAX
	 */
	$.nette.ext('init').linkSelector = 'a.ajax';
	$.nette.init();
	budget.init();

	/*
	 
	 ! Toto znefunkčnilo checkboxy u mazání soborů.
	 
    $('input').iCheck({
        checkboxClass: 'icheckbox_default',
        radioClass: 'iradio_default',
        increaseArea: '20%'
    });
    $('.job input').iCheck({
        checkboxClass: 'icheckbox_large'
    });
	*/

    $('.job input').on('ifChecked', function() {
        $(this).closest('.job').addClass('job-checked');
    });

    $('.job input').on('ifUnchecked', function() {
        $(this).closest('.job').removeClass('job-checked');
    });

    $('select').customSelect();
    $('.jobs-grid').masonry({
        itemSelector: '.box-holder'
    });
    $('#temp-modal1').on('click', function(e) {
        openModal('#modal-1');
        e.preventDefault();
    });


    $('#temp-modal2').on('click', function(e) {
        openModal('#modal-2');
        e.preventDefault();
    });

    $('.modal-close').on('click', function(e) {
        e.preventDefault();
        modal = $(this).parent().parent();
        closeModal(modal);
    });


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


function openModal(modalId) {
    //skryje menu, docasna ukazka
    $('#showLeft').click();



    $(modalId).show();
    setTimeout(function() {
        $(modalId).addClass('modal-opened');
    }, 1);
    $('.modal-overlay').fadeIn();
    $('body').css('overflow', 'hidden');
    history.pushState({'id': 1}, 'Detail1', 'detail/1');
}

function closeModal(modalId) {
    $(modalId).removeClass('modal-opened');
    setTimeout(function() {
        $(modalId).hide();
    }, 200);
    $('.modal-overlay').fadeOut();
    $('body').css('overflow', 'visible');
    history.back();
}
