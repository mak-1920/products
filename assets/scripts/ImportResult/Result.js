import {Status} from "./Status";

export let Result = function(data) {
    let status = new Status().allStatuses[data.id];
    let block = status.htmlBlock;
    let that = {};

    status.getHead.call(this, data);

    that.printSuccessInfo = function() {
        let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

        $(block).addClass('border-success');
        $(block).append('<p>Requests: ' + (data.failed.length + data.complete.length) + '</p>');
        $(block).append('<p>Count of valid rows: ' + data.complete.length + '</p>');
        $(block).append('<p>Count of invalid rows: ' + data.failed.length + '</p><ul>');

        for(let row of data.failed) {
            let li = $('<li style="margin-left: 10px"></li>');
            for(let i = 0; i < rowFields.length; i++) {
                $(li).append(row[rowFields[i]] + ', ');
            }
            $(block).append(li);
        }

        $(block).append('</ul>');
    }

    that.printFailedInfo = function() {
        let params = ['Delimiter', 'Enclosure', 'Escape', 'Have header'];
        $(block).addClass('border-danger');
        $(block).append('<p>Settings:</p>');

        for(let i = 0; i < params.length; i++) {
            $(block).append('<p>' + params[i] + ': ' + data.settings[i] + '</p>');
        }
    }

    return that;
}