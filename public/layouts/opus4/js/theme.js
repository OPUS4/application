$(document).ready(function() {
	// JavaScript detection
	$("html").removeClass("no-js").addClass("js");
	
	// normalize box-heights
    var max = 0;
        $('#adminMenuContainer ul > li > *').each(function(){  
			if($(this).height() > max){  
			max = $(this).height();  
        }
    });    
    $('#adminMenuContainer ul > li > *').height(max);

	// ActionBox
	var $actionbox = $('#actionboxContainer');
	var $actionboxSpacer = $('<div />', {
		"class": "actionbox-spacer",
		"height": $actionbox.outerHeight()
	});
	if ($actionbox.size())
	{
		$(window).scroll(function ()
		{
			if (!$actionbox.hasClass('fixed') && $(window).scrollTop() > $actionbox.offset().top)
			{
				$actionbox.before($actionboxSpacer);
				$actionbox.addClass("fixed");
			}
			else if ($actionbox.hasClass('fixed')  && $(window).scrollTop() < $actionboxSpacer.offset().top)
			{
				$actionbox.removeClass("fixed");
				$actionboxSpacer.remove();
			}
		});
	}
	
	// DropDown behaviour
	$(".dropdown > dt > a").click(function(event) {
		$(this).closest(".dropdown").toggleClass("dropdown-open");
		event.preventDefault();
	});
	$(".dropdown > dd a").click(function(event) {
		$(this).closest(".dropdown").toggleClass("dropdown-open");
		$('html,body').animate({scrollTop:$(this.hash).offset().top - ($actionbox.outerHeight() + 10)}, 0);
		event.preventDefault();
	});
	$(document).mouseup(function(event) {
		if ($(".dropdown-open") && !$(event.target).parents().hasClass("dropdown-open")) {
			$(".dropdown-open").removeClass("dropdown-open");
		}
	});
});
