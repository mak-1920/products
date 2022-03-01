export let TBPrint = function() {
    this.setField = function(tr, string) {
        $(tr).append('<td>' + string + '</td>');
    }

    this.getRow = function(row) {
        let tr = $('<tr></tr>');
        this.generateRow(tr, row);
        return tr;
    }

    this.generateRow = function(tr, row) {
        console.log('you must override this function');
    }
}