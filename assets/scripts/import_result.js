let block = $('.import-result')

function getImportResultToHtml(data) {
    let div = $('<div class="border border-1 p-2 my-1 rounded"></div>')
    $(div).append('<p>ID' + data.id + '</p>')
    $(div).append('<p>Status' + data.status + '</p>')

    if (data.status === 'STATUS_FAILED') {
        $(div).addClass('border-danger')
        $(div).append('<p>Settings:</p>')
        $(div).append('<p>Delimiter: ' + data.settings[0] + '</p>')
        $(div).append('<p>Enclosure: ' + data.settings[1] + '</p>')
        $(div).append('<p>Escape: ' + data.settings[2] + '</p>')
        $(div).append('<p>Have header: ' + data.settings[3] + '</p>')

    } else if (data.status === 'STATUS_IMPORTED') {
        $(div).addClass('border-success')
        $(div).append('<p>Requests: ' + (data.failed.length + data.complete.length) + '</p>')
        $(div).append('<p>Count of valid rows: ' + data.complete.length + '</p>')
        $(div).append('<p>Count of invalid rows: ' + data.failed.length + '</p><ul>')
        for(let row of data.failed) {
            $(div).append('<li style="margin-left: 10px;">'
                + row['Product Code'] + ', '
                + row['Product Name'] + ', '
                + row['Product Description'] + ', '
                + row['Stock'] + ', '
                + row['Cost in GBP'] + ', '
                + row['Discontinued']
                +'</li>')
        }
        $(div).append('</ul>')
    }
    $(div).append()

    return div
}

function getProcessedInfo(ids) {
    let info = $('<div></div>')
    $(info).append('<p>Files have been uploaded and will be processed!</p><p>')
    for(let id of ids) {
        $(info).append('id' + id + '; ')
    }
    $(info).append('</p>')
    return info
}

$(() => {
    setEventSource('#import-upload-url', response => {
        let result = JSON.parse(response.data)
        $(block).append(getImportResultToHtml(result))
    })
})

$('#import-form').on('submit', () => {
    block.html('')

    let data = new FormData()
    let token = $('#import_by_csv__token').val()

    data.append('token', token)

    for(let file of $('#import_by_csv_file')[0].files) {
        data.append('files[]', file)
    }

    for(let setting of $('.csv-sets > fieldset > div')) {
        let params = $(setting).find('input')
        let set = $(params[0]).val() + $(params[1]).val() + $(params[2]).val() + +$(params[3]).is(':checked')
        data.append('settings[]', set)
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
                $(block).prepend(getProcessedInfo(ids))
            }
        },
        error: () => {
            console.log('Error!!!')
        }
    })

    return false
})