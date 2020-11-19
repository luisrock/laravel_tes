const resultsBlock = document.getElementById('content-results');
const spinner = document.getElementById('spinning');
const ldText = document.getElementById('loading-text');
const btnForm = document.getElementById('btn-send-trib-form');

function waitingResults() {
    spinner.style.display = "block";
    ldText.style.display = "inline";
    resultsBlock.innerHTML = '';
    btnForm.disabled = true;
    btnForm.style.display = 'none';
}

document.getElementById('trib-form').addEventListener("submit", function(event) {
//     event.preventDefault();
    waitingResults(); 
});