$('#currency-converter-form input, #currency-converter-form select').on('change', () => {
    let from = $('#currency-converter-from').val();
    let to = $('#currency-converter-to').val();
    let val = $('#currency-converter-value').val();

    let currencies = jQuery.parseJSON($('#currency-converter-currencies').html());
    let result = val / currencies[from] * currencies[to];
    $('#currency-converter-result').val(result);
})