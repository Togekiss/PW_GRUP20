/**
 * Created by Marta on 03/04/2017.
 */

$('.imageupload').imageupload({
    maxFileSizeKb: 4048
});


$('#register-form').on('submit', function(event) {

    var bdate = new Date($('#birthdate').val());

    if (bdate > Date.now()) {
        event.preventDefault();
        $('#date-help').text("This date is a future one!");
    }

});

$(document).on('click', '#register_submit', function(event) {
    event.preventDefault();
    alert("analysing");
    var bdate = document.getElementById("birthdate").value;


    if (bdate > Date.now()) {

        alert("Future date!");
    }

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
    console.log("Marta");
    $.get("/like/" + event.target.id, function (data) {
        $('#reload-' + event.target.id).html(data);
    });
});

$('.send-comment').on('submit', function(event) {
    event.preventDefault();

    $.ajax({
       type:"POST",
        url: "/comment/" + event.target.id,
        data: $('.send-comment').serialize(),
        success: function (data) {
            $('#reload-' + event.target.id).html(data);
        }
    });
});

$('.remove-image').on('click', function(event) {
    event.preventDefault();

    if (confirm("Are you sure about deleting this image?") === true) {
        $.get("/remove/" + event.target.id, function (data) {
            location.reload();
        });
    }
});

var kk = 0;
var kk2 = 0;

$('.modify').on('click', function(event) {
    event.preventDefault();
    kk = event.target.id;
});

$('.edit-image').on('click', function(event) {
    event.preventDefault();
    kk2 = event.target.id;
});

$('#modifyform').on('submit', function(event) {
    event.preventDefault();

    $.ajax({
        type:"POST",
        url: "/modify-comment/" + kk,
        data: $('#modifyform').serialize()
    });

    location.reload();
});

$(document).ready(function (e) {
    $('#editform').on('submit',(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type:'POST',
            url: $(this).attr('action') + kk2,
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
                location.reload();
            },
            error: function(data){
                location.reload();
            }
        });
    }));
});

$(document).ready(function (e) {
    $('#edituserform').on('submit',(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
                location.reload();
            },
            error: function(data){
                location.reload();
            }
        });
    }));
});


/*
$(document).on('click', '.send-comment', function(event) {
    //event.preventDefault();
    $.post("/comment/" + event.target.id, function (data) {
        alert("commented");
        $('#reload-' + event.target.id).html(data);
    });
});
*/