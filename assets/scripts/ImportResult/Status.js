export let Status = function(resultContainer, requestId) {
    if(requestId === undefined) {
        return;
    }

    let that = {};
    that.id = requestId;
    that.htmlBlock = $('.request-' + that.id);

    that.getHead = function(data) {
        $(that.htmlBlock).append('<p>ID' + data.id + '</p>');
        $(that.htmlBlock).append('<p>Status: ' + data.status + '</p>');
    }

    if(that.htmlBlock.length === 0) {
        that.htmlBlock = $('<div class="request-' + that.id + ' border border-1 p-2 my-1 rounded"></div>');
        that.getHead({id: that.id, status: 'IMPORT_NEW'});
        $(resultContainer).prepend(that.htmlBlock);
    } else {
        that.htmlBlock.html('');
    }

    this.allStatuses[that.id] = that;
    return that;
}

Status.prototype.allStatuses = {};