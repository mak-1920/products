import {Result} from "./Result";

export let PrintResult = function(data) {
    let div = $('.request-' + data.id);
    $(div).html('');

    let status = new Result(data);

    if (data.status === 'STATUS_FAILED') {
        status.printFailedInfo();
    } else if (data.status === 'STATUS_IMPORTED') {
        status.printSuccessInfo();
    }
}