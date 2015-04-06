/**
 * Created by Thomas on 01.04.15.
 */

// jquery function to show/hide input fields for new property
jQuery(function ($) {
    $('.panel-heading').load(function (e) {
        if ($(this).hasClass('panel-collapsed')) {
            // collapse the panel
            $(this).parents('.slideOut').find('.panel-body').slideUp();
            $(this).parents('.slideOut').removeClass('panel panel-default');
            $(this).addClass('panel-collapsed btn btn-default btn-sm');
            $(this).find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');


        }
        else {
            // expand the panel
            $(this).parents('.slideOut').find('.panel-body').slideDown();
            $(this).parents('.slideOut').addClass('panel panel-default');
            $(this).removeClass('panel-collapsed btn btn-default btn-sm');
            $(this).find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
        }
    });
    $('.panel-heading').on("click", function (e) {
        if ($(this).hasClass('panel-collapsed')) {
            // expand the panel
            $(this).parents('.slideOut').find('.panel-body').slideDown();
            $(this).parents('.slideOut').addClass('panel panel-default');
            $(this).removeClass('panel-collapsed btn btn-default btn-sm');
            $(this).find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');

        }
        else {
            // collapse the panel
            $(this).parents('.slideOut').find('.panel-body').slideUp();
            $(this).parents('.slideOut').removeClass('panel panel-default');
            $(this).addClass('panel-collapsed btn btn-default btn-sm');
            $(this).find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
        }
    });
});


<!--Hide Flashmessage after 4000ms-->
jQuery(function($) {
    $(".flashmessage").fadeOut(4000, function() {
        setTimeout($(this).remove(), 1000)
    });
});


//$('.flashmessage').wrap('<div class="flashmessage"/>').contents().unwrap();
//$('.flashmessage li').parent().wrap('<div class="alert alert-success flashmessage"/>').contents().unwrap();
//$('.flashmessage li').wrap('<div class="flashmessage-ok"/>').contents().unwrap();


$('input[type="file"]').ezdz();
