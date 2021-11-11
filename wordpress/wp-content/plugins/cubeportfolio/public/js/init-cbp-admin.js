// override LoadMore for backend
(function($, window, document, undefined) {
    var CubePortfolio = $.fn.cubeportfolio.constructor;
    function Plugin(parent) {
        var t = this;

        t.parent = parent;

        t.loadMore = $(parent.options.loadMore).find('.cbp-l-loadMore-link');

        // load click or auto action
        if (parent.options.loadMoreAction.length) {
            t.click();
        }

    }

    Plugin.prototype.click = function() {
        var t = this,
            numberOfClicks = 0,
            displayItemsLoadMore = parseInt(t.parent.options.displayItemsLoadMore, 10);

        t.loadMore.on('click.cbp', function(e) {
			
            var item = $(this),
                container = $('#cbp-load-more-container'),
                items, elements;

            e.preventDefault();

            if (t.parent.isAnimating || item.hasClass('cbp-l-loadMore-stop')) {
                return;
            }

            item.addClass('cbp-l-loadMore-loading');

            elements = container.children().slice(numberOfClicks, numberOfClicks + displayItemsLoadMore);
            items = elements.clone(true);

            items.addClass('cbp-item-config-loaded');

            items.each(function(index, item) {
                var img = $(item).find('img');
                img.attr('src', img.attr('srcc'));
            });

            t.parent.$obj.cubeportfolio('appendItems', items, function() {
                item.removeClass('cbp-l-loadMore-loading');

                numberOfClicks = numberOfClicks + displayItemsLoadMore;

                if (container.children().slice(numberOfClicks).length === 0) {
                    item.addClass('cbp-l-loadMore-stop');
                }
                
            });

        });
    };


    Plugin.prototype.auto = function() {
        var t = this;

        t.parent.$obj.on('initComplete.cbp', function() {
            Object.create({
                init: function() {
                    var self = this;

                    // the job inactive
                    self.isActive = false;

                    self.numberOfClicks = 0;

                    self.offset = 0;

                    self.displayItemsLoadMore = parseInt(t.parent.options.displayItemsLoadMore, 10);

                    self.container = $('#cbp-load-more-container');

                    // set loading status
                    t.loadMore.addClass('cbp-l-loadMore-loading');

                    // cache window selector
                    self.window = $(window);

                    // add events for scroll
                    self.addEvents();

                    // trigger method on init
                    self.getNewItems();

                    return this;
                },

                addEvents: function() {
                    var self = this,
                        timeout;

                    t.loadMore.on('click.cbp', function(e) {
                        e.preventDefault();
                    });

                    self.window.on('scroll.loadMoreObject', function() {

                        clearTimeout(timeout);

                        timeout = setTimeout(function() {
                            if (!t.parent.isAnimating) {
                                // get new items on scroll
                                self.getNewItems();
                            }
                        }, 500);

                    });

                    // when the filter is completed
                    t.parent.$obj.on('filterComplete.cbp', function() {
                        self.getNewItems();
                    });
                },

                getNewItems: function() {
                    var self = this,
                        topLoadMore, topWindow;


                    if (t.parent.isAnimating || self.isActive || t.loadMore.hasClass('cbp-l-loadMore-stop')) {
                        return;
                    }

                    topLoadMore = t.loadMore.offset().top;
                    topWindow = self.window.scrollTop() + self.window.height();

                    if (topLoadMore > topWindow) {
                        return;
                    }

                    // this job is now busy
                    self.isActive = true;

                    var items = self.container.children().slice(self.offset, self.offset + self.displayItemsLoadMore).clone(true);

                    items.addClass('cbp-item-config-loaded');

                    items.each(function(index, item) {
                        var img = $(item).find('img');
                        img.attr('src', img.attr('srcc'));
                    });


                    t.parent.$obj.cubeportfolio('appendItems', items, function() {
                        // increment number of clicks
                        self.numberOfClicks++;

                        self.offset = self.numberOfClicks * self.displayItemsLoadMore;

                        if (self.container.children().slice(self.offset).length === 0) {
                            t.loadMore.addClass('cbp-l-loadMore-stop').removeClass('cbp-l-loadMore-loading');

                            // remove events
                            self.window.off('scroll.loadMoreObject');
                            t.parent.$obj.off('filterComplete.cbp');
                        } else {
                            // make the job inactive
                            self.isActive = false;

                            self.window.trigger('scroll.loadMoreObject');
                        }

                    });

                }
            }).init();
        });

    };


    Plugin.prototype.destroy = function() {
        var t = this;

        t.loadMore.off('.cbp');
        $(window).off('scroll.loadMoreObject');
    };

    CubePortfolio.plugins.loadMore = function(parent) {
        if (parent.options.loadMore === '') {
            return null;
        }

        return new Plugin(parent);
    };

})(jQuery, window, document);


