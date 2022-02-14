let block = $('.import-result');

function printImportResult(data) {
    function getHead() {
        $(div).append('<p>ID' + data.id + '</p>');
        $(div).append('<p>Status: ' + data.status + '</p>');
    }

    function getFailedInfo() {
        let params = ['Delimiter', 'Enclosure', 'Escape', 'Have header'];
        $(div).addClass('border-danger');
        $(div).append('<p>Settings:</p>');

        for(let i = 0; i < params.length; i++) {
            $(div).append('<p>' + params[i] + ': ' + data.settings[i] + '</p>');
        }
    }

    function getSuccessInfo() {
        let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

        $(div).addClass('border-success');
        $(div).append('<p>Requests: ' + (data.failed.length + data.complete.length) + '</p>');
        $(div).append('<p>Count of valid rows: ' + data.complete.length + '</p>');
        $(div).append('<p>Count of invalid rows: ' + data.failed.length + '</p><ul>');
        for(let row of data.failed) {
            let li = $('<li style="margin-left: 10px"></li>');
            for(let i = 0; i < rowFields.length; i++) {
                $(li).append(row[rowFields[i]] + ', ');
            }
            $(div).append(li);
        }
        $(div).append('</ul>');
    }

    let div = $('.request-' + data.id);
    $(div).html('');

    getHead();

    if (data.status === 'STATUS_FAILED') {
        getFailedInfo();
    } else if (data.status === 'STATUS_IMPORTED') {
        getSuccessInfo();
    }
}

function createBlocksWithRequestsInfo(ids) {
    for(let id of ids) {
        let request = $('<div class="request-' + id + ' border border-1 p-2 my-1 rounded"></div>');
        $(request).append('ID' + id + ' (Wait result)');
        block.append(request);
    }
}

$(() => {
    setEventSource('#import-upload-url', response => {
        let result = JSON.parse(response.data);
        setTimeout(function waitCreateBlock() {
            if($('.request-' + result.id).length > 0) {
                printImportResult(result);
            } else {
                waitCreateBlock();
            }
        }, 100);
    })
})

$('#import-form').on('submit', () => {
    block.html('');

    let data = new FormData();
    let token = $('#import_by_csv__token').val();

    data.append('token', token);

    for(let file of $('#import_by_csv_file')[0].files) {
        data.append('files[]', file);
    }

    for(let setting of $('.csv-sets > fieldset > div')) {
        let params = $(setting).find('input');
        let set = $(params[0]).val() + $(params[1]).val() + $(params[2]).val() + +$(params[3]).is(':checked');
        data.append('settings[]', set);
    }

    $.ajax({
        url: Routing.generate('ajax_import_upload'),
        method: 'post',
        contentType: false,
        processData: false,
        data: data,
        success: (res) => {
            let form = $('#import-form')
            form.wrap('<form>').closest('form').get(0).reset();
            form.unwrap();
            $('.csv-sets').html('')
            let ids = res.ids
            if(ids.length !== 0) {
                createBlocksWithRequestsInfo(ids);
            }
        },
        error: () => {
            console.log('Error!!!');
        }
    });

    return false;
})