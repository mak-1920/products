import {SetEventSource} from "./SetEventSource";
import {Print} from "../NewProducts/Print";

SetEventSource('#import-uploaded-url', response => {
    let result = JSON.parse(response.data)
    Print(result.complete)
});