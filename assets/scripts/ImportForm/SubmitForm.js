import {Status} from "../ImportResult/Status";

$('#import-form').on('submit', () => {
    let data = new FormData();
    let token = $('#import_by_csv__token').val();
    let block = $('.import-result');

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
            for(let i = 0, length = ids.length; i < length; i++) {
                new Status(block, ids[i]);
            }
        },
        error: () => {
            console.log('Error!!!');
        }
    });

    return false;
})