var Numbers = new Map();
var lastFetched = '2021-01-01'
var activeNumber = $('.number-list-item:nth-child(1)').data('number');
var refreshTimer;

//Number Click
$('.number-list-item').on('click', handleActiveNumberChange);
function handleActiveNumberChange(event) {
    $('.number-list-item').removeClass('active');
    $(event.target).addClass('active');
    activeNumber = $(event.target).data('number')

    if (Numbers.has(activeNumber)) {
        let number = Numbers.get(activeNumber);
        if (number.more) {
            $('#load-more').text('Load More')
        } else {
            $('#load-more').text('No More Message')
        }
        populateMessages(activeNumber);
    }
    $('#header-number').text(activeNumber);
    // Marking message read.
    if ($(`.number-list-item[data-number="${activeNumber}"]>div>div:nth-child(1)>span.pill-text`).text() != '') {
        markRead(activeNumber);
    }
    $(`.number-list-item[data-number="${activeNumber}"]>div>div:nth-child(1)>span.pill-text`).text('')
}

//Search
$('#searchip').on("keyup", (event) => {
    $('.number-list-item').addClass('d-none');
    $(".number-list-item[data-number]:contains(" + event.target.value + ")").removeClass('d-none');
});

//Refresh Change
$('#refreshtime').on('change', () => {
    if ($('#refreshtime').val() == 'OFF') {
        clearInterval(refreshTimer);
    } else {
        clearInterval(refreshTimer);
        refreshTimer = setInterval(getAllMessages, $('#refreshtime').val());
    }
})

//loadmore click

$('#load-more').on('click', () => {
    let number = Numbers.get(activeNumber);
    var fData = new FormData();
    fData.append('number', activeNumber);
    fData.append('offset', number.offset);
    fData.append('till', number.till.toISOString());
    let firstChatMessage = $('.chat-message:first');
    $.ajax({
        method: "POST",
        data: fData,
        contentType: false,
        processData: false,
        url: 'inbox.php?action=getmessage',
        success: function (response) {
            response = JSON.parse(response)
            if (response.success) {
                //Setting flag for no more messages
                if (response.data.length < 20) {
                    number.more = false;
                    number.offset += response.data.length;
                } else {
                    number.offset += 20;
                }

                //add messages to number
                response.data.forEach(m => {
                    let message = {
                        message_uuid: m.message_uuid,
                        message_direction: m.message_direction,
                        message_start_stamp: new Date(m.message_start_stamp),
                        message_party: activeNumber,
                        message_text: m.message_text,
                        message_delivered: m.message_delivered,
                    };
                    number.messages.set(m.message_uuid, message)
                });
                if (!number.more) {
                    $('#load-more').text('No More Messages');
                }
            } else {
                console.log(response)
            }
        },
        error: function (response) {
            console.log("Error", response.message);
        }

    });
    sortMessage(activeNumber);
    populateMessages(activeNumber);
});


//New Message

$('#newnumberbtn').on('click', () => {
    if ($('#contact-list>div.number-list-item:not(.d-none)').length == 0) {
        var num = $('#searchip').val();
        addNewNumberToList(num, new Date(), "");
        Numbers.set(num, { messages: new Map(), offset: 0, till: new Date(), more: false })
    }
});


//Send Message
$('#sendbutton').on('click', (event) => {
    var message = $('#mess-text').val();
    var fromnumber = $('#from-number').val();
    if (message == '') return
    // $('.status-text').append(`<div class="spinner-border" role="status">
    //     <span class="sr-only">Loading...</span></div>`);
    var fData = new FormData();
    fData.append('fromnumber', fromnumber);
    fData.append('tonumber', activeNumber);
    fData.append('message', message);
    //Sending Message
    $.ajax({
        method: "POST",
        data: fData,
        contentType: false,
        processData: false,
        url: 'send.php?action=send',
        success: function (response) {
            if (response.success) {
                $('#mess-text').val(''); // clear message box
                if ($('#refreshtime').val() != 'OFF') { // Auto ARefresh is off.
                    getAllMessages();
                }
            } else {
                display_message('Error Sending message :' + response.message, 'negative', 2000)
            }
        },
        error: function (response) {
            console.log("Error", response.message);
        }
    });
});

