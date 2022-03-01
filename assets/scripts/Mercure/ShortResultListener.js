import {SetEventSource} from "./SetEventSource";
import {RequestsPrint} from "../TablePrint/NewRequests/RequestsPrint";

SetEventSource('#import-upload-short-info-url', response => {
    let result = JSON.parse(response.data);
    new RequestsPrint(result);
})

