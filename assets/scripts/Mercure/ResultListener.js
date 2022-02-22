import {SetEventSource} from "./SetEventSource";
import {PrintResult} from "../ImportResult/PrintResult";

SetEventSource('#import-upload-url', response => {
    let result = JSON.parse(response.data);
    PrintResult(result);
})

