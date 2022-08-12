import {Data} from "./Data";
import {Status} from "../ImportResult/Status";

export let Sender = function(formBlockSelector) {
    this.__proto__ = new Data(formBlockSelector);
    let block = $('.import-result');

    this.resetForm = function() {
        this.form.wrap('<form>').closest('form').get(0).reset();
        this.form.unwrap();
    }

    this.send = function() {
        $.ajax({
            url: Routing.generate('ajax_import_upload'),
            method: 'post',
            contentType: false,
            processData: false,
            data: this.data,
            success: (res) => {
                this.resetForm();
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
}