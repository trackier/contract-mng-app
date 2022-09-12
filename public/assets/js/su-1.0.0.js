/**
 * Author: vNative Dev Team
 */
(function (window, $) {
    "use strict";
    var SU = (function () {
        function SU() {
            this.url = "insight/organization";
            this.chart = "insight/chart";
            this.oochart = "offersoptimize/graph";
        }

        SU.prototype = {
            performance: function () {
                var $this = this;
                request.get({ url: $this.chart, data: $('#indexrange').serialize()}, function(err, data) {
                    var date = [], orgs = [], leads = [], active = [], perfdata = [], totalLeads = 0, totalOrgs = 0, totalActiveOrgs = 0;
                    $.each(data.stats, function(i, val) {
                        date.push(i);
                        if(!isNaN(val.lead)) {
                            totalLeads += val.lead;    
                        }
                        if(!isNaN(val.org)) {
                            totalOrgs += val.org;    
                        }
                        if(!isNaN(val.active)) {
                            totalActiveOrgs += val.active;    
                        }
                        
                        orgs.push(val.org || 0);
                        leads.push(val.lead || 0);
                        active.push(val.active || 0);
                    });
                    perfdata.push({name: 'Active', data: active});
                    perfdata.push({name: 'Leads', data: leads});
                    perfdata.push({name: 'Organization', data: orgs});

                    $('#totalLeads').text(totalLeads);
                    $('#totalOrgs').text(totalOrgs);
                    $('#totalActiveOrgs').text(totalActiveOrgs);

                    if ($("#perfstat").length) {
                        $.Hcharts.createLineChart('perfstat', perfdata, date, 'Performance Report');
                    }
                });
            },
            update: function () {
                $(document.body).on("click", ".update", function (e) {
                    e.preventDefault();
                    var self = $(this),
                        oldHtml = self.html();

                    var link = self.attr('href') || self.data('href'),
                        waitMessage = self.data('wait-message') || oldHtml,
                        fallbackMsg = self.data('fallback-msg') || 'No message provided by server',
                        iscode = self.data('iscode') || false,
                        noreload = self.data('noreload') || false;
                    
                    self.html(waitMessage);
                    request.post({ url: link, data: self.data('send') || {} }, function (err, d) {
                        self.html(oldHtml);
                        if (err) {
                            return swal({
                                html: true,
                                title: "Error",
                                text: "Something went wrong!!",
                                type: "warning",
                                confirmButtonClass: "btn-outline-dark btn-sm",
                                confirmButtonText: "Close"
                            });
                        }
                        if(self.data('opentab')) {
                            window.open('', '_blank').location.href = d.message;
                            console.log('Fields fired');
                            return;
                        }
                        //FIX : Request Message test overfolwe test; 
                        var message = d.message || fallbackMsg;
                        if (iscode) {
                            message = '<code>' + message + '</code>';
                        }
                        swal({
                            html: true,
                            title: message,
                            type: "success",
                            confirmButtonClass: "btn-outline-primary"
                        },
                        function(){
                            //FIX : No reload Required
                            if (noreload) {
                                return false;
                            }
                            if (self.data('fallback')) {
                                window.location.href = self.data('fallback');
                            } else {
                                window.location.reload();
                            }
                        });
                    });
                });
            },
            confirm: function () {
                $(document.body).on("click", ".confirm", function (e) {
                    e.preventDefault();
                    var self = $(this),
                        oldHtml = self.html();

                    var link = self.attr('href') || self.data('href'),
                        waitMessage = self.data('wait-message') || oldHtml,
                        fallbackMsg = self.data('fallback-msg') || 'No message provided by server',
                        iscode = self.data('iscode') || false,
                        noreload = self.data('noreload') || false;


                    if (!link) return false;
                    
                    // self.html(waitMessage);
                    swal({
                        html: true,
                        title: self.data('message') || "Are you sure?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-outline-danger btn-sm",
                        cancelButtonClass: "btn-outline-dark btn-sm",
                        confirmButtonText: "Yes, Proceed",
                        closeOnConfirm: false
                    },
                    function() {
                        request.post({ url: link, data: self.data('send') || {} }, function (err, d) {
                            self.html(oldHtml);
                            if (err) {
                                return swal({
                                    html: true,
                                    title: "Error",
                                    text: "Something went wrong!!",
                                    type: "warning",
                                    confirmButtonClass: "btn-outline-dark btn-sm",
                                    confirmButtonText: "Close"
                                });
                            }
                            if(self.data('opentab')) {
                                window.open('', '_blank').location.href = d.message;
                                console.log('Fields fired');
                                return;
                            }
                            //FIX : Request Message test overfolwe test; 
                            var message = d.message || fallbackMsg;
                            if (iscode) {
                                message = '<code>' + message + '</code>';
                            }
                            swal({
                                html: true,
                                title: message,
                                type: "success",
                                confirmButtonClass: "btn-outline-primary"
                            },
                            function(){
                                //FIX : No reload Required
                                if (noreload) {
                                    return false;
                                }
                                if (self.data('fallback')) {
                                    window.location.href = self.data('fallback');
                                } else {
                                    window.location.reload();
                                }
                            });
                        });
                    });
                });
            },
            delete: function () {
                $(document.body).on("click", ".delete", function (e) {
                    e.preventDefault();
                    var self = $(this), link = self.attr('href') || self.data('href');
                    if (!link) return false;

                    swal({
                        html: true,
                        title: self.data('message') || "Are you sure?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-outline-danger btn-sm",
                        cancelButtonClass: "btn-outline-dark btn-sm",
                        confirmButtonText: "Yes, delete it!",
                        closeOnConfirm: false
                    },
                    function(){
                        request.delete({url: link}, function (err, data) {
                            if (err) {
                                return swal({
                                    html: true,
                                    title: err,
                                    type: "warning",
                                    confirmButtonClass: "btn-outline-danger"
                                });
                            }

                            swal({
                                html: true,
                                title: data.message || "Done",
                                type: "success",
                                confirmButtonClass: "btn-outline-primary"
                            }, function () {
                                if (self.data('fallback')) {
                                    window.location.href = self.data('fallback');
                                } else {
                                    window.location.reload();
                                }
                            });
                        });
                    });
                });
            },
            prompt: function () {
                $(document.body).on("click", ".prompt", function (e) {
                    e.preventDefault();
                    var self = $(this), message = "",
                        sendData = self.data('send') || {},
                        link = self.attr('href') || self.data('href');
                    
                    if (!link) return swal("Error!", "No Link Set", "warning");
                    if (self.data('message')) {
                        message += self.data('message');
                    } else {
                        message += 'Are you sure, you want to proceed with the action?!';
                    }

                    swal({
                        html: true,
                        title: message,
                        type: "input",
                        showCancelButton: true,
                        confirmButtonClass: "btn-outline-danger btn-sm",
                        cancelButtonClass: "btn-outline-dark btn-sm",
                        closeOnConfirm: false,
                        inputPlaceholder: "Enter Cancel Note"
                    }, function (inputValue) {
                        if (inputValue === false) return false;
                        if (inputValue === "") {
                            swal.showInputError("No input was provided!!");
                            return false
                        }
                        sendData['note'] = inputValue;
                        request.delete({url: link, data: sendData}, function (err, data) {
                            if (err) {return swal("Error!", err, "warning");}
                            swal({
                                html: true,
                                title: data.message || self.data('fallback-msg') || 'No Message provided by server',
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
                        });
                    });
                });
            },
            removeThis: function () {
                $(document.body).on("click", ".removeThis", function (e) {
                    e.preventDefault();

                    var self = $(this);
                    var input = self.parent().parent().find('input');
                    input.remove();
                    self.remove();
                });
            },
            cityCountry: function () {
                var citynames = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    prefetch: {
                        url: '/assets/plugins/country.json',
                        filter: function(list) {
                            return $.map(list, function(country) {
                                return { name: country.name, val: country.code };
                            });
                        }
                    },
                    remote: {
                        url: '/insight/city.json?q=%QUERY',
                        wildcard: '%QUERY',
                        filter: function(list) {
                            return $.map(list.cities, function(cityname) {
                                return { name: cityname._name + ' - ' + cityname._country, val: cityname._name };
                            });
                        }
                    }
                });
                citynames.initialize();

                $('.geos').tagsinput({
                    typeaheadjs: {
                        name: 'citynames',
                        displayKey: 'name',
                        valueKey: 'val',
                        source: citynames.ttAdapter()
                    },
                    freeInput: false
                });
            },
            country: function () {
                var countries = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    prefetch: {
                        url: '/assets/plugins/country.json',
                        filter: function(list) {
                            return $.map(list, function(country) {
                                return { name: country.name, val: country.code };
                            });
                        }
                    }
                });
                countries.initialize();

                $('.geos').tagsinput({
                    typeaheadjs: {
                        name: 'countries',
                        displayKey: 'name',
                        valueKey: 'val',
                        source: countries.ttAdapter()
                    },
                    freeInput: false
                });
            },
            cycleInvoice: function() {
                $('#invoiceCodeModal').on('show.bs.modal', function (event) {
                    var btn = $(event.relatedTarget),
                        invoice = btn.data('invoice');
                    
                    $.each(invoice, function(i, val) {
                        var text = "<b>Invoice ID: "+ i + "</b><br>";
                        text += "Order ID: " + val.ref_id + "<br>";
                        text += "Payment ID: " + val.data.payment_id + "<br>";
                        text += "Amount: " + val.amount + "<br>";
                        text += "<br>Items:";
                        $.each(val.items, function(i, v) {
                            text += "<br>" + v.name + " - " + v.currency + " " + v.display_amount;
                        });
                        text += "<br><hr>";
                        $('#invoiceCode').html(text);
                    });
                });

                $('#callbackCodeModal').on('hidden.bs.modal', function (event) {
                    $('#invoiceCode').html('');
                });
            },
            accountEditInfo: function() {
                var paymentModeDom = $("#paymentMode"),
                    paymentProcessorDom = $("#paymentProcessorDiv");

                paymentProcessorDom.hide();
                if (paymentModeDom.val() == "automatic") {
                    paymentProcessorDom.show();
                }

                paymentModeDom.change(function() {
                    if (paymentModeDom.val() == "automatic") {
                        paymentProcessorDom.show();
                    } else {
                        paymentProcessorDom.hide(); 
                    }
                });
            },
            ooperformance: function () {
                var $this = this;
                request.get({ url: $this.oochart, data: $('#indexrange').serialize()}, function(err, data) {
                    var date = [], accounts = [], manualLinkTest = [], automatedLinkTest = [], perfdata = [];
                    console.log(data);
                    $.each(data.stats, function(i, val) {
                        date.push(i);
                        accounts.push(val.account || 0);
                        manualLinkTest.push(val.manual || 0);
                        automatedLinkTest.push(val.automated || 0);
                    });
                    perfdata.push({name: 'Account', data: accounts});
                    perfdata.push({name: 'Automated Test', data: automatedLinkTest});
                    perfdata.push({name: 'Manual Test', data: manualLinkTest});
                    if ($("#perfstat").length) {
                        $.Hcharts.createLineChart('perfstat', perfdata, date, 'Performance Report');
                    }
                });
            },
            init: function () {
                this.delete();
                this.prompt();
                this.update();
                this.confirm();
                this.removeThis();
            }
        };
        return SU;
    }());

    window.su = new SU();
}(window, jQuery));
//initializing main su module
(function($) {
    "use strict";
    su.init();
}(window.jQuery));
