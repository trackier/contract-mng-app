! function($) {
    "use strict";

    var Sidemenu = function() {
        this.$body = $("body"),
        this.$openLeftBtn = $(".open-left"),
        this.$menuItem = $("#sidebar-menu a")
    };
    Sidemenu.prototype.openLeftBar = function() {
        $("#wrapper").toggleClass("enlarged");
        $("#wrapper").addClass("forced");

        if ($("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left")) {
            $("body").removeClass("fixed-left").addClass("fixed-left-void");
        } else if (!$("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left-void")) {
            $("body").removeClass("fixed-left-void").addClass("fixed-left");
        }

        if ($("#wrapper").hasClass("enlarged")) {
            $(".left ul").removeAttr("style");
        } else {
            $(".subdrop").siblings("ul:first").show();
        }

        toggle_slimscroll(".slimscrollleft");
        $("body").trigger("resize");
    },
    //menu item click
    Sidemenu.prototype.menuItemClick = function(e) {
        if (!$("#wrapper").hasClass("enlarged")) {
            if ($(this).parent().hasClass("has_sub")) { }
            if (!$(this).hasClass("subdrop")) {
                // hide any open menus and remove all other classes
                $("ul", $(this).parents("ul:first")).slideUp(350);
                $("a", $(this).parents("ul:first")).removeClass("subdrop");
                $("#sidebar-menu .pull-right i").removeClass("md-remove").addClass("md-add");

                // open our new menu and add the open class
                $(this).next("ul").slideDown(350);
                $(this).addClass("subdrop");
                $(".pull-right i", $(this).parents(".has_sub:last")).removeClass("md-add").addClass("md-remove");
                $(".pull-right i", $(this).siblings("ul")).removeClass("md-remove").addClass("md-add");
            } else if ($(this).hasClass("subdrop")) {
                $(this).removeClass("subdrop");
                $(this).next("ul").slideUp(350);
                $(".pull-right i", $(this).parent()).removeClass("md-remove").addClass("md-add");
            }
        }
    },
    //init sidemenu
    Sidemenu.prototype.init = function() {
        var $this = this;

        var ua = navigator.userAgent,
            event = (ua.match(/iP/i)) ? "touchstart" : "click";

            //bind on click
            this.$openLeftBtn.on(event, function(e) {
                console.log('Click');
                e.stopPropagation();
                $this.openLeftBar();
            });

            // LEFT SIDE MAIN NAVIGATION
            $this.$menuItem.on(event, $this.menuItemClick);

            // NAVIGATION HIGHLIGHT & OPEN PARENT
            $("#sidebar-menu ul li.has_sub a.active").parents("li:last").children("a:first").addClass("active").trigger("click");
        },

        //init Sidemenu
        $.Sidemenu = new Sidemenu, $.Sidemenu.Constructor = Sidemenu
}(window.jQuery),

//main app module
function($) {
    "use strict";
    var App = function() {
        this.VERSION = "3.0.0",
        this.AUTHOR = "CloudStuff",
        this.SUPPORT = "info@cloudstuff.tech",
        this.pageScrollElement = "html, body",
        this.$body = $("body")
    };

    //on doc load
    App.prototype.onDocReady = function(e) {
        FastClick.attach(document.body);
        resizefunc.push("initscrolls");
        resizefunc.push("changeptype");

        $('.animate-number').each(function() {
            $(this).animateNumbers($(this).attr("data-value"), true, parseInt($(this).attr("data-duration")));
        });

        //RUN RESIZE ITEMS
        $(window).resize(debounce(resizeitems, 100));
        $("body").trigger("resize");

        // right side-bar toggle
        $('.right-bar-toggle').on('click', function(e) {
            $('#wrapper').toggleClass('right-bar-enabled');
        });
    },
    //initilizing 
    App.prototype.init = function() {
        var $this = this;
        //document load initialization
        $(document).ready($this.onDocReady);
        //init side bar - left
        $.Sidemenu.init();
    },
    $.App = new App, $.App.Constructor = App
}(window.jQuery),

//initializing main application module
function($) {
    "use strict";
    $.App.init();
}(window.jQuery);

