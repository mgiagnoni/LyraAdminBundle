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

    $('<button></button>')
        .text($(".ly-form input[type='submit']").attr('value'))
        .button({
            icons: {primary: 'ui-icon-disk'}
        })
        .click(function(e) {
            e.preventDefault();
            $('.ly-form').submit();
        })
        .appendTo($('.form-actions'));

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
});
