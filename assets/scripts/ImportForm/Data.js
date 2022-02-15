export let Data = function(formBlockSelector){
    let that = {};

    that.form = $(formBlockSelector);
    that.data = new FormData();

    let token = $('#import_by_csv__token').val();

    that.data.append('token', token);

    for(let file of $('#import_by_csv_file')[0].files) {
        that.data.append('files[]', file);
    }

    for(let setting of $('.csv-sets > fieldset > div')) {
        let params = $(setting).find('input');
        let set = $(params[0]).val() + $(params[1]).val() + $(params[2]).val() + +$(params[3]).is(':checked');
        that.data.append('settings[]', set);
    }

    return that;
}