async function getAllMessages() {
    var fData = new FormData();
    fData.append('fetched', lastFetched);
    $.ajax({
        method: "POST",
        data: fData,
        contentType: false,
        processData: false,
        url: 'inbox.php?action=getallmessage',
        success: function (response) {
            response = JSON.parse(response)
            if (response.success) {
                response.data.forEach(m => {
                    let partyNumber = m.message_direction == 'IN' ? m.message_from_number : m.message_to_number
                    let message = {
                        message_uuid: m.message_uuid,
                        message_direction: m.message_direction,
                        message_start_stamp: new Date(m.message_start_stamp),
                        message_party: partyNumber,
                        message_text: m.message_text,
                        message_delivered: m.message_delivered,
                    };
                    if (Numbers.has(partyNumber)) {
                        Numbers.get(partyNumber).messages.set(m.message_uuid, message)
                    } else {
                        addNewNumberToList(partyNumber, m.message_start_stamp, m.message_text)
                        let messages = new Map();
                        messages.set(m.message_uuid, message)
                        Numbers.set(partyNumber, { messages: messages, offset: 0, till: new Date(), more: true })
                    }

                    if (partyNumber != activeNumber) {
                        //Add Notification of incomming message
                        if (m.message_direction == 'IN' && m.message_delivered == null) {
                            let count = $(`.number-list-item[data-number="${partyNumber}"]>div>div:nth-child(1)>span.pill-text`).text() == '' ? 0 : parseInt($(`.number-list-item[data-number="${partyNumber}"]>div>div:nth-child(1)>span.pill-text`).text()) + 1;
                            console.log(count)
                            $(`.number-list-item[data-number="${partyNumber}"]>div>div:nth-child(1)>span.pill-text`).text(count)
                        }
                    }
                });
            } else {
                console.log(response)
            }
            [...Numbers.keys()].forEach(num => { sortMessage(num) });
            populateMessages(activeNumber);
        },
        error: function (response) {
            console.log("Error", response.message);
        }
    });
    let now = new Date();
    lastFetched = now.toISOString()

};

function populateMessages(number) {
    $('#messages>div.chat-message').remove();
    Numbers.get(number).messages.forEach(mes => {
        let senttime = convertDate(mes.message_start_stamp);
        $('#messages').append(`<div class="chat-message chat-message-${mes.message_direction == "OUT" ? "right" : 'left'} pb-4" data-muuid="${mes.message_uuid}" data-timestamp="${mes.message_start_stamp}">
			<div class="flex-shrink-1 rounded py-2 px-3 mr-3 ${mes.message_direction == "OUT" ? "bg-right" : 'bg-left'}">
		    	<div class="message-number font-weight-bold mb-1">${mes.message_direction == "OUT" ? "You" : mes.message_party}</div>
					<span class="message-text">${mes.message_text}</span>
                    <div class="text-muted small text-nowrap mt-2 text-right">${mes.message_direction == 'OUT' ? 'Sent ' + senttime : 'Received ' + senttime}
							${mes.message_delivered != null && mes.message_direction == 'OUT' ? 'Delivered ' + convertDate(mes.message_delivered) : ''}
                    </div>
				</div>								
			</div>`);
    });
    //Scroll to bottom
    var objDiv = document.getElementById("messages");
    objDiv.scrollTop = objDiv.scrollHeight;

}

function markRead(number) {
    var fData = new FormData();
    fData.append('number', number);
    $.ajax({
        method: "POST",
        data: fData,
        contentType: false,
        processData: false,
        url: 'inbox.php?action=markread',
        success: function (response) {
        },
        error: function (response) {
            console.log("Error", response.message);
        }
    });
};

function addNewNumberToList(number, lastmessage, text) {
    $('#contact-list').append(`<div class="number-list-item list-group-item border-0 my-1 pl-2 '.($index==0?'active':'').'" data-number="${number}">
									<div class="d-flex flex-column align-items-start" style="pointer-events: none;">
										<div class="d-flex flex-row w-100 justify-content-between" style="pointer-events: none;">
											<span class="list-number">${number}</span>
											<span class="badge badge-pill badge-danger pill-text"></span>											
										</div>
										<div class="d-flex flex-row w-100 justify-content-between" style="pointer-events: none;">							
											<span class="list-text">${text.substring(0, 50)}</span>
											<span class="list-time my-auto">${lastmessage.toLocaleString('en-ca').replace(/[,p\.m]/g, '').trim()}</span>
										</div>															
									</div></div>`);
    $('.number-list-item').off('click', handleActiveNumberChange);
    $('.number-list-item').on('click', handleActiveNumberChange);
}
//Initialization
//add prefetched numbers to numbers
$('.number-list-item').each((ind, num) => {
    Numbers.set($(num).data('number'), { messages: new Map(), offset: 0, till: new Date(), more: true });
})


getAllMessages();

if ($('#refreshtime').val() != 'OFF') refreshTimer = setInterval(getAllMessages, $('#refreshtime').val());

//Helpers

function sortMessage(number) {
    if (Numbers.has(number)) {
        let messages = Numbers.get(number).messages;
        Numbers.get(number).messages = new Map([...messages].sort((a, b) => a['1'].message_start_stamp - b['1'].message_start_stamp));
    }
}

function convertDate(date) {
    if (typeof (date) == 'string') date = new Date(date);
    var dateStr =
        ("00" + date.getDate()).slice(-2) + "-" +
        ("00" + (date.getMonth() + 1)).slice(-2) + "-" +
        date.getFullYear() + " " +
        ("00" + date.getHours()).slice(-2) + ":" +
        ("00" + date.getMinutes()).slice(-2) + ":" +
        ("00" + date.getSeconds()).slice(-2);
    return dateStr
}