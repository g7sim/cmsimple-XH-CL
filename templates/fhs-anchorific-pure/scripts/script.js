jQuery(document).ready(function ($) {
	function scrollbar_width() {
		if (jQuery('body').height() > jQuery(window).height()) {
			var calculation_content = jQuery('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>');
			jQuery('body').append(calculation_content);
			var width_one = jQuery('div', calculation_content).innerWidth();
			calculation_content.css('overflow-y', 'scroll');
			var width_two = jQuery('div', calculation_content).innerWidth();
			jQuery(calculation_content).remove();
			return (width_one - width_two);
		}
		return 0;
	}
	mobNavHeight = function () {
		if ($(window).innerWidth() + scrollbar_width() <= 980) {
			var f = $('#footer').innerHeight();
			var h = $('#header').innerHeight();
			var w = $('#wrapper').innerHeight();
			var pad = 15;
			var gesamtHoehe = (w + f + h + 7);
			var myTop = (h);
			$('.nav_horizontal').css({
				'height': gesamtHoehe + 'px',
				'top': myTop + "px"
			});
			$('#wrapper').css({
				'top': myTop + "px",
				'padding': "0 0 " + pad + "em 0"
			});
		} else {
			var x = $('ul.menulevel1').height();
			$('.nav_horizontal').css({
				'height': x + 'px',
				'top': '0px'
			});
			$('#wrapper').css({
				'padding-top': '0px',
				'top': '0px'
			});
			$('.nav_horizontal').show();
		}
	}
	mobNavHeight();
	$(window).resize(function () {
		scrollbar_width();
		mobNavHeight();
	});
	$('.burger').click(function () {
		$('.nav_horizontal').fadeToggle();
		$('.burger i').toggleClass('fa-bars fa-close');
	});
});
