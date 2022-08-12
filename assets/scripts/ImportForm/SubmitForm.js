import {Sender} from "./Sender";

$('#import-form').on('submit', () => {
    let form = new Sender('#import-form');
    form.send();

    return false;
})