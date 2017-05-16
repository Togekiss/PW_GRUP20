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
    alert(event.target.id);
    $.get("/ajax/comments/" + event.target.id, function (data) {
        alert("LOADED");
        $('#comments-list').html(data);
    });
});
