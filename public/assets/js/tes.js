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

const tribForm = document.getElementById('trib-form');
if(tribForm) {
    tribForm.addEventListener("submit", function(event) {
        //event.preventDefault();
        waitingResults(); 
    }); 
}

function tesGetTheText(element, trim = false) {
    if(!element) return '';
    text = element.innerText || element.textContent;
    if(!text) return '';
    if (trim) {
        return text.trim();
    }
    return text;
}

const btns = document.querySelectorAll('.btn-copy-text');
btns.forEach(btn => {
    btn.addEventListener('click', function(event) {
        let targetElement = event.target;
        let td = targetElement.closest('td');
        let textToCopyElement = td.querySelector('.tes-text-to-be-copied');
        const toTrim = textToCopyElement.dataset.spec === 'trim';
        let textToCopy = tesGetTheText(textToCopyElement, toTrim);
        // console.log(textToCopy);
        navigator.clipboard.writeText(textToCopy).then(
            function() {
                /* clipboard successfully set */
                // window.alert('Success! The text was copied to your clipboard')
                td.querySelector('.btn-copy-text').innerHTML = 'copiado';
            },
            function() {
                /* clipboard write failed */
                window.alert('Ops! Seu navegador não suporta a API Clipboard. Tente atualizá-lo.')
            }
        );
    });
});


//nav links style
const navLinks = document.querySelectorAll('.nav-link');
function navLinkActiveStyle(el) {
    el.style.color = '#5c80d1';
    el.style.borderBottomColor = 'rgb(92 128 209 / 48%)'; 
    navLinks.forEach(function(eachEl) {
        if (eachEl !== el) {
            eachEl.removeAttribute('style');
        }
    });   
}

//Initial state and adding click event
navLinks.forEach(function(el) {
    el.addEventListener('click', linkColorAndBorder);
    if (el.classList.contains('active')) {
        navLinkActiveStyle(el);
    } 
    
});

function linkColorAndBorder() {
    let clicked = this;
    navLinkActiveStyle(clicked);
}
