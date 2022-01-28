jQuery(function() {
    $('.import_files').on('change', (e, info) => {
        let files = e.target.files
        let prototype = $('#import_by_csv_csvSettings').data('prototype')

        for(let i = 0; i < files.length; i++) {
            let block = $('<fieldset></fieldset>');
            $(block).html('<legend>' + files[i].name + '</legend>')

            let form = prototype.replace(/__name__/g, i)
            $(block).append(form)
            $('.csv-sets').append(block)
            // console.log(block)
        }
    })
})