define([
    'jquery',
    'jquery/ui',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, ui, $t, modal) {

    $.widget('js.launcher',{
        options:{
            searchbox: "#search_box",
            searchResultsBox: "#search_results",
            menuJson: {},
            keyCodes: '17_77',
            saveKeyCodes: '17_83',
            searchUrl: '',
            launcherPopup: "#js_launcher",
            menuLinks : {},
            menuParents : [],
            typingTimer : 0,
            doneTypingInterval : 500,
            popupOptions : {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                modalClass: 'js-launcher',
                title: "Launcher",
                buttons: [],
                focus: "#search_box",
                opened: function() {
                    $(".modal-footer").hide();
                }
            },
        },
        _create: function(){
            var self = this;
            var keyQueueCombinations = [null, null];
            self.renderMenu(self.options.menuJson, [], 1);
            $('body').on("keydown", function (e) {
                if(e.which == 40 || e.which == 38) return;

                keyQueueCombinations.push(e.which);
                keyQueueCombinations.shift();
                if(keyQueueCombinations.join('_') == self.options.keyCodes) {
                    self.openPopup();
                } else if(keyQueueCombinations.join('_') == self.options.saveKeyCodes) {
                    e.preventDefault();
                    self.saveItem();
                }
            });

        },
        openPopup: function() {
            var self = this;
            $(this.options.launcherPopup).modal(this.options.popupOptions).modal('openModal');
            setTimeout(function(){
                $(self.options.searchbox).on('keyup', function(e){

                    if (e.which == 40) {
                        self.down();
                        return;
                    }

                    if (e.which == 38) {
                        self.up();
                        return;
                    }

                    if(e.which == 13)
                    {
                        self.goToHighlightedLink();
                        return;
                    }

                    //perform instant nav/config search
                    var results = self.searchMenu($(self.options.searchbox));
                    if(!$(results).map)
                    {
                        $(self.options.searchResultsBox).html('');
                        return;
                    }
                    var html = $.map(results, function(e){
                        var url = e['url'];
                        return "<p><a href=\""+url+"\">" + e['label-full'] + "</a></p>";
                    });
                    $(self.options.searchResultsBox).html(html.join(''));
                    if($(self.options.searchbox).val() != "" && $(self.options.searchResultsBox).find("p").length < 1)
                    {
                        clearTimeout(self.options.typingTimer);
                        self.options.typingTimer = setTimeout(function() {
                            var query = $(self.options.searchbox).val();
                            searchurl = self.options.searchUrl + ('?query='+query);

                            $.ajax({
                                url: searchurl,
                                type: "GET",
                                showLoader: true
                            }).done(function (transport) {
                                var html = $.map(transport, function(e){
                                    var url = e['url'];
                                    return "<p><a href=\""+url+"\">" + e['name'] + "</a></p>";
                                });
                                $(self.options.searchResultsBox).html(html.join(''));
                            });
                        }, self.options.doneTypingInterval);
                    }
                });

            }, 10);
        },
        saveItem: function() {
            var self = this;
            var $focused = $(':focus');
            $focused.trigger("blur");
            if($('button[data-ui-id="save-and-continue-button"]').length) {
                $('button[data-ui-id="save-and-continue-button"]').trigger('click');
            } else if($('button[data-ui-id="save-button"]').length) {
                $('button[data-ui-id="save-button"]').trigger('click');
            } else if($("button#save_and_edit_button").length) {
                $("button#save_and_edit_button").trigger('click');
            } else if($("button#save").length) {
                $("button#save").trigger('click');
            }
        },
        searchMenu: function(string)
        {
            var self = this;
            if(!string)
            {
                return self.options.menuLinks;
            }
            string = $(string).val().toString().toLowerCase();
            var returnedKeys = $.grep(Object.keys(self.options.menuLinks), function (element, index) {
                return (element.indexOf(string) != -1) ? element : false ;
            });
            return returnedKeys.map(function(v){
                return self.options.menuLinks[v];
            });
        },
        renderMenu: function(o,a,depth)
        {
            var self = this;
            Object.keys(o).forEach(function(item){
                var menu = o[item];
                if(menu.url && menu.url != '#')
                {
                    self.registerLink(menu.label, menu.url);
                }

                a.push(Array(depth).join('-') + item);

                if(menu.children)
                {
                    self.options.menuParents.push(menu.label);
                    self.renderMenu(menu.children,a,depth+1);
                    self.options.menuParents.pop();
                }
            });
            return a;
        },
        registerLink: function(label, url)
        {
            var self = this;
            var pre = '';
            if(self.options.menuParents.length > 0)
            {
                pre = self.options.menuParents.join(' - ');
                pre += ' - ';
            }
            self.options.menuLinks[(pre+label).toLowerCase()] = {
                'url':url,
                'label':label,
                'label-full':pre+label};
        },

        up: function()
        {
            var self = this;
            var elements = $(self.options.searchResultsBox).children("p");
            var i = this.getSetectedHighlightedIndex();
            if(i > 0)
            {
                $(elements[i]).removeClass('highlight');
                $(elements[i-1]).addClass('highlight');
            }
            this.scrollResults();
        },

        down: function()
        {
            var self = this;
            var elements = $(self.options.searchResultsBox).children("p");
            var i = this.getSetectedHighlightedIndex();
            if(i+1 != elements.length)
            {
                if(elements[i])
                {
                    $(elements[i]).removeClass('highlight');
                }
                $(elements[i+1]).addClass('highlight');
            }
            this.scrollResults();
        },

        goToHighlightedLink: function()
        {
            var self = this;
            var a    = $(self.options.searchResultsBox).find('p.highlight').children("a");
            if(!a || !a[0])
            {
                return;
            }
            document.location = a[0].href;
        },

        getSetectedHighlightedIndex: function()
        {
            var self = this;
            var elements = $(self.options.searchResultsBox).children("p");
            var found = false;
            for(var i=0;i<elements.length;i++)
            {
                if($(elements[i]).hasClass('highlight'))
                {
                    found = true;
                    break;
                }
            }

            return found ? i : -1
        },

        scrollResults: function()
        {
            var self = this;
            var $parent = $(".modal-content");
            var $container = $(self.options.searchResultsBox);
            var $scrollTo = $container.find('.highlight');
            var margin = 44;
            $parent.animate({scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop() - margin, scrollLeft: 0},100);
        }
    });
    return $.js.launcher;
});
