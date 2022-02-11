function addRow(tr, string) {
    $(tr).append('<td>' + string + '</td>')
}

function addRows(rows) {
    for(let rowKey in rows) {
        let tr = $('<tr></tr>');
        addRow(tr, rows[rowKey]['Product Code'])
        addRow(tr, rows[rowKey]['Product Name'])
        addRow(tr, rows[rowKey]['Product Description'])
        addRow(tr, rows[rowKey]['Stock'])
        addRow(tr, rows[rowKey]['Cost in GBP'])
        addRow(tr, '?')
        if (rows[rowKey]['Discontinued']) {
            let date = new Date(rows[rowKey]['Discontinued'].date)
            // addRow(tr, date.toLocaleDateString('en-US'));
            addRow(tr, Intl.DateTimeFormat('en-GB', {dateStyle: 'short', timeStyle: 'short', hour12: true}).format(date))
        } else {
            addRow(tr, '-');
        }
        $('.new-products table').append(tr)
    }
}

$(() => {
    setEventSource('#import-uploaded-url', response => {
        let result = JSON.parse(response.data)
        addRows(result.complete)
    })
})