/* ------------ some utility functions ----------------------- */
function executeFunctionByName(functionName, context /*, args */ ) {
    var args = [].slice.call(arguments).splice(2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for (var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(this, args);
}
var w, h, dw, dh;
var changeptype = function() {
    w = $(window).width();
    h = $(window).height();
    dw = $(document).width();
    dh = $(document).height();

   
    if (!$("#wrapper").hasClass("forced")) {
        if (w > 1024) {
            $("body").removeClass("smallscreen").addClass("widescreen");
            $("#wrapper").removeClass("enlarged");
        } else {
            $("body").removeClass("widescreen").addClass("smallscreen");
            $("#wrapper").addClass("enlarged");
            $(".left ul").removeAttr("style");
        }
        if ($("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left")) {
            $("body").removeClass("fixed-left").addClass("fixed-left-void");
        } else if (!$("#wrapper").hasClass("enlarged") && $("body").hasClass("fixed-left-void")) {
            $("body").removeClass("fixed-left-void").addClass("fixed-left");
        }
    }
    toggle_slimscroll(".slimscrollleft");
}

var debounce = function(func, wait, immediate) {
    var timeout, result;
    return function() {
        var context = this,
            args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) result = func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) result = func.apply(context, args);
        return result;
    };
}

function resizeitems() {
    if ($.isArray(resizefunc)) {
        for (i = 0; i < resizefunc.length; i++) {
            window[resizefunc[i]]();
        }
    }
}

function initscrolls() {
    $('.slimscroller').slimscroll({
        height: 'auto',
        size: "5px"
    });
    $('.slimscrollleft').slimScroll({
        height: 'auto',
        position: 'right',
        size: "5px",
        color: '#98a6ad',
        wheelStep: 5
    });
}

function isMobile() {
    let isMobile = false, ua = this.ua;
    if (
      /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(window.navigator.userAgent)
    ) isMobile = true;

    return isMobile;
}

function toggle_slimscroll(item) {
    if ($("#wrapper").hasClass("enlarged")) {
        $(item).css("overflow", "inherit").parent().css("overflow", "inherit");
        $(item).siblings(".slimScrollBar").css("visibility", "hidden");
    } else {
        $(item).css("overflow", "hidden").parent().css("overflow", "hidden");
        $(item).siblings(".slimScrollBar").css("visibility", "visible");
    }
}

$(document).ready(function() {
    $('.autoHeight').on( 'change keyup keydown paste cut', 'textarea', function (){
        $(this).height(0).height(this.scrollHeight);
    }).find('textarea').change();
});

// === following js will activate the menu in left side bar based on url ====
$(document).ready(function() {
    $("#sidebar-menu a").each(function() {
        if (this.href == window.location.href) {
            $(this).addClass("active");
            $(this).parent().addClass("active"); // add active to li of the current link
            $(this).parent().parent().prev().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().parent().prev().click();
            $(this).parent().parent().prev().click(); // click the item to make it drop
        }
    });
});

var wow = new WOW({
    boxClass: 'wow', // animated element css class (default is wow)
    animateClass: 'animated', // animation css class (default is animated)
    offset: 50, // distance to the element when triggering the animation (default is 0)
    mobile: false // trigger animations on mobile devices (true is default)
});
wow.init();

//for account manager
function show_div() {
    $('#show_manager').animate({bottom : '0px'});
    $('#show_manager_cross').show().animate({bottom : '230px'});
}
function hide_div() {
    $('#show_manager').animate({bottom : '-200px'});
    $('#show_manager_cross').hide().animate({bottom : '30px'});
}

//portlets
!function($) {
    "use strict";

    var Portlet = function() {
        this.$body = $("body"),
        this.$portletIdentifier = ".portlet",
        this.$portletCloser = '.portlet a[data-toggle="remove"]',
        this.$portletRefresher = '.portlet a[data-toggle="reload"]'
    };

    //on init
    Portlet.prototype.init = function() {
        var $this = this;
        $(document).on("click",this.$portletCloser, function (ev) {
            ev.preventDefault();
            var $portlet = $(this).closest($this.$portletIdentifier);
            var $portlet_parent = $portlet.parent();
            $portlet.remove();
            if ($portlet_parent.children().length == 0) {
                $portlet_parent.remove();
            }
        });

        // Panel Reload
        $(document).on("click",this.$portletRefresher, function (ev) {
            ev.preventDefault();
            var $portlet = $(this).closest($this.$portletIdentifier);
            // This is just a simulation, nothing is going to be reloaded
            $portlet.append('<div class="panel-disabled"><div class="loader-1"></div></div>');
            var $pd = $portlet.find('.panel-disabled');
            setTimeout(function () {
                $pd.fadeOut('fast', function () {
                    $pd.remove();
                });
            }, 500 + 300 * (Math.random() * 5));
        });
    },
    $.Portlet = new Portlet, $.Portlet.Constructor = Portlet
}(window.jQuery),

