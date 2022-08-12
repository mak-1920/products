import {TBPrint} from "../TBPrint";

export let ProductsPrint = function(rows) {
    this.__proto__ = new TBPrint();

    let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP'];

    this.addTime = function(tr, date) {
        if(date !== '') {
            date = new Date(date.date);
            this.setField(tr, Intl.DateTimeFormat('en-GB', {dateStyle: 'short', timeStyle: 'short', hour12: true}).format(date));
        } else {
            this.setField(tr, '-');
        }
    }

    this.generateRow = function(tr, row) {
        for(let i = 0, length = rowFields.length; i < length; i++) {
            this.setField(tr, row[rowFields[i]]);
        }
        this.addTime(tr, row['Discontinued']);
    }

    for(let rowKey in rows) {
        if(rows.hasOwnProperty(rowKey)) {
            let tr = this.getRow(rows[rowKey]);
            $('.new-products table').append(tr);
        }
    }
}