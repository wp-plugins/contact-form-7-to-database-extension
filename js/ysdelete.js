function ysDelete(urlPrefix, inputId, buttonId, resultsId) {
    $ = jQuery;
    $(buttonId).attr('disabled', 'disabled');
    $.ajax({
               url: urlPrefix + $(inputId).val(),
               success: function(data, textStatus, jqXHR) {
                   $(resultsId).text(data);
                   $(buttonId).removeAttr('disabled');
               },
               error: function(jqXHR, textStatus, errorThrown) {
                   $(resultsId).text('HTTP Error: ' + textStatus + ': ' + errorThrown);
                   $(buttonId).removeAttr('disabled');
               }
           });
}
