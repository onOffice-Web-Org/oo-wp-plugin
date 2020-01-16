// noconflict mode
var oo = jQuery.noConflict();

oo(document).ready(function() {

	oo('#oo-galleryslide').slick({
		infinite: true,
		slidesToShow: 1
	});

	oo('#oo-similarframe').slick({
		infinite: true,
		arrows: false,
		dots: true,
		autoplay: true,
		slidesToShow: 3,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 2
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 1
				}
			}
		]
	});

});