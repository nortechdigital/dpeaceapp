// worker.js
let intervalId;

self.onmessage = function(e) {
    if (e.data.command === 'start') {
        // Clear any existing interval
        if (intervalId) {
            clearInterval(intervalId);
        }
        
        // Run immediately and then every minute (60000 ms)
        fetchHistory();
        intervalId = setInterval(fetchHistory, 5000);
    }
    else if (e.data.command === 'stop') {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }
};

function fetchHistory() {
    fetch('./payapi/history.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            self.postMessage({
                result: 'success', 
                data: data,
                timestamp: new Date().toISOString()
            });
        })
        .catch(error => {
            self.postMessage({
                result: 'error', 
                error: error.message,
                timestamp: new Date().toISOString()
            });
        });
}