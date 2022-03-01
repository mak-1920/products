import {SetEventSource} from "./SetEventSource";
import {ProductsPrint} from "../TablePrint/NewProducts/ProductsPrint";

SetEventSource('#import-uploaded-url', response => {
    let result = JSON.parse(response.data)
    new ProductsPrint(result.complete)
});