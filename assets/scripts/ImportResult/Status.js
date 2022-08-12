export let Status = function(resultContainer, requestId) {
    if(requestId === undefined) {
        return;
    }

    this.id = requestId;
    this.allStatuses[this.id] = this;
    this.htmlBlock = $('.request-' + this.id);

    if(this.htmlBlock.length === 0) {
        this.htmlBlock = $('<div class="request-' + this.id + ' border border-1 p-2 my-1 rounded"></div>');
        this.getHead({id: this.id, status: 'IMPORT_NEW'});
        $(resultContainer).prepend(this.htmlBlock);
    }
}

Status.prototype.getHead = function(data) {
    $(this.htmlBlock).html('');
    $(this.htmlBlock).append('<p>ID' + data.id + '</p>');
    $(this.htmlBlock).append('<p>Status: ' + data.status + '</p>');
}
Status.prototype.allStatuses = {};