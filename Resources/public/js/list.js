jQuery().ready(function() {
    $('#ly-list-wrapper')
       .addClass('ui-widget ui-corner-all');

    $('#ly-list-wrapper h1')
        .addClass('ui-widget-header ui-corner-all');

    $('table.ly-list')
        .addClass('ui-widget');

    $('table.ly-list td')
        .addClass('ui-widget-content');

    // Sortable headers
    $('table.ly-list th.sorted-asc')
        .append("<span class='ui-icon ui-icon-triangle-1-s'></span>");

    $('table.ly-list th.sorted-desc')
        .append("<span class='ui-icon ui-icon-triangle-1-n'></span>");

    $('table.ly-list th').not('.sorted-asc, .sorted-desc').has('a')
        .append("<span class='ui-icon ui-icon-triangle-2-n-s'></span>");

    $('table.ly-list th')
        .addClass('ui-widget-content ui-state-default');

    // Flash messages
    $('.flash-messages .success').addClass('ui-state-highlight');

    // Actions buttons
    var batchSubmit = false;
    $('input.button')
        .each(function() {
            $('<button></button>')
                .attr('class', $(this).attr('class'))
                .text($(this).attr('value'))
                .click(function(e) {
                    e.preventDefault();
                    batchSubmit = false;
                    $(this).prev().click();
                })
                .insertAfter($(this).hide())
        });

    $('a.button, input.button + button').each(function() {
        // Extracts icon name from class attribute
        var icon = /ui-icon-(\S+)/.exec(this.className);
        $(this).button({
            disabled: $(this).hasClass('disabled') ? true : false,
            text: $(this).hasClass('icon-only') ? false : true,
            icons: {
                primary: icon !== null ? 'ui-icon-' + icon[1] : null
            }
        });
    });

    $(".action-delete")
        .click(function(e) {
            e.preventDefault();

            $("<div></div>")
                .appendTo("body")
                .load(this.href, showDialog);
        })

    // Batch actions
    $('.batch-actions select').lyraselect();

    // 'Go' buttom
    $('.batch-actions input[type="submit"]')
        .button()
        .click(function() {
            batchSubmit = true;
        });

    $("#ly-list-wrapper form").submit(function () {
        if (!batchSubmit) {
            return true;
        }

        $('.flash-messages div').remove();
        if ($(this).find(".batch-actions select").val() == '') {
            $('<div></div>')
                .text(batchMessages.noAction)
                .addClass('ui-state-error')
                .append('<span class="ui-icon ui-icon-alert"></span>')
                .appendTo($(this).find('.flash-messages'));

            return false;
        }

        var ids = [];
        $(this).find('input[name="ids[]"]:checked').each(function() {
            ids.push($(this).val());
        });
        if(ids.length == 0) {
            $('<div></div>')
                .text(batchMessages.noSelection)
                .addClass('ui-state-error')
                .append('<span class="ui-icon ui-icon-alert"></span>')
                .appendTo($(this).find('.flash-messages'));

            return false;
        }

        if($(this).find(".batch-actions select").val() != 'delete') {
            return true;
        }

        $("<div></div>")
            .appendTo("body")
            .load(this.action, {'action[batch]' : '','ids[]' : ids, 'batch_action' : 'delete'}, showDialog);

        return false;
    });

    // Select all
    $('.batch-select-all input[type="checkbox"]')
    .change(function() {
        $('.batch-select')
            .attr("checked", this.checked);
      }
    );

    // Modal dialog for confirmation messages

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
            width: 400,
            title: $("h1", this).hide().text(),
            close: function() {$(this).remove()},
            buttons: buttonsOpts
        })
    }
});
