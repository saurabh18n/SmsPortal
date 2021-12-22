
//negative,Positive,default
$('#preview_btn').on('click', (event) => {
    if (!validateInput()) {
        return;
    }
    //Processing the numbers
    let numbersText = $('#tonumber').val()
    numbersText = numbersText.replace(new RegExp(';', 'g'), ',');
    let numbers = numbersText.split(',')
    $('#sms_pre').hide();
    //Populate numbers

    numbers.forEach(num => {
        let templet = `<tr>;
						<td class="vtable" valign="top" align="left" nowrap="nowrap">
							<input class="formfld w-100 sms-to-number form-control" type="text" value=${num}>
						</td>
						<td class="vtable status-text" valign="top" align="left">
                            Ready to be sent
						</td>
						<td class="vtable" valign="top" align="left" nowrap="nowrap">
						</td>
					</tr>`;
        $('#sms_post_priview tbody').append(templet)
        $('#sms_post_priview_messagetext').val($('#messagetext').val());
    });
    $('#sms_post').show();
});

//Handle Resend Click
function handleResendSms(messid, row) {
    var fData = new FormData();
    fData.append('messid', messid);
    $.ajax({
        method: "POST",
        data: fData,
        contentType: false,
        processData: false,
        url: 'send.php?action=resend',
        success: function (response) {
            handleSendResponce(response, row);
        },
        error: function (response) {
            console.log("Error", response.message);
        }
    });
};

//handle Send responce
function handleSendResponce(response, row) {
    if (response.success) {
        row.find('td:nth-child(2)').text(response.message)
    } else {
        row.find('td:nth-child(2) div').remove();
        row.find('td:nth-child(2)').text(response.message)
        var ip = document.createElement('input')
        ip.setAttribute("type", "button");
        ip.setAttribute("class", "btn my-1");
        ip.setAttribute("style", "width:100px");
        ip.setAttribute("value", "Retry")
        ip.addEventListener('click', () => {
            handleResendSms(response.data.messid, row);
        });
        row.find('td:nth-child(3) input').remove();
        row.find('td:nth-child(3)').append(ip);
    }
}


//Send Button click handler
$('#send_btn').on('click', (event) => {
    $('#send_btn').prop('disabled', true)
    $('.sms-to-number').prop('disabled', true)
    $('#sms_post_priview_messagetext').prop('disabled', true)
    $('.status-text').text('');
    $('.status-text').append(`<div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span></div>`);
    var message = $('#sms_post_priview_messagetext').val();
    $('.sms-to-number').each((i, inp) => {
        let number = inp.value;
        var fData = new FormData();
        fData.append('number', number);
        fData.append('message', message);
        let row = $(inp).parent().parent()
        //Sending Message
        $.ajax({
            method: "POST",
            data: fData,
            contentType: false,
            processData: false,
            url: 'send.php?action=send',
            success: function (response) {
                handleSendResponce(response, row);
            },
            error: function (response) {
                console.log("Error", response.message);
            }
        });
    });
});

// Function to validate input to send message
function validateInput() {
    let rexp = new RegExp("^\\+\\d{11}([,|;]\\+\\d{11})*$")
    if ($('#tonumber').val() === '') {
        display_message('To Number can not be empty Please provide numbers to which sms is to be sent', 'negative', 3000)
        return false
    } else if (!rexp.test($('#tonumber').val())) {
        display_message('Please enter numbers in correct format  +12152565548 seprated by (,) or (;) as +12152565548;+12152565549 or +12152565548,+12152565549', 'negative', 3000)
        return false
    } else if ($('#messagetext').val() === "") {
        display_message('Please enter the message', 'negative', 3000)
        return false
    } else {
        return true
    }
}