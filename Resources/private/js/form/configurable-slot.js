define(['jquery'], function($) {
    "use strict";

    /**
     * Entity search widget
     * @see https://github.com/viljamis/ResponsiveSlides.js/blob/master/responsiveslides.js
     */
    $.fn.configurableSlotWidget = function(options) {

        var settings = $.extend({
            "auto": true,             // Boolean: Animate automatically, true or false
            "speed": 500,             // Integer: Speed of the transition, in milliseconds
            "timeout": 4000,          // Integer: Time between slide transitions, in milliseconds
            "pager": false,           // Boolean: Show pager, true or false
            "nav": false,             // Boolean: Show navigation, true or false
            "random": false,          // Boolean: Randomize the order of the slides, true or false
            "pause": false,           // Boolean: Pause on hover, true or false
            "pauseControls": true,    // Boolean: Pause when hovering controls, true or false
            "prevText": "Previous",   // String: Text for the "previous" button
            "nextText": "Next",       // String: Text for the "next" button
            "maxwidth": "",           // Integer: Max-width of the slideshow, in pixels
            "navContainer": "",       // Selector: Where auto generated controls should be appended to, default is after the <ul>
            "manualControls": "",     // Selector: Declare custom pager navigation
            "namespace": "conf-slot", // String: change the default namespace used
            "before": $.noop,         // Function: Before callback
            "after": $.noop           // Function: After callback
        }, options);

        var i = 0;

        this.each(function() {

            i++;

            var $this = $(this),

                // Local variables
                vendor,
                // selectTab,
                // startCycle,
                // restartCycle,
                // rotate,
                // $tabs,

                // Helpers
                index = 0,
                checkedIndex = 0,
                $slide = $this.find('.choices > ul').children(),
                length = $slide.length,
                fadeTime = parseFloat(settings.speed),
                // waitTime = parseFloat(settings.timeout),
                maxw = parseFloat(settings.maxwidth),

                // Namespacing
                namespace = settings.namespace,
                namespaceIdx = namespace + i,

                // Classes
                // navClass = namespace + "_nav " + namespaceIdx + "_nav",
                // activeClass = namespace + "_here",
                visibleClass = namespaceIdx + "_on",
                slideClassPrefix = namespaceIdx + "_s",

                // Styles for visible and hidden slides
                visible = {"float": "left", "position": "relative", "opacity": 1, "zIndex": 2},
                hidden = {"float": "none", "position": "absolute", "opacity": 0, "zIndex": 1},

                // Detect transition support
                supportsTransitions = (function () {
                    var docBody = document.body || document.documentElement;
                    var styles = docBody.style;
                    var prop = "transition";
                    if (typeof styles[prop] === "string") {
                        return true;
                    }
                    // Tests for vendor specific prop
                    vendor = ["Moz", "Webkit", "Khtml", "O", "ms"];
                    prop = prop.charAt(0).toUpperCase() + prop.substr(1);
                    var i;
                    for (i = 0; i < vendor.length; i++) {
                        if (typeof styles[vendor[i] + prop] === "string") {
                            return true;
                        }
                    }
                    return false;
                })(),

                // Before callback
                // TODO use CSS transitions
                beforeSlide = function(idx) {
                    var $thisSlide = $slide.eq(idx),
                        $choiceInput = $thisSlide.find('input[type=radio]'),
                        config = $choiceInput.data('config'),
                        $quantityInput = $this.find('input[type=number]'),
                        $info = $this.find('.choice-info');

                    $info.fadeOut(fadeTime/2, function() {
                        var min = config.min_quantity || 1,
                            max = config.max_quantity || 1;

                        $choiceInput.prop('checked', true);

                        var quantity = $quantityInput
                            //.prop('disabled', min == max)
                            .prop('min', min)
                            .prop('max', max)
                            .val();

                        if (quantity < min) {
                            $quantityInput.val(min);
                        } else if(quantity > max ) {
                            $quantityInput.val(max);
                        }

                        $this.find('.choice-title').text(config.title);
                        $this.find('.choice-description').html(config.description);
                        $this.find('.choice-price').html(config.price + '&nbsp&euro;');

                        $info.fadeIn(fadeTime/2);
                    });
                },

                // Fading animation
                slideTo = function (idx) {
                    beforeSlide(idx);
                    settings.before(idx);
                    // If CSS3 transitions are supported
                    if (supportsTransitions) {
                        $slide
                            .removeClass(visibleClass)
                            .css(hidden)
                            .eq(idx)
                            .addClass(visibleClass)
                            .css(visible);
                        index = idx;
                        setTimeout(function () {
                            settings.after(idx);
                        }, fadeTime);
                        // If not, use jQuery fallback
                    } else {
                        $slide
                            .stop()
                            .fadeOut(fadeTime, function () {
                                $(this)
                                    .removeClass(visibleClass)
                                    .css(hidden)
                                    .css("opacity", 1);
                            })
                            .eq(idx)
                            .fadeIn(fadeTime, function () {
                                $(this)
                                    .addClass(visibleClass)
                                    .css(visible);
                                settings.after(idx);
                                index = idx;
                            });
                    }
                };

            // Initialize each slides
            $slide.each(function (i) {
                // Add ID
                this.id = slideClassPrefix + i;

                var $thisSlide = $(this),
                    $radio = $thisSlide.find('input[type=radio]');

                $thisSlide.find('img').prop('src', $radio.data('config').image);

                if ($radio.is(':checked')) {
                    checkedIndex = i;
                }
            });

            // Add max-width and classes
            $this.addClass(namespace + " " + namespaceIdx);
            if (options && options.maxwidth) {
                $this.css("max-width", maxw);
            }

            // Hide all slides, then show first one
            $slide
                .hide()
                .css(hidden)
                .eq(0)
                .addClass(visibleClass)
                .css(visible)
                .show();

            // CSS transitions
            if (supportsTransitions) {
                $slide
                    .show()
                    .css({
                        // -ms prefix isn't needed as IE10 uses prefix free version
                        "-webkit-transition": "opacity " + fadeTime + "ms ease-in-out",
                        "-moz-transition": "opacity " + fadeTime + "ms ease-in-out",
                        "-o-transition": "opacity " + fadeTime + "ms ease-in-out",
                        "transition": "opacity " + fadeTime + "ms ease-in-out"
                    });
            }

            // Max-width fallback
            if (typeof document.body.style.maxWidth === "undefined" && settings.maxwidth) {
                var widthSupport = function () {
                    $this.css("width", "100%");
                    if ($this.width() > maxw) {
                        $this.css("width", maxw);
                    }
                };

                // Init fallback
                widthSupport();
                $(window).bind("resize", function () {
                    widthSupport();
                });
            }

            // Click event handler
            var $trigger = $this.find('a'),
                $prev = $trigger.filter(".prev");

            $trigger.bind("click", function (e) {
                e.preventDefault();

                var $visibleClass = $("." + visibleClass);

                // Prevent clicking if currently animated
                if ($visibleClass.queue('fx').length) {
                    return;
                }

                //  Adds active class during slide animation
                //  $(this)
                //    .addClass(namespace + "_active")
                //    .delay(fadeTime)
                //    .queue(function (next) {
                //      $(this).removeClass(namespace + "_active");
                //      next();
                //  });

                // Determine where to slide
                var idx = $slide.index($visibleClass),
                    prevIdx = idx - 1,
                    nextIdx = idx + 1 < length ? index + 1 : 0;

                // Go to slide
                slideTo($(this)[0] === $prev[0] ? prevIdx : nextIdx);
                /*if (settings.pager || settings.manualControls) {
                    selectTab($(this)[0] === $prev[0] ? prevIdx : nextIdx);
                }

                if (!settings.pauseControls) {
                    restartCycle();
                }*/
            });

            slideTo(checkedIndex);
        });

        return this;
    };

    return {
        init: function($element) {
            $element.configurableSlotWidget();
        }
    };
});
