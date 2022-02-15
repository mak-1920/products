import {Data} from "./Data";
import {Status} from "../ImportResult/Status";

export let Sender = function(formBlockSelector) {
    let that = new Data(formBlockSelector);
    let block = $('.import-result');

    let resetForm = function() {
        that.form.wrap('<form>').closest('form').get(0).reset();
        that.form.unwrap();
    }

    that.send = function() {
        $.ajax({
            url: Routing.generate('ajax_import_upload'),
            method: 'post',
            contentType: false,
            processData: false,
            data: that.data,
            success: (res) => {
                resetForm();
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
    }

    return that;
}