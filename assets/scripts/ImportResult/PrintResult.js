import {Result} from "./Result";

export let PrintResult = function(data) {
    let div = $('.request-' + data.id);
    let block = $('.import-result');
    $(div).html('');

    let status = new Result(block, data);

    if (data.status === 'STATUS_FAILED') {
        status.printFailedInfo();
    } else if (data.status === 'STATUS_IMPORTED') {
        status.printSuccessInfo();
    }
}