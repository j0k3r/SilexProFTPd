
// from CasperJS website
$(document).ready(function() {
    // section-toc
    (function(window) {
        var containerSelector = 'div';
        var padding = 50;
        var elements = $('.section-toc');
        var initials = [];
        function position(i, initial) {
            var element = initial.element;
            var container = initial.container;
            var sp = window.scrollY + padding;
            var ep = element.position().top;
            var eh = element.height();
            var ct = container.position().top;
            var ch = container.height();
            var cp = element.css('position');
            var mp = ct + ch - eh;
            var em = element.position().left
            ;
            if (sp > ep && sp < mp && cp !== "fixed") {
                element.css('position', 'fixed').css('top', padding).css('left', em).css('margin-top', initial.margintop);
            } else if (cp === "fixed") {
                if (sp < ct + padding) {
                    element.css('position', initial.position).css('margin-top', initial.margintop);
                } else if (sp >= mp) {
                    element.css('position', initial.position).css('margin-top', (ch - eh - padding - 30));
                }
            }
        }
        elements.each(function(i, element) {
            var n_element = $(element);
            initials.push({
                element:    n_element,
                container:  n_element.parents(containerSelector),
                position:   n_element.css('position'),
                top:        n_element.position().top,
                left:       n_element.position().left,
                margintop:  n_element.css('margin-top')
            });
        });
        window.onscroll = function() {
            $(initials).each(position);
        };
    })(window);

    // bind change event to select
    $('#transfer-type').bind('change', function () {
      var url = $(this).val(); // get selected value
      if (url) { // require a URL
        window.location = url; // redirect
      }
      return false;
    });
});