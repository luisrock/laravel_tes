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


//Open tab content that has something initially
//Disable .nav-item > .nav-link with no corresponding content on .tab-pane for pesq uisas prontas
const navItems = document.querySelectorAll('.nav-item');

firstClicked = false;
navItems.forEach(function(el) {
    let hasContent = document.querySelectorAll('tr').length > 0;
    if(!hasContent) {
        //no pane has content. Leave as it is
        return; 
    }
    let navLink = el.querySelector('.nav-link');
    let href = navLink.getAttribute('href');
    let tabPane = document.querySelector(href);
    let trs = tabPane.querySelectorAll('tr');
    if (trs.length === 0) {
        if (navLink.closest('.nav-tabs-tribunais')) {
            //disable for tema (pesquisa pronta)
            navLink.classList.add('disabled'); 
        }
        navLink.classList.remove('active');
        tabPane.classList.remove('show', 'active');
    } else {
        if (!firstClicked) {
            navLink.classList.add('active');
            tabPane.classList.add('show', 'active');
            firstClicked = true;
        } else {
            navLink.classList.remove('active');
            tabPane.classList.remove('show', 'active');
        }
    }
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

