/**
 * Script de filtragem client-side para a tabela de teses
 * Permite buscar por: Tema, Número, Texto, Tese, Relator e Julgado
 * Inclui highlight dos termos encontrados
 */
(function () {
    'use strict';

    const searchInput = document.getElementById('table-search-input');
    const searchContainer = document.getElementById('search-container');
    const toggleBtn = document.getElementById('toggle-search-btn');
    const clearBtn = document.getElementById('clear-search-btn');
    const resultsCountElement = document.querySelector('.trib-texto-quantidade code:last-child');
    const tableBody = document.querySelector('.table-results tbody');

    if (!searchInput || !tableBody) return;

    // Guardar o total original de resultados
    const originalTotal = parseInt(resultsCountElement.innerText.replace(/\D/g, '')) || 0;
    const rows = Array.from(tableBody.querySelectorAll('tr'));

    // Cache do conteúdo original de cada linha para restaurar após highlight
    const originalContent = new Map();
    rows.forEach((row, index) => {
        originalContent.set(index, row.innerHTML);
    });

    // Toggle da visibilidade da busca
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (searchContainer.classList.contains('d-none')) {
                // Mostrar: Reaplicar filtro existente
                searchContainer.classList.remove('d-none');
                filterTable(searchInput.value.toLowerCase());
                searchInput.focus();
            } else {
                // Ocultar: Limpar visualização (mas manter valor no input)
                searchContainer.classList.add('d-none');
                filterTable('');
            }
        });
    }

    // Limpar busca
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            filterTable('');
            searchInput.focus();
        });
    }

    // Evento de digitação com debounce leve
    let timeout = null;
    searchInput.addEventListener('keyup', function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            filterTable(searchInput.value.toLowerCase());
        }, 100);
    });

    // Função principal de filtragem e highlight
    function filterTable(term) {
        let visibleCount = 0;

        rows.forEach((row, index) => {
            // Restaurar conteúdo original antes de processar
            row.innerHTML = originalContent.get(index);

            // Se o termo for vazio, mostrar tudo e limpar highlights
            if (term === '') {
                row.style.display = '';
                visibleCount++;
                return;
            }

            const textContent = row.textContent.toLowerCase();

            // Verifica se o termo está presente
            if (textContent.includes(term)) {
                row.style.display = '';
                visibleCount++;

                // Aplicar highlight
                highlightTerm(row, term);
            } else {
                row.style.display = 'none';
            }
        });

        // Atualizar contador
        updateCounter(visibleCount);

        // Mostrar/ocultar botão de limpar
        if (clearBtn) {
            clearBtn.style.display = term === '' ? 'none' : 'block';
        }
    }

    function highlightTerm(element, term) {
        if (!term) return;

        // Função recursiva para percorrer nós de texto
        function traverseAndHighlight(node) {
            if (node.nodeType === 3) { // Node.TEXT_NODE
                const text = node.nodeValue;
                const lowerText = text.toLowerCase();
                const index = lowerText.indexOf(term);

                if (index >= 0) {
                    const span = document.createElement('span');
                    // Preservar o texto original (case sensitive) mas envolver o match
                    const before = text.substring(0, index);
                    const match = text.substring(index, index + term.length);
                    const after = text.substring(index + term.length);

                    const highlightSpan = document.createElement('span');
                    highlightSpan.className = 'search-highlight';
                    highlightSpan.textContent = match;

                    const afterNode = document.createTextNode(after);

                    node.nodeValue = before;
                    node.parentNode.insertBefore(highlightSpan, node.nextSibling);
                    node.parentNode.insertBefore(afterNode, highlightSpan.nextSibling);

                    // Continuar buscando no restante do texto (afterNode)
                    traverseAndHighlight(afterNode);
                }
            } else if (node.nodeType === 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                // Node.ELEMENT_NODE - recursão, ignorando scripts/styles
                // Precisamos converter para array porque a lista de childNodes muda dinamicamente
                Array.from(node.childNodes).forEach(child => traverseAndHighlight(child));
            }
        }

        traverseAndHighlight(element);
    }

    function updateCounter(count) {
        if (resultsCountElement) {
            if (count === originalTotal) {
                resultsCountElement.innerText = originalTotal;
            } else {
                resultsCountElement.innerText = `${count} de ${originalTotal}`;
            }
        }
    }

})();
