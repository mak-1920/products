import {TBPrint} from "../TBPrint";

export let RequestsPrint = function(request) {
    this.__proto__ = new TBPrint();

    this.generateRow = function(tr, row) {
        this.setField(tr, row.id);
        this.setField(tr, row.status);
        this.setField(tr, row.file);
        this.setField(tr, row.settings);
        this.setField(tr, row.complete.length);
        this.setField(tr, row.failed.length);
    }

    let tr = this.getRow(request);
    $('.new-requests table').append(tr);
}