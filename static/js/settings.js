$('#btn_add_new,#btn_add_new_back').on('click', () => {
    $('#numlist').toggleClass('d-none');
    $('#addnum').toggleClass('d-none');
});


$('.number-edit-btn').on('click', (event) => {
    let data = $(event.target).data();
    $('#number_name').val(data.name);
    $('#api_user_number').val(data.number);
    $('#hid-uuid').val(data.uuid)
    $('#add-form').attr('action', 'settings.php?action=update');
    $('#numlist').toggleClass('d-none');
    $('#addnum').toggleClass('d-none');
});



$('.number-enable-check').on('change', (event) => {
    let data = $(event.target).data();
    let checked = $(event.target).is(":checked");
    $('#hid-uuid').val(data.uuid)
    if (checked) {
        $('#add-form').attr('action', 'settings.php?action=enable');
    } else {
        $('#add-form').attr('action', 'settings.php?action=disable');
    }
    $('#add-new-submit').click();
});

$('.number-default-check').on('change', (event) => {
    let data = $(event.target).data();
    let checked = $(event.target).is(":checked");
    $('#hid-uuid').val(data.uuid)
    if (checked) {
        $('#add-form').attr('action', 'settings.php?action=default');
    } else {
        $('#add-form').attr('action', 'settings.php?action=undefault');
    }
    $('#add-new-submit').click();

});