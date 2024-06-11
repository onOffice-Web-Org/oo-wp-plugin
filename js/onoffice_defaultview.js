(function($) {
    $(function() {
        $(document).ready(function() {
            $('#oo-galleryslide').slick({
                infinite: true,
                slidesToShow: 1
            });

            $('#oo-similarframe').slick({
                infinite: true,
                arrows: false,
                dots: true,
                autoplay: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                responsive: [{
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 2
                    }
                }, {
                    breakpoint: 575,
                    settings: {
                        slidesToShow: 1
                    }
                }]
            });
            applyGradientToSegments();

            function applyGradientToSegments() {
                const segments = $('.energy-certificate-container .segment');
                if (segments.length === 0) return;

                const colors = {
                    start: [0, 128, 0],
                    middle: [255, 185, 0],
                    end: [255, 0, 0]
                };

                segments.each(function(index) {
                    const ratio = index / (segments.length - 1);
                    const isFirstHalf = ratio < 0.5;
                    const adjustedRatio = isFirstHalf ? ratio * 2 : (ratio - 0.5) * 2;
                    const color = calculateColor(isFirstHalf ? colors.start : colors.middle, isFirstHalf ? colors.middle : colors.end, adjustedRatio);
                    const nextColor = calculateColor(isFirstHalf ? colors.start : colors.middle, isFirstHalf ? colors.middle : colors.end, adjustedRatio + 1 / (segments.length - 1));

                    $(this).css('background', `linear-gradient(to right, rgb(${color.join(',')}), rgb(${nextColor.join(',')}))`);
                });
            }

            function calculateColor(start, end, ratio) {
                return start.map((startValue, i) => Math.round(startValue + ratio * (end[i] - startValue)));
            }
        });
    });
})(jQuery);