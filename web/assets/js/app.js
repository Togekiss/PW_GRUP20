/**
 * Created by Marta on 03/04/2017.
 */

$('.imageupload').imageupload({
    maxFileSizeKb: 4048
});

$(document).on('click', '#load_more', function(event) {
    event.preventDefault();
    $.get("/ajax/images", function (data) {
        $('#recent-images').html(data);
    });
});

/*
$('#load_more').click(function(event) {
    event.preventDefault();
    $.get("/ajax/images", function (data) {
        $('#recent-images').html(data);
    });
});
*/