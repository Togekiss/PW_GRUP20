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

$(document).on('click', '.load_more_comments', function(event) {
    event.preventDefault();
    $.get("/ajax/comments/" + event.target.id, function (data) {
        $('#comments-list').html(data);
    });
});

$(document).on('click', '.like', function(event) {
    event.preventDefault();
    $.get("/like/" + event.target.id, function (data) {
        $('#reload-' + event.target.id).html(data);
    });
});