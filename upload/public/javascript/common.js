// Common
$(document).ready(function() {

    // Top search
    $('#topSearchButton').click(function () {
        if ($('#topSearchQuery').val()) {
            window.location.href = $('#topSearchAction').val() + $('#topSearchQuery').val();
        } else {
            $('#topSearchForm').addClass('has-error');
        }
    });

    $('#topSearchForm').keypress(function(e){
        if (e.which == 13) {
            if ($('#topSearchQuery').val()) {
                window.location.href = $('#topSearchAction').val() + $('#topSearchQuery').val();
            } else {
                $('#topSearchForm').addClass('has-error');
            }
        }
     });


    // Bottom Search
    $('#footerSearchForm button').click(function () {
        if ($('#footerSearchForm input[name=query]').val()) {
            window.location.href = $('#footerSearchForm input[name=action]').val() + $('#footerSearchForm input[name=query]').val();
        } else {
            $('#footerSearchForm').addClass('has-error');
        }
    });

    $('#footerSearchForm').keypress(function(e){
        if (e.which == 13) {
            if ($('#footerSearchForm input[name=query]').val()) {
                window.location.href = $('#footerSearchForm input[name=action]').val() + $('#footerSearchForm input[name=query]').val();
            } else {
                $('#footerSearchForm').addClass('has-error');
            }
        }
     });


    // Submitting the report form
    $('#reportSubmit').click(function () {

        $.ajax({
            url:  'index.php?route=catalog/product/report',
            type: 'POST',
            data: { product_id: $('#reportProductId').val(), message: $('#reportMessage').val() },
            beforeSend: function () {

                // Disable send button & add the timer icon
                $('#reportSubmit').addClass('disabled').prepend('<i class="glyphicon glyphicon-hourglass"></i>');
            },
            success: function (e) {
              if (e['status'] == 200) {

                  // Hide the form
                  $('#productReport .modal-footer, #reportMessage').addClass('hide');

                  // Response output
                  $('#productReport h4').html(e['title']);
                  $('#productReport .modal-body p').html(e['message']);

              } else {
                  alert('Connection error. Please, try again later.');
              }
            },
            error: function (e) {
              alert('Internal server error. Please, try again later.');
            }
        });
    });
});

function lengthFilter(val, limit) {
    var len = val.value.replace(/\,\s/g, ',').length;
    if (len >= limit) {
      val.value = val.value.substring(0, limit);
    }
};

// Product favorite
function favorite(product_id, user_is_logged) {
    if (!user_is_logged) {
        $('#loginForm').modal('toggle');
    } else {
        $.ajax({
            url:  'index.php?route=catalog/product/favorite',
            type: 'POST',
            data: {product_id : product_id},
            success: function (e) {
              if (e['status'] == 200) {
                if (e['code']) $('#productFavoriteButton' + product_id + ' .glyphicon').removeClass('glyphicon-heart-empty').addClass('glyphicon-heart');
                else $('#productFavoriteButton' + product_id + ' .glyphicon').removeClass('glyphicon-heart').addClass('glyphicon-heart-empty');
                if (e['total']) $('#productFavoriteButton' + product_id + ' span').html(e['total']);
                else $('#productFavoriteButton' + product_id + ' span').html('')
              }
            },
            error: function (e) {
              alert('Session expired! Please login or try again later.');
            }
        });
    }
}

// Init report form
function report(product_id, product_title) {

    // Set misconfiguration
    $('#reportProductId').val(product_id);
    $('#productReport h4').html(product_title);

    // Reset previews response
    $('#productReport .modal-body p').html('');
    $('#reportSubmit').removeClass('disabled');
    $('#reportSubmit i').remove();

    // Show navigation
    $('#productReport .modal-footer, #reportMessage').removeClass('hide').val('');
}

// Zoom image
function zoomImage(url, title) {
    $('#zoomImage .modal-header h4').html(title);
    $('#zoomImage .modal-body img').attr('src', url);
}

// Zoom video
function zoomVideo(url, title) {
    $('#zoomVideo .modal-header h4').html(title);
    $('#zoomVideo .modal-body iframe').attr('src', url);

    $('#zoomVideo').on('hidden.bs.modal', function () {
        $('#zoomVideo .modal-body iframe').attr('src', false);
    });
}

// Zoom audio
function zoomAudio(url, title) {
    $('#zoomAudio .modal-header h4').html(title);
    $('#zoomAudio .modal-body iframe').attr('src', url);

    $('#zoomAudio').on('hidden.bs.modal', function () {
        $('#zoomAudio .modal-body iframe').attr('src', false);
    });
}

function timer(sec, block, direction) {
    var time    = sec;
    direction   = direction || false;

    var hour    = parseInt(time / 3600);
    if ( hour < 1 ) hour = 0;
    time = parseInt(time - hour * 3600);
    if ( hour < 10 ) hour = '0'+hour;

    var minutes = parseInt(time / 60);
    if ( minutes < 1 ) minutes = 0;
    time = parseInt(time - minutes * 60);
    if ( minutes < 10 ) minutes = '0'+minutes;

    var seconds = time;
    if ( seconds < 10 ) seconds = '0'+seconds;

    //block.innerHTML = hour+':'+minutes+':'+seconds;
    block.innerHTML = minutes+':'+seconds;

    if ( direction ) {
        sec++;

        setTimeout(function(){ timer(sec, block, direction); }, 1000);
    } else {
        sec--;

        if ( sec > 0 ) {
            setTimeout(function(){ timer(sec, block, direction); }, 1000);
        } else {
            location.reload();
        }
    }
}
