jQuery(function() {
    $('.import_files').on('change', (e, info) => {
        $('.csv-sets').html('')
        let files = e.target.files
        let prototype = $('#prototype').data('prototype')

        for(let i = 0; i < files.length; i++) {
            let block = $('<fieldset></fieldset>');
            $(block).html('<legend>' + files[i].name + '</legend>')

            let form = prototype.replace(/__name__/g, i)
            $(block).append(form)
            $('.csv-sets').append(block)
        }
    })
})