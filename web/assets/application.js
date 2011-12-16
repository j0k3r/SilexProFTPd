
// from CasperJS website
$(document).ready(function() {
    // section-toc
    (function(window) {
        var containerSelector = 'div';
        var padding = 50;
        var elements = $('.section-toc');
        var initials = [];
        function position(i, initial) {
            var element = initial.element
              , container = initial.container
              , sp = window.scrollY + padding
              , ep = element.position().top
              , eh = element.height()
              , ct = container.position().top
              , ch = container.height()
              , cp = element.css('position')
              , mp = ct + ch - eh
              , em = element.position().left
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
            var element = $(element);
            initials.push({
                element:    element,
                container:  element.parents(containerSelector),
                position:   element.css('position'),
                top:        element.position().top,
                left:       element.position().left,
                margintop:  element.css('margin-top')
            });
        });
        window.onscroll = function() {
            $(initials).each(position);
        }
    })(window);
})