(function($, window, document, undefined) {
    'use strict';

    // reset _filterFromUrl to not trigger on admin side
    $.fn.cubeportfolio.constructor.prototype.filterFromUrl = function() {};

    var gridContainer = $('#cbpw-grid' + cbpwOptions.id),
        filtersContainer = $('#cbpw-filters' + cbpwOptions.id),
        wrap, filtersCallback;

    /*********************************
     init cubeportfolio
     *********************************/
    cbpwOptions.options.singlePageCallback = function(url, element) {

        // to update singlePage content use the following method: this.updateSinglePage(yourContent)
        var self = this;

        $.ajax({
                url: url,
                type: 'POST',
                dataType: 'html',
                timeout: 30000,
                data: {
                    link: url,
                    type: 'cbp-singlePage',
                    source: 'cubeportfolio',
                    id: cbpwOptions.id,
                    popupData: localStorage.getItem('popup')
                }
            }).done(function(result) {
                self.updateSinglePage('<div class="notice-cbp-singlePage"><strong>Cube Portfolio Notice:</strong> You can\'t test this feature here because some contents don\'t work fine on the admin side. <br>Please test this feature on the frontend side.</div>');
            })
            .fail(function() {
                self.updateSinglePage("Error! Please refresh the page!");
            });
    };

    cbpwOptions.options.singlePageInlineCallback = function(url, element) {

        // to update singlePage content use the following method: this.updateSinglePageInline(yourContent)
        var self = this;

        $.ajax({
                url: url,
                type: 'POST',
                dataType: 'html',
                timeout: 30000,
                data: {
                    link: url,
                    type: 'cbp-singlePageInline',
                    source: 'cubeportfolio',
                    id: cbpwOptions.id,
                    popupData: localStorage.getItem('popup')
                }
            }).done(function(result) {
                self.updateSinglePageInline('<div class="notice-cbp-singlePage"><strong>Cube Portfolio Notice:</strong> You can\'t test this feature here because some contents don\'t work fine on the admin side. <br>Please test this feature on the frontend side.</div>');
            })
            .fail(function() {
                self.updateSinglePageInline("Error! Please refresh the page!");
            });
    };

    /*********************************
     add id attr to singlePage block
     *********************************/
    // when the plugin is completed
    gridContainer.on('initComplete.cbp', function() {
        var item = $(this).data('cubeportfolio').singlePage;

        if (item) {
            item.wrap.attr('id', 'cbpw-singlePage' + cbpwOptions.id);
        }
    });

    gridContainer.cubeportfolio(cbpwOptions.options);

    cbpwOptions.initFilters = function(container) {

        if (container.hasClass('cbp-l-filters-dropdown')) {

            wrap = container.find('.cbp-l-filters-dropdownWrap');

            wrap.on({
                'mouseover.cbp': function() {
                    wrap.addClass('cbp-l-filters-dropdownWrap-open');
                },
                'mouseleave.cbp': function() {
                    wrap.removeClass('cbp-l-filters-dropdownWrap-open');
                }
            });

            filtersCallback = function(me) {
                wrap.find('.cbp-filter-item').removeClass('cbp-filter-item-active');

                wrap.find('.cbp-l-filters-dropdownHeader').text(me.text());

                me.addClass('cbp-filter-item-active');

                wrap.trigger('mouseleave.cbp');
            };

        } else {
            filtersCallback = function(me) {
                me.addClass('cbp-filter-item-active').siblings().removeClass('cbp-filter-item-active');
            };
        }

        container.on('click.cbp', '.cbp-filter-item', function() {

            var me = $(this);

            if (me.hasClass('cbp-filter-item-active')) {
                return;
            }

            // get cubeportfolio data and check if is still animating (reposition) the items.
            if (!$.data(gridContainer[0], 'cubeportfolio').isAnimating) {
                filtersCallback.call(null, me);
            }

            // filter the items
            gridContainer.cubeportfolio('filter', me.data('filter'), function() {});

        });
    };

    cbpwOptions.refreshFilters = function(container) {
        container.off('click.cbp');
        cbpwOptions.initFilters(container);
    };


    /*********************************
     add listener for filters
     *********************************/
    cbpwOptions.initFilters(filtersContainer);


    /*********************************
     activate counter for filters
     *********************************/
    gridContainer.cubeportfolio('showCounter', filtersContainer.find('.cbp-filter-item'), function() {
        /* don't exevute this snippet on admin side
        // read from url and change filter active
        var match = /#cbpf=(.*?)([#|?&]|$)/gi.exec(location.href),
            item;
        if (match !== null) {
            item = filtersContainer.find('.cbp-filter-item').filter('[data-filter="' + match[1] + '"]');
            if (item.length) {
                filtersCallback.call(null, item);
            }
        }
        */
    });
})(jQuery, window, document);
