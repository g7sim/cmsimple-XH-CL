$("ul.menulevel1").addClass("nav navbar-nav");

$("ul.menulevel2").addClass("dropdown-menu"); 
$("ul.menulevel3").addClass("dropdown-menu"); 
$("ul.menulevel4").addClass("dropdown-menu"); 
$("ul.menulevel5").addClass("dropdown-menu"); 
$("ul.menulevel6").addClass("dropdown-menu"); 
$("ul.menulevel7").addClass("dropdown-menu"); 
$("ul.menulevel8").addClass("dropdown-menu"); 
$("ul.menulevel9").addClass("dropdown-menu"); 

/* $('.nav li > span').each(function() {
var $this = $(this);
$this.replaceWith('<a class="has-submenu xhspan" href="#">' + $this.text() + '</a>');
}); -- removed */

$(document).ready(function(){
    $("#myaff").affix({
        offset: { 
            top: $(".header").outerHeight(true)  /* Set top offset equal to header outer height including margin */
        }
    });
});
 	  
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
   
$("span[data-toggle=popover]").popover()  
   