/**
 * Components
 */
function($) {
    "use strict";
    
    var Components = function() {};

    //initializing tooltip
    Components.prototype.initTooltipPlugin = function() {
        $.fn.tooltip && $('[data-toggle="tooltip"]').tooltip()
    },

    //initializing popover
    Components.prototype.initPopoverPlugin = function() {
        $.fn.popover && $('[data-toggle="popover"]').popover()
    },

    //initializing custom modal
    Components.prototype.initCustomModalPlugin = function() {
        $('[data-plugin="custommodal"]').on('click', function(e) {
            Custombox.open({
                target: $(this).attr("href"),
                effect: $(this).attr("data-animation"),
                overlaySpeed: $(this).attr("data-overlaySpeed"),
                overlayColor: $(this).attr("data-overlayColor")
            });
            e.preventDefault();
        });
    },

    //initializing nicescroll
    Components.prototype.initNiceScrollPlugin = function() {
        //You can change the color of scroll bar here
        $.fn.niceScroll &&  $(".nicescroll").niceScroll({ cursorcolor: '#98a6ad',cursorwidth:'6px', cursorborderradius: '5px'});
    },

    Components.prototype.accountSearchLoad = function (input) {
        input = input || $('.accountSearch');
        var searchUrl = input.data('search-url') || '/account/manage.json';
        var noResultsMsg = input.data('no-result-msg') || "No Account Found!";
        var defaultOpts = {
            searchUrl: searchUrl,
            noResultMsg: noResultsMsg,
            queryFunc: function (input, params) {
                var obj = { query: { } };
                if (isNaN(params.term)) {
                    obj['search'] = params.term;
                    obj['live'] = 1;
                    obj['type'] = 'orgname';
                }
                return obj;
            },
            transformFunc: function (data) {
                var accounts = _.values(data.orgs);

                var results = [];
                _.each(accounts, function (c) {
                    if (c.status == 'deleted') {
                        return;
                    }
                    if (c.title) {
                        c.title = c.title.replace(/&amp;/g, '&');
                    }
                    results.push({
                        id: c._id,
                        text: c.name
                    });
                })
                return { results: results }
            }
        }
        this._select2Ajax(input, defaultOpts);
    },

    Components.prototype._select2Ajax = function(input, defaultOpts) {
        if (input.length == 0) {
            return;
        }

        var url = input.data('search-url') || defaultOpts['searchUrl'];
        var noResultsMsg = input.data('no-result-msg') || defaultOpts['noResultMsg'];

        var ajaxOpts = {
            url: url,
            // The number of milliseconds to wait for the user to stop typing before
            delay: 500,
            cache:true,
            data: function(params) {
                return defaultOpts.queryFunc(input, params);
            },
            processResults: function(data) {
                return defaultOpts.transformFunc(data);
            },
        }

        input.select2({
            language: {
                noResults: function () {
                    return noResultsMsg;
                }, 
                errorLoading:function(){ return "Searching..."} },
            ajax: ajaxOpts
        });
    },


    //advance multiselect start
    Components.prototype.initMultiSelect = function() {
        $('#my_multi_select3').multiSelect({
            selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
            selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function () {
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function () {
                this.qs1.cache();
                this.qs2.cache();
            }
        });
    },

    Components.prototype.initBase64Decode = function () {
        var selector = $('.decode_base64');
        $.each(selector, function (i, val) {
            var $el = $(val),
                content = $el.html();
            
                try {
                $el.html(atob(content));
            } catch (e) {
                //do nothing
            }
        })
    },

    //daterange picker
    Components.prototype.initDatePicker = function() {
        // initialize beautiful daterange picker
        if (typeof moment === "undefined") { return false;}
        $('#daterange').daterangepicker({
            format: 'YYYY-MM-DD',
            startDate: $('#start').val(),
            endDate: $('#end').val(),
            maxDate: moment(),
            dateLimit: { days: 180},
            showDropdowns: true,
            showWeekNumbers: true,
            timePicker: false,
            timePickerIncrement: 1,
            timePicker12Hour: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'left',
            drops: 'down',
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-success',
            cancelClass: 'btn-default',
            separator: ' to ',
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Submit',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            }
        }, function(start, end, label) {
            $('#start').attr('value', start.format('YYYY-MM-DD'));
            $('#end').attr('value', end.format('YYYY-MM-DD'));
            $('#daterange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        });

        //initialize beautiful daterange picker
        $('.date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        },
        function(start, end, label) {
            $('.date').attr('value', start.format('YYYY-MM-DD'));
        });

        //initialize beautiful datetime picker
        $('.datetime').daterangepicker({
            "singleDatePicker": true,
            "timePicker": true,
            "timePicker24Hour": true,
            "autoApply": true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function(start, end, label) {
            $('.datetime').attr('value', start.format('YYYY-MM-DD'));
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(".datesubmit").submit();
        });
    },
    //multiselect
    Components.prototype.initSelect2 = function() {
        $('select[value]').each(function () {
            $(this).val(this.getAttribute("value"));
        });

        var selectTags = $('.selectVal'); // This is for adding 'selected="true"' on <option> of <select> tag
        if (selectTags.length > 0) {
            $.each(selectTags, function (i, el) {
                var $el = $(el);
                var optValue = $el.data('value') || [];   // This will contain all the values of select tag
                optValue.forEach(function (val) {
                    $el.find('option[value="' + val + '"]').prop('selected', true);
                });
            });
        }
        $('.selectpicker').select2();
    },

    /******* Functions for Display Widget Data ********/
    /**
     * This function will animate the counter by increasing it from a starting value
     * to final value within a given duration of time
     * @param  {Object}   obj Keys -> ('oldCount', 'newCount', 'duration')
     * @param  {Function} cb  Callback Function
    */
    Components.prototype.animateCount = function (obj, cb) {
        if (!obj) {obj = {};}

        $({countValue: obj.oldCount || 0}).animate(
            {countValue: obj.newCount || 0},
            {
                duration: obj.duration || 2000, /* time for animation in milliseconds */
                step: cb
            }
        );
    },

    /**
     * Display the Counter in the UI
     * @param  {Float} value The current value of counter
     * @param  {String} prop  Name of the property
     * @param  {String} elId  The ID of the element
     */
    Components.prototype.displayCounter = function (value, prop, elId) {
        prop = prop.toLowerCase();
        if (prop === 'revenue' || prop === 'payout') {
            $(elId).html(value.toFixed(2));
        } else {
            $(elId).html(value.toFixed(0));
        }
    },

    /**
     * Opens Last Active Tab
     */
    Components.prototype.initLatestTab = function () {
        $('a[data-toggle="tab"]').on('click', function (e) {
            localStorage.setItem('lastTab', $(e.target).attr('href'));
        });

        //go to the latest tab, if it exists:
        var lastTab = localStorage.getItem('lastTab');

        if (lastTab) {
            $('a[href="'+lastTab+'"]').click();
        }
    },

    /**
     * Shows Collapse, Default close unless saved as open
     */
    Components.prototype.initCollapseState = function () {
        $('.collapse').on('hidden.bs.collapse', function () {
            localStorage.removeItem('open_' + this.id);
        });

        $('.collapse').on('shown.bs.collapse', function () {
            localStorage.setItem('open_' + this.id, true);
        });
        $('.collapse').each(function () {
            if (localStorage.getItem('open_' + this.id)) {
                $(this).collapse('show');
            }
        });
    },

    /**
     * Initialize the widgets by incrementing their count from zero
     */
    Components.prototype.initWidgets = function () {
        var self = this;
        ['Clicks', 'Conversions', 'Impressions', 'Payout', 'Revenue'].forEach(function (prop) {
            var elId = '#live' + prop;
            var newCount = parseFloat($(elId).text());
            self.displayCounter(newCount, prop, elId);
        });
        // self.masonry();
    },

    Components.prototype.masonry = function () {
        var $this = this;
        var isotope = {filter: '*'};
        var container = $(".portfolioContainer");
        container.isotope(isotope);

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            this.masonry();
        });
    },

    /**
     * Create an object for the property and animate the counter for it with the
     * help of animate counter and display counter
     * @param  {String} prop     Name of the property
     * @param  {Float} finalVal The Final value of the counter
     */
    Components.prototype.widgetCounter = function (prop, finalVal) {
        var self = this;

        var elId = '#live' + prop.ucfirst(),
            obj = {
                oldCount: parseFloat($(elId).text()),
                newCount: finalVal
            };
        
        self.displayCounter(value, prop, elId);
    },

    Components.prototype.convertTo = function (value, currency, places) {
        if (!places) places = 2;
        
        var ans = 1;
        if (!currency) {
            currency = '';
        }
        switch (currency.toLowerCase()) {
            case 'inr':
                places = 3;
                ans = value * 66;
                break;

            case 'pkr':
                places = 3;
                ans = value * 104;
                break;

            case 'aud':
                places = 3;
                ans = (value * 1.3);
                break;

            case 'eur':
                places = 3;
                ans = (value * 0.9);
                break;

            case 'gbp':
                places = 3;
                ans = (value * 0.8);
                break;

            default:
                ans = value;
                break;
        }
        var final = Math.round(ans * Math.pow(10, places)) / Math.pow(10, places);
        return final;
    },

    Components.prototype._confirmSwal = function (msg, callback) {
        swal({
            html: true,
            title: msg,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-outline-primary btn-sm",
            cancelButtonClass: "btn-outline-dark btn-sm",
            confirmButtonText: "Yes, proceed!",
            closeOnConfirm: false
        }, callback)
    },

    Components.prototype._errorSwal = function (msg) {
        return swal({
            html: true,
            title: "Error",
            text: msg || "Aw snap!! Something went wrong. Our Dev Team have been notified!",
            type: "warning",
            confirmButtonClass: "btn-outline-dark btn-sm",
            confirmButtonText: "Close"
        });
    },

    Components.prototype._successSwal = function (self, data) {
        var fallbackMsg = self.data('fallback-msg') || 'No message provided by server';
        swal({
            html: true,
            title: data.message || fallbackMsg,
            type: "success",
            confirmButtonClass: "btn-outline-primary"
        },
        function(){
            if (self.data('fallback')) {
                window.location.href = self.data('fallback');
            } else {
                window.location.reload();
            }
        });
    },

    Components.prototype.bulkSelect = function () {
        $('.bulk_select_action').on('click', function (e) {
            // first confirm the action
            var ids = [],
                self = $(this),
                url = self.data('href') || self.attr('href'),
                method = (self.data('method') || 'POST').toLowerCase(),
                message = self.data('message') || 'Proceed with action?'

            $.Components._confirmSwal(message, function () {
                $.each($('.bulk_select_option:checked'), function (index, el) {
                    var $el = $(el);
                    if ($el.data('id')) {
                        ids.push($el.data('id'))
                    }
                })

                if (ids.length === 0) {
                    return $.Components._errorSwal('Please Select atleast 1 item!!');
                }

                // Send a request to backend
                request[method]({url: url, data: {bulk_op: 1, action: self.data('action'), ids: ids}}, function (err, data) {
                    if (err) {
                        return $.Components._errorSwal()
                    }
                    $.Components._successSwal(self, data)
                })
            })
        })
    },

    //initilizing
    Components.prototype.init = function() {
        var $this = this;
        this.initTooltipPlugin(),
        this.initPopoverPlugin(),
        this.initNiceScrollPlugin(),
        this.initCustomModalPlugin(),
        this.initSelect2(),
        this.initLatestTab(),
        this.initCollapseState(),
        this.initDatePicker(),
        this.accountSearchLoad(),
        //creating portles
        $.Portlet.init();
        this.initBase64Decode();
    },
    $.Components = new Components, $.Components.Constructor = Components

}(window.jQuery),
    //initializing main application module
function($) {
    "use strict";
    $.Components.init();
}(window.jQuery);
