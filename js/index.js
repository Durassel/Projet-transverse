// Index Navigation scroll
jQuery("document").ready(function($){
	var nav = $('.navbar');

	$(window).scroll(function () {
		if(window.innerWidth > 768) {
			if ($(this).scrollTop() > 0) {
				// Scroll
	    		$('.navbar').css({
		            'background': '#FFF',
		            'border-bottom': '1px solid #CCC',
		            'transition': 'background .3s ease'
	        	});
	        	$('.navbar .logo').css({
	        		'filter': 'brightness(35%)',
				    '-webkit-filter': 'brightness(35%)',
				    '-moz-filter': 'brightness(35%)',
				    '-o-filter': 'brightness(35%)',
				    '-ms-filter': 'brightness(35%)'
	        	});
	        	$('.nav>li>a, .navbar-header>a, .nav button').css({
	        		'color': '#3B3B3B',
	        		'transition': 'color .3s ease',
	        		'text-shadow': 'none',
	        		'transition': 'text-shadow .3s ease'
	        	});
			} else {
				// At the top
				$('.navbar').css({
		            'background': 'transparent',
		            'border-bottom-color': 'rgba(255,255,255,0.2)'
	        	});
	        	$('.navbar .logo').css({
	        		'filter': 'brightness(100%)',
				    '-webkit-filter': 'brightness(100%)',
				    '-moz-filter': 'brightness(100%)',
				    '-o-filter': 'brightness(100%)',
				    '-ms-filter': 'brightness(100%)'
	        	});
	        	$('.nav>li>a, .navbar-header>a, .nav button').css({
	        		'color': '#FFF',
	        		'text-shadow': '0 1px 2px rgba(0,0,0,.6)',
	        		'transition': 'text-shadow .3s ease'
	        	});
			}
		}
	});
});