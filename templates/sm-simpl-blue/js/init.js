 $('#navigation li').click(function() {
    $('#navigation li').addClass('selected');
});

$('.nav li > span').each(function() {
var $this = $(this);
$this.replaceWith('<a href="#" class="xhspan" onclick="return false" >' + $this.text() + '</a>');
});

/* height of sidebar */
$(document).ready(function() {
  $('#content').height($('#main-content').height());
});

$(document).ready(function() {
  $('#side1').height($('#content').height());
});
$(document).ready(function() {
  $('#side2').height($('#content').height());
});


$(function() {
	$('#main-menu').smartmenus({
		mainMenuSubOffsetX: -1,
		subMenusSubOffsetX: 10,
		subMenusSubOffsetY: 0
	});
});


// SmartMenus mobile menu toggle button
$(function() {
  var $mainMenuState = $('#main-menu-state');
  if ($mainMenuState.length) {
    // animate mobile menu
    $mainMenuState.change(function(e) {
      var $menu = $('#main-menu');
      if (this.checked) {
        $menu.hide().slideDown(250, function() { $menu.css('display', 'block'); });
      } else {
        $menu.show().slideUp(250, function() { $menu.css('display', 'none'); });
      }
    });
  // hide mobile menu beforeunload
    $(window).bind('beforeunload unload', function() {
      if ($mainMenuState[0].checked) {
        $mainMenuState[0].click(); 
      }
    });
	
	 }
});

/* $( "input[name*='man']" ).val( "has man in it!" ); */
$('[href^="#"]').addClass('cross').attr("onclick","return false");


/* ----------------- to top button ------------------*/

 (function() {
   			$('<i id="to-top"></i>').appendTo($('body'));

			$(window).scroll(function() {
				if($(this).scrollTop() > 200) {
					$('#to-top').fadeIn();	
				} else {
					$('#to-top').fadeOut();
				}
			});
			
			$('#to-top').click(function() {
				$('body,html').animate({scrollTop:0},100);
			});	

	})();					  
   
