export let Print = function(rows) {
    let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP'];

    function setField(tr, string) {
        $(tr).append('<td>' + string + '</td>');
    }

    function addTime(tr, date) {
        if(date !== '') {
            date = new Date(date.date);
            setField(tr, Intl.DateTimeFormat('en-GB', {dateStyle: 'short', timeStyle: 'short', hour12: true}).format(date));
        } else {
            setField(tr, '-');
        }
    }

    function getRow(row) {
        let tr = $('<tr></tr>');

        for(let i = 0, length = rowFields.length; i < length; i++) {
            setField(tr, row[rowFields[i]]);
        }
        setField(tr, '?');
        addTime(tr, row['Discontinued']);

        return tr;
    }

    for(let rowKey in rows) {
        if(rows.hasOwnProperty(rowKey)) {
            let tr = getRow(rows[rowKey]);
            $('.new-products table').append(tr);
        }
    }
}