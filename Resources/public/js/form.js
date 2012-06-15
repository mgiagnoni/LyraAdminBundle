jQuery().ready(function() {
    $('#ly-top-bar')
        .addClass('ui-widget ui-state-default');

    $('#ly-top-bar .user-info .user')
        .prepend("<span class='ui-icon ui-icon-person'></span>");

    $('#ly-form-wrapper')
        .addClass('ui-widget ui-widget-content ui-corner-all');

    $('#ly-form-wrapper h1')
        .addClass('ui-widget-header ui-corner-all');

    $('.ly-form fieldset')
        .addClass('ui-widget-content');

    $('.button')
        .each(function() {
            // Extracts icon name from class attribute
            var icon = /ui-icon-(\S+)/.exec(this.className);
            $(this).button({
                icons: {
                  primary: icon !== null ? 'ui-icon-' + icon[1] : null
                }
            });
        });

    $('.action-save')
        .click(function(e) {
            e.preventDefault();
            $('.ly-form').submit();
        })

    $(".ly-form legend").each(function() {
      $(this).parent().before(
      $("<h3 class='ui-widget-header ui-corner-top'></h3>")
        .click(function() {
          var fs = $(this).next();
          var sfx = ['n','s'];
          fs.toggle();
          $(this).find("span")
            .addClass('ui-icon-triangle-1-' + (fs.css('display') == 'none' ? sfx.pop() : sfx.shift()))
            .removeClass('ui-icon-triangle-1-' + sfx[0]);
        })
        .disableSelection()
        .text($(this).hide().text())
        .append("<span class='ui-icon ui-icon-triangle-1-n'></span>")
      )
    });

    $('ul.error-list li').addClass('ui-state-error');

    $('.date-picker').each(function() {
        var format = $(this).data('date');
        $(this).datepicker({
            dateFormat : format,
        })
    });

    $('.datetime-picker').each(function() {
        var dateFormat = $(this).data('date');
        var timeFormat = $(this).data('time');
        var ampm = $(this).data('ampm');
        $(this).datetimepicker({
            dateFormat : dateFormat,
            timeFormat : timeFormat,
            ampm : ampm == '1',
        })
    });

    // Modal dialog for confirmation messages

    $(".dialog")
        .click(function(e) {
            e.preventDefault();

            $("<div></div>")
                .appendTo("body")
                .load(this.href, showDialog);
        });

    var showDialog = function() {
        $(".buttons", this).hide()
        var buttonOk = $("input[type='submit']", this);
        var buttonsOpts = {};
        buttonsOpts[buttonOk.hide().val()] = function() {
            buttonOk.click();
            $(this).dialog("close");
        };
        buttonsOpts[buttonOk.next().hide().text()] = function() {
            $(this).dialog("close");
        };
        $(this).dialog({
            modal: true,
            autoOpen: true,
            resizable: false,
            minHeight: 90,
            width: 450,
            title: $("h1", this).hide().text(),
            close: function() {$(this).remove()},
            buttons: buttonsOpts
        })
    };
});
