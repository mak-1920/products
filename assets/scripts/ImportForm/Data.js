export let Data = function(formBlockSelector){
    this.form = $(formBlockSelector);
    this.data = new FormData();

    let token = $('#import_by_csv__token').val();

    this.data.append('token', token);

    for(let file of $('#import_by_csv_file')[0].files) {
        this.data.append('files[]', file);
    }

    for(let setting of $('.csv-sets > fieldset > div')) {
        let params = $(setting).find('input');
        let set = $(params[0]).val() + $(params[1]).val() + $(params[2]).val() + +$(params[3]).is(':checked');
        this.data.append('settings[]', set);
    }
}