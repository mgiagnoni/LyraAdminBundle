 /*
  * This file is part of the LyraAdminBundle package.
  *
  * Copyright 2011-2012 Massimo Giagnoni <gimassimo@gmail.com>
  *
  * This source file is subject to the MIT license. Full copyright and license
  * information are in the LICENSE file distributed with this source code.
  */
(function($, undefined) {
    $.widget("lyra.confirm", {
        options: {
            loadUrl: null,
            loadData: false,
            dialogWidth: 450
        },
        _init: function() {
            var box = $('<div></div>')
                .appendTo('body')
                .dialog({
                    modal: true,
                    autoOpen: false,
                    resizable: false,
                    minHeight: 90,
                    width: this.options.dialogWidth,
                    close: function() {$(this).remove()},
                });

            if (this.options.loadData) {
                box.load(this.options.loadUrl, this.options.loadData, this._showDialog);
            } else {
                box.load(this.options.loadUrl, this._showDialog);
            }
        },
        _showDialog: function() {
            var buttonsOpts = {};
            var buttonOk = $(this).find(".dialog-submit");
            if (buttonOk.length) {
                buttonsOpts[buttonOk.hide().val()] = function() {
                    buttonOk.click();
                    $(this).dialog('close');
                };
            }
            var buttonCanc = $(this).find(".dialog-cancel");
            buttonsOpts[buttonCanc.hide().text()] = function() {
                $(this).dialog('close');
            };
            $(this).dialog('option', {
                title: $(this).find('h1').hide().text(),
                buttons: buttonsOpts
            });
            $(this).dialog('open');
        },
    });
})(jQuery);
