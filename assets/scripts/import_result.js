$(function(){
    let url = $('#mercure-import-send-url').text();

    $('.import-result-ids li').each((i, e) => {
        let u = url.replace('___id___', $(e).html().substr(2))
        console.log(u, e)

        let eventSource = new EventSource(u)
        eventSource.onmessage = data => {
            let div = $('<div></div>')
            $(div).append('<p>ID' + data.id + '</p>')
            $(div).append('<p>Status' + data.status + '</p>')

            if(data.status === 'STATUS_FAILED') {
                $(div).append('<p>Settings:</p>')
                $(div).append('<p>Delimiter: ' + data.settings[0] + '</p>')
                $(div).append('<p>Enclosure: ' + data.settings[1] + '</p>')
                $(div).append('<p>Escape: ' + data.settings[2] + '</p>')
                $(div).append('<p>Have header: ' + data.settings[3] + '</p>')

            } else if (data.status === 'STATUS_IMPORTED') {
                $(div).append('<p>Requests: ' + (data.failed.length + data.complete.length) + '</p>')
                $(div).append('<p>Count of valid rows: ' + data.complete.length + '</p>')
                $(div).append('<p>Count of invalid rows: ' + data.failed.length + '</p><ul>')
                data.failed.each(e => {
                    $(div).append('<li>Requests: ' + (data.failed.length + data.complete.length) + '</li>')
                })
                $(div).append('</ul>')
            }
            $(div).append()

            $('.import-send-result').append(div)
        }
    })
})