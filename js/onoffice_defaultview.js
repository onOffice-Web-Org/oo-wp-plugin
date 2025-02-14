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
                const segments = $('.oo-details-energy-certificate .energy-certificate-container .segment');

                if (segments.length === 0) return;

                const colors = {
                    start: [0, 128, 0],
                    middle: [255, 185, 0],
                    end: [255, 0, 0]
                };

                segments.each(function(index) {
                    const positionRatio = index / (segments.length - 1);
                    const isInInitialSegment = positionRatio < 0.5;
                    const normalizedPosition = isInInitialSegment ? positionRatio * 2 : (positionRatio - 0.5) * 2;
                    const color = calculateColor(isInInitialSegment ? colors.start : colors.middle, isInInitialSegment ? colors.middle : colors.end, normalizedPosition);
                    const nextColor = calculateColor(isInInitialSegment ? colors.start : colors.middle, isInInitialSegment ? colors.middle : colors.end, normalizedPosition + 1 / (segments.length - 1));

                    $(this).css('background', `linear-gradient(to right, rgb(${color.join(',')}), rgb(${nextColor.join(',')}))`);
                });
            }

            function calculateColor(start, end, positionRatio) {
                return start.map((startValue, i) => Math.round(startValue + positionRatio * (end[i] - startValue)));
            }
        });
    });
})(jQuery);