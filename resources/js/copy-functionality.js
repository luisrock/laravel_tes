export function initCopyButtons() {
    document.addEventListener('click', function (event) {
        const buttonElement = event.target.closest('.btn-copy-text');
        if (!buttonElement) return;

        let textToCopy = buttonElement.getAttribute('data-clipboard-text');

        // Fallback for search results (complex content in hidden span)
        if (!textToCopy) {
            const container = buttonElement.closest('td') || buttonElement.closest('.copy-container') || buttonElement.closest('div.tw-bg-white'); // Added div fallback for card layout
            if (container) {
                const textElement = container.querySelector('.tes-text-to-be-copied');
                if (textElement) {
                    textToCopy = textElement.textContent;
                    if (textElement.dataset.spec === 'trim') {
                        textToCopy = textToCopy.trim();
                    } else {
                        // Clean up extra whitespace from template indentation
                        textToCopy = textToCopy.replace(/\s+/g, ' ').trim();
                    }
                }
            }
        }

        if (!textToCopy) return;

        navigator.clipboard.writeText(textToCopy).then(() => {
            const originalHtml = buttonElement.innerHTML;

            // Visual Feedback
            // Check if we already have the check icon to avoid double feedback
            if (buttonElement.querySelector('.fa-check')) return;

            const checkIcon = document.createElement('i');
            checkIcon.className = 'fa fa-check tw-mr-1.5';
            buttonElement.innerHTML = '';
            buttonElement.appendChild(checkIcon);
            buttonElement.appendChild(document.createTextNode(' Copiado'));

            // Add success styling (green)
            buttonElement.classList.add('tw-text-green-700', 'tw-border-green-600', 'tw-bg-green-50');

            // Revert after 5 seconds
            setTimeout(() => {
                buttonElement.innerHTML = originalHtml;
                buttonElement.classList.remove('tw-text-green-700', 'tw-border-green-600', 'tw-bg-green-50');
            }, 5000);

        }).catch(err => {
            console.error('Failed to copy: ', err);
            // alert('Erro ao copiar para a área de transferência.');
        });
    });
}
