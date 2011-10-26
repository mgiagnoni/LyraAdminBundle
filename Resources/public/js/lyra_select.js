(function($) {

$.widget("ui.lyraselect", {

    options: {

    },

    _create: function() {
        var self = this;

        this.curListIdx = 0;
        this.nbItems = 0;

        this.lyselect = $('<a class="' + this.widgetBaseClass + ' ui-widget ui-state-default ui-corner-all" href="#" tabindex="0" role="button"></a>')
            .insertAfter(this.element);

        var tabindex = this.element.attr('tabindex');
		if (tabindex) {
			this.lyselect.attr('tabindex', tabindex);
		}

        this.lyselect.prepend('<span class="' + this.widgetBaseClass + '-icon ui-icon ui-icon-triangle-1-s" />')
        this.lyselect.prepend('<span class="' + this.widgetBaseClass + '-current" />');

        this.lyselect
            .bind('click.lyraselect', function() {
                self._toggle();
                return false;
            })
            .bind('keydown.lyraselect', function(e) {
                switch (e.keyCode) {
                    case $.ui.keyCode.DOWN:
                        //step = 1;
                        self._moveSelection(1);
                        return false;
                        break;
                    case $.ui.keyCode.UP:
                        //step = -1;
                        self._moveSelection(-1);
                        return false;
                        break;
                    case $.ui.keyCode.ENTER:
                        self._toggle();
                        return false;
                        break;
                    case $.ui.keyCode.ESCAPE:
                        self.lyselect_items.hide();
                        break;
                }
                return true;
            });

        $(document).bind("click.lyraselect", function(event) {
			 self.lyselect_items.hide();
             });

        this.element.hide();

        this.lyselect_items = $('<ul class="' + this.widgetBaseClass + '-items ui-widget ui-widget-content"></ul>').appendTo('body');

    },

    _init: function() {
        var self = this;

        this.element.find('option').each(function() {
            self.nbItems++;
            $("<li class='ui-state-default'>" + $(this).text() + "</li>")
                .bind('mouseover.lyraselect', function(event) {
                    self._showSelection($(this).index());

                })
                .bind('click.lyraselect', function() {
                    self.curListIdx = $(this).index();
                    self._doSelect();
                    self.lyselect_items.hide();
                    return false;
                })
                .appendTo(self.lyselect_items);
        });

        self.lyselect.find('span')
            .text(self.lyselect_items.find('li').eq(0).text());
    },

    _toggle: function() {
        this.lyselect_items.toggle();
        this.lyselect.focus();

        if (this.lyselect_items.css('display') != 'none') {
            this._showSelection(this.curListIdx);

            this.lyselect_items.position({
                'my': 'left top',
                'at': 'left bottom',
                'of': this.lyselect,
            });
        }
    },

    _moveSelection: function(step) {
        var selIdx = this.lyselect_items.find('li.ui-state-hover').index();
        selIdx += step;
        if (selIdx < 0 || selIdx >= this.nbItems) {
            return;
        }
        this.curListIdx = selIdx;
        this._showSelection(selIdx);
        this._doSelect();
    },

    _showSelection: function(idx) {
        this.lyselect_items.find('li')
        .eq(idx)
        .addClass("ui-state-hover")
        .siblings()
        .removeClass("ui-state-hover");

    },

    _doSelect: function() {
        this.lyselect.find('span')
          .text(this.lyselect_items.find('li').eq(this.curListIdx).text());
        this.element.find('option')
          .eq(this.curListIdx)
          .attr("selected", "selected");
    }
});

})(jQuery);
