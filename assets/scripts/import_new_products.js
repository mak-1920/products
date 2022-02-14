$(() => {
    function addRow(tr, string) {
        $(tr).append('<td>' + string + '</td>');
    }

    function addTime(tr, date) {
        if(date !== undefined) {
            date = new Date(date.date);
            addRow(tr, Intl.DateTimeFormat('en-GB', {dateStyle: 'short', timeStyle: 'short', hour12: true}).format(date));
        } else {
            addRow(tr, '-');
        }
    }

    function addRows(rows) {
        let rowFields = ['Product Code', 'Product Name', 'Product Description', 'Stock', 'Cost in GBP'];

        for(let rowKey in rows) {
            let tr = $('<tr></tr>');
            for(let i = 0; i < rowFields.length; i++) {
                addRow(tr, rows[rowKey][rowFields[i]]);
            }
            addRow(tr, '?')
            addTime(tr, rows[rowKey]['Discontinued']);

            $('.new-products table').append(tr)
        }
    }

    setEventSource('#import-uploaded-url', response => {
        let result = JSON.parse(response.data)
        addRows(result.complete)
    })
})