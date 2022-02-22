import {Status} from "./Status";

export let Result = function(resultContainer, data) {
    let status = new Status().allStatuses[data.id];
    if(status === undefined) {
        status = new Status(resultContainer, data.id);
    }
    this.__proto__ = status;
    let block = this.htmlBlock;

    this.getHead(data);

    this.printSuccessInfo = function() {
        let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP', 'Discontinued'];

        $(block).addClass('border-success');
        $(block).append('<p>Requests: ' + (data.failed.length + data.complete.length) + '</p>');
        $(block).append('<p>Count of valid rows: ' + data.complete.length + '</p>');
        $(block).append('<p>Count of invalid rows: ' + data.failed.length + '</p><ul>');

        let rows = data.failed;
        for(let i = 0, rowsLength = rows.length; i < rowsLength; i++) {
            let li = $('<li style="margin-left: 10px"></li>');
            for(let j = 0, fieldsLength = rowFields.length; j < fieldsLength; j++) {
                $(li).append(rows[i][rowFields[j]] + ', ');
            }
            $(block).append(li);
        }

        $(block).append('</ul>');
    }

    this.printFailedInfo = function() {
        let params = ['Delimiter', 'Enclosure', 'Escape', 'Have header'];
        $(block).addClass('border-danger');
        $(block).append('<p>Settings:</p>');

        for(let i = 0, length = params.length; i < length; i++) {
            $(block).append('<p>' + params[i] + ': ' + data.settings[i] + '</p>');
        }
    }
}