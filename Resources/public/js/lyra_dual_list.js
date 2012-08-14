/*
 * jQuery Dual Listbox UI widget
 * Copyright 2012 Massimo Giagnoni
 *
 * License: MIT
 * http://opensource.org/licenses/mit-license.php
 *
 */
(function($, undefined) {
    $.widget("lyra.dlist", {
        options: {
            lists: {
                available: {
                    id: '{id}-available',
                    class: 'dlist-available'
                },
                selected: {
                    id: '{id}-selected',
                    class: 'dlist-selected'
                }
            },
            buttonWrapper: '<div class="dlist-button-wrapper"></div>',
            buttons: {
                select: {
                    text: 'Add >',
                    id: '{id}-select',
                    class: 'dlist-button'
                },
                remove: {
                    text: '< Remove',
                    id: '{id}-remove',
                    class: 'dlist-button'
                },
                removeAll: {
                    text: '<< Remove All',
                    id: '{id}-remove-all',
                    class: 'dlist-button'
                },
                selectAll: {
                    text: 'Add All >>',
                    id: '{id}-select-all',
                    class: 'dlist-button'
                }
            }
        },
        lists: {},
        buttons: {},
        _create: function() {
            var self = this;

            this.element.hide();

            $.each(this.options.lists, function(key, value) {
                self.lists[key] = self._createList(value);
            });

            this.lists.selected.find('option').remove();
            this.lists.available.find('option').each(function() {
                $(this).data('orig_idx', $(this).index());
                $(this).bind('dblclick.dlist', function(e) {
                    self._moveSelection($(this).parent().is(self.lists.selected) ? 'remove' : 'select');
                });
            });

            this._moveSelection('select');
            this.element.after(this.lists.available, this.lists.selected);
            this.btnWrapper = $(this.options.buttonWrapper)
                .insertAfter(this.lists.available);

            $.each(this.options.buttons, function(key, value){
                if (value) {
                    self.buttons[key] = (self._createButton(key, value));
                }
            });
        },
        _moveSelection: function(action) {
            var self = this;

            var lists = [this.lists.selected,this.lists.available];
            if (action == 'select' || action == 'selectAll') {
                lists.reverse();
            }

            if (action == 'selectAll' || action == 'removeAll') {
                lists[0].find('option').attr('selected', true);
            }

            var opts = lists[0].find('option:selected').attr('selected', false);
            if (opts.length == 0) {
                return;
            }

            opts.each(function() {
                var opt = $(this);
                lists[1].find('option').each(function() {
                    if ($(this).data('orig_idx') > opt.data('orig_idx')) {
                        opt.insertBefore($(this));
                        return false;
                    }
                });
                if (lists[1].find('option').index(opt) == -1) {
                    // option not yet inserted, append at bottom of list
                    opt.appendTo(lists[1]);
                }
                // select/deselect corresponding option in 'real' listbox
                self.element.find('option').eq(opt.data('orig_idx')).attr('selected', action == 'select' || action == 'selectAll')
            });
        },
        _createList: function(options) {
            var list = this.element.clone();
            list.removeAttr('name').show();

            if (options.id) {
                var id = this.element.attr('id');
                list.attr('id', options.id.replace(/\{id\}/g, id ? id : ''));
            } else {
                list.removeAttr('id');
            }

            if (options.class) {
                list.addClass(options.class);
            }

            return list;
        },
        _createButton: function(action, options) {
            var self = this;

            var id = this.element.attr('id'),
                button = $('<button></button>')
                .attr('id', options.id.replace(/\{id\}/g, id ? id : ''))
                .text(options.text)
                .bind('click.dlist', function(e) {
                    if ($.isFunction(options.click)) {
                        options.click.apply(self.element, arguments)
                    } else {
                        e.preventDefault();
                        self._moveSelection(action);
                    }
                }).appendTo(this.btnWrapper);

            if (options.class) {
                button.addClass(options.class);
            }

            return button;
        },
        selectAll: function() {
            this._moveSelection('selectAll');
        },
        select: function() {
            this._moveSelection('select');
        },
        removeAll: function() {
            this._moveSelection('removeAll');
        },
        remove: function() {
            this._moveSelection('remove');
        },
        button: function(name) {
            return this.buttons[name];
        },
        list: function(name) {
            return this.lists[name];
        },
        destroy: function() {
            $.each(this.lists, function(key, list) {
                list.remove();
            });

            $.each(this.buttons, function(key, button) {
                button.remove();
            });

            this.btnWrapper.remove();
            this.element.show();
            $.Widget.prototype.destroy.call(this);
        }
    });
})(jQuery);
