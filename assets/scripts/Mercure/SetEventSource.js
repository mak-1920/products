export let SetEventSource = function(blockWithURL, callback) {
    if($(blockWithURL).length === 0) {
        return null;
    }

    const url = JSON.parse($(blockWithURL).html());
    const eventSource = new EventSource(url);
    eventSource.onmessage = callback;
    return eventSource;
}