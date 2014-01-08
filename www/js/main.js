$(document).ready(function() {


    $('input').iCheck({
        checkboxClass: 'icheckbox_default',
        radioClass: 'iradio_default',
        increaseArea: '20%'
    });
    $('.job input').iCheck({
        checkboxClass: 'icheckbox_large'
    });


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
//
//    $('.searchinput').typeahead({
//        name: 'search',
//        local: [
//            "text",
//            "lorem",
//            "dolor"
//        ]
//    });



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
//window.addEventListener('popstate', function(event) {
//    alert('popstate fired!');
//
//});


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



