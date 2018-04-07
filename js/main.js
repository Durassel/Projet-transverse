// Delay Carousel
$('.carousel').carousel({
  interval: 5000
})

// Modal box
$('#loginModal').on('shown.bs.modal', function () {
  $('#myInput').focus()
})

// Scroll page
$('a.page-scroll').bind('click', function(event) {
    var $anchor = $(this);
    $('html, body').stop().animate({
        scrollTop: $($anchor.attr('href')).offset().top - 60
    }, 1500, 'easeInOutExpo');
    event.preventDefault();
});

// Gallerie
jQuery(document).ready(function($){
	var $active = false;

	$('.work').click(function(e) {
		e.preventDefault();
		var $work = $(this);

		if($work.hasClass('active')) {
			return true;
		}

		var $detail = $work.parent().nextAll('.row-detail:first');
		var $work_detail = $('.work-detail', $work).clone();

		$('.work').removeClass('active');
		$work.addClass('active');

		if($active) {
			var $el = $active;
			$el.slideUp(500, function(){
				$el.remove();
			});
		}

		$detail.append($work_detail);
		$work_detail.slideDown();
		$active = $work_detail;

		scrollTo($work);
	});

});

var scrollTo = function(cible) {
	$('html, body').animate({scrollTop: cible.offset().top}, 750);
}