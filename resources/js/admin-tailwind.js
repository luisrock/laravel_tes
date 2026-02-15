/**
 * Admin Dashboard JavaScript - Tailwind Version
 * Lógica para gerenciamento da área administrativa com Tailwind CSS
 */

(function ($) {
    'use strict';

    // ==================== VARIÁVEIS GLOBAIS ====================
    let currentPage = 1;
    let perPage = 60;
    let filterStatus = 'not_created';
    let orderBy = 'results';
    let orderDirection = 'desc';
    let searchTerm = '';

    // URLs das rotas (serão definidas via data attributes no HTML)
    let storeUrl = window.adminRoutes ? window.adminRoutes.store : '/admin-ajax-request';
    let deleteUrl = window.adminRoutes ? window.adminRoutes.delete : '/admin-ajax-request-del';
    let getTemasUrl = window.adminRoutes ? window.adminRoutes.getTemas : '/admin/get-temas';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ==================== FUNÇÕES UTILITÁRIAS ====================

    function titleCase(str, limit = 3) {
        var splitStr = str.toLowerCase().split(' ');
        for (var i = 0; i < splitStr.length; i++) {
            if (splitStr[i].length < limit) {
                continue;
            }
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);
        }
        return splitStr.join(' ');
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    function cleanKeyword(keyword) {
        return keyword.replace(/^["']|["']$/g, '').trim();
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ==================== INICIALIZAÇÃO ====================

    $(document).ready(function () {
        console.log('Admin Dashboard Tailwind JS carregado!');

        // Event Listeners
        initEventListeners();

        // Carregar temas inicialmente
        loadTemas();
    });

    // ==================== CARREGAMENTO DE DADOS ====================

    function loadTemas() {
        showLoading();

        const params = {
            page: currentPage,
            per_page: perPage,
            filter_status: filterStatus,
            order_by: orderBy,
            order_direction: orderDirection,
            search: searchTerm
        };

        $.ajax({
            url: getTemasUrl,
            type: 'GET',
            data: params,
            success: function (response) {
                if (response.success) {
                    renderTemas(response.data);
                    renderPagination(response.pagination);
                    updateStats(response.pagination);
                }
            },
            error: function (xhr, status, error) {
                console.error('Erro ao carregar temas:', error);
                $('#temas-container').html('<div class="tw-p-4 tw-bg-red-50 tw-text-red-700 tw-rounded-lg">Erro ao carregar temas. Por favor, recarregue a página.</div>');
            }
        });
    }

    function renderTemas(temas) {
        if (temas.length === 0) {
            $('#temas-container').html('<div class="tw-p-4 tw-bg-blue-50 tw-text-blue-700 tw-rounded-lg">Nenhum tema encontrado com os filtros selecionados.</div>');
            $('#pagination-container').hide();
            return;
        }

        // Wrapper da tabela
        let html = '<div class="tw-overflow-x-auto tw-rounded-lg tw-shadow tw-border tw-border-gray-200"><table class="tw-min-w-full tw-divide-y tw-divide-gray-200"><thead class="tw-bg-gray-50"><tr>';

        // Cabeçalho
        const thClass = 'tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase tw-tracking-wider';

        html += `<th class="${thClass} tw-w-10">
                    <input type="checkbox" id="select-all-header" class="tw-rounded tw-border-gray-300 tw-text-brand-600 focus:tw-ring-brand-500">
                </th>`;
        html += `<th class="${thClass} tw-w-16">ID</th>`;
        html += `<th class="${thClass}">Keyword</th>`;
        html += `<th class="${thClass}">Label</th>`;
        html += `<th class="${thClass} tw-w-24 tw-text-center">Resultados</th>`;
        html += `<th class="${thClass} tw-w-32">Criado em</th>`;
        html += `<th class="${thClass} tw-w-48">Ações</th>`;
        html += '</tr></thead><tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">';

        temas.forEach((t, k) => {
            const isCreated = (t.created_at && t.slug);
            const rowClass = isCreated ? 'hide-created tw-bg-green-50' : 'tw-hover:bg-gray-50';

            let labelValue = t.label;
            if (!labelValue) {
                const cleanedKeyword = cleanKeyword(t.keyword);
                labelValue = titleCase(cleanedKeyword);
            }

            const keyword_display = isCreated ?
                `<a href="/tema/${t.slug}" target="_blank" class="tw-text-brand-600 hover:tw-underline">${escapeHtml(t.keyword)} <i class="fas fa-external-link-alt tw-text-xs tw-ml-1"></i></a>` :
                escapeHtml(t.keyword);

            const tdClass = 'tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-900';

            html += `
                <tr id="td-tema-${t.id}" class="td-tema ${rowClass}" data-tema-id="${t.id}">
                    <td class="${tdClass}">
                        <input type="checkbox" class="tema-checkbox tw-rounded tw-border-gray-300 tw-text-brand-600 focus:tw-ring-brand-500" data-tema-id="${t.id}">
                    </td>
                    <td class="${tdClass} tw-text-gray-500">${t.id}</td>
                    <td class="${tdClass} tw-max-w-xs tw-truncate" title="${escapeHtml(t.keyword)}">${keyword_display}</td>
                    <td class="${tdClass} tw-max-w-xs tw-truncate" title="${escapeHtml(labelValue)}">${escapeHtml(labelValue)}</td>
                    <td class="${tdClass} tw-text-center">
                        <span class="tw-px-2 tw-inline-flex tw-text-xs tw-leading-5 tw-font-semibold tw-rounded-full tw-bg-gray-100 tw-text-gray-800">
                            ${t.results}
                        </span>
                    </td>
                    <td class="${tdClass} tw-text-gray-500 tw-text-xs">${t.created_at || '-'}</td>
                    <td class="${tdClass}">
                        <div id="div-create-${k}" ${isCreated ? 'style="display:none"' : ''}>
                            <button class="btn-create tw-inline-flex tw-items-center tw-px-3 tw-py-1.5 tw-border tw-border-transparent tw-text-xs tw-font-medium tw-rounded-md tw-text-white tw-bg-emerald-600 hover:tw-bg-emerald-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-offset-2 focus:tw-ring-emerald-500" 
                                data-id="${t.id}" data-keyword="${escapeHtml(t.keyword)}" data-index="${k}">
                                <i class="fa fa-plus-circle tw-mr-1.5"></i> Criar
                            </button>
                        </div>
                        <div id="div-create-label-${k}" style="display:none" class="tw-flex tw-flex-col tw-gap-2">
                            <input type="text" id="create-input-${t.id}" class="tw-block tw-w-full tw-rounded-md tw-border-gray-300 tw-shadow-sm focus:tw-border-brand-500 focus:tw-ring-brand-500 tw-sm:text-sm" placeholder="Label..." value="${escapeHtml(labelValue)}">
                            <div class="tw-flex tw-gap-2">
                                <button class="btn-submit-create tw-inline-flex tw-items-center tw-px-2.5 tw-py-1.5 tw-border tw-border-transparent tw-text-xs tw-font-medium tw-rounded tw-text-white tw-bg-brand-600 hover:tw-bg-brand-700" data-id="${t.id}" data-index="${k}">
                                    <i class="fa fa-check"></i>
                                </button>
                                <button class="btn-cancel-create tw-inline-flex tw-items-center tw-px-2.5 tw-py-1.5 tw-border tw-border-gray-300 tw-text-xs tw-font-medium tw-rounded tw-text-gray-700 tw-bg-white hover:tw-bg-gray-50" data-index="${k}">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';

        $('#temas-container').html(html);
        updateSelectionCount();
    }

    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            $('#pagination-container').hide();
            return;
        }

        $('#pagination-container').show();

        const info = `Exibindo <span class="tw-font-medium">${pagination.from}</span> a <span class="tw-font-medium">${pagination.to}</span> de <span class="tw-font-medium">${pagination.total}</span> temas`;
        $('#pagination-info').html(info);

        let html = '<nav class="tw-relative tw-z-0 tw-inline-flex tw-rounded-md tw-shadow-sm -tw-space-x-px" aria-label="Pagination">';
        const current = pagination.current_page;
        const last = pagination.last_page;

        // Helper para botões
        const btnClass = "tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-500 hover:tw-bg-gray-50";
        const btnClassDisabled = "tw-relative tw-inline-flex tw-items-center tw-px-2 tw-py-2 tw-border tw-border-gray-300 tw-bg-gray-100 tw-text-gray-400 tw-cursor-not-allowed";

        // Primeiro
        html += `<button ${current === 1 ? 'disabled class="' + btnClassDisabled + ' tw-rounded-l-md"' : 'class="' + btnClass + ' tw-rounded-l-md" onclick="changePage(1)"'}>
            <span class="tw-sr-only">Primeira</span> <i class="fas fa-angle-double-left"></i>
        </button>`;

        // Anterior
        html += `<button ${current === 1 ? 'disabled class="' + btnClassDisabled + '"' : 'class="' + btnClass + '" onclick="changePage(' + (current - 1) + ')"'}>
            <span class="tw-sr-only">Anterior</span> <i class="fas fa-angle-left"></i>
        </button>`;

        // Páginas
        let pagesToShow = [];
        if (last <= 10) {
            for (let i = 1; i <= last; i++) pagesToShow.push(i);
        } else {
            pagesToShow.push(1);
            if (current <= 4) {
                for (let i = 2; i <= 5; i++) pagesToShow.push(i);
                pagesToShow.push('...');
                pagesToShow.push(last);
            } else if (current >= last - 3) {
                pagesToShow.push('...');
                for (let i = last - 4; i <= last; i++) pagesToShow.push(i);
            } else {
                pagesToShow.push('...');
                pagesToShow.push(current - 1);
                pagesToShow.push(current);
                pagesToShow.push(current + 1);
                pagesToShow.push('...');
                pagesToShow.push(last);
            }
        }

        pagesToShow.forEach(page => {
            if (page === '...') {
                html += '<span class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-bg-white tw-text-sm tw-font-medium tw-text-gray-700">...</span>';
            } else {
                const activeClass = page === current ? 'tw-z-10 tw-bg-brand-50 tw-border-brand-500 tw-text-brand-600' : 'tw-bg-white tw-border-gray-300 tw-text-gray-500 hover:tw-bg-gray-50';
                html += `<button class="tw-relative tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-border tw-text-sm tw-font-medium ${activeClass}" onclick="changePage(${page})">${page}</button>`;
            }
        });

        // Próximo
        html += `<button ${current === last ? 'disabled class="' + btnClassDisabled + '"' : 'class="' + btnClass + '" onclick="changePage(' + (current + 1) + ')"'}>
            <span class="tw-sr-only">Próxima</span> <i class="fas fa-angle-right"></i>
        </button>`;

        // Último
        html += `<button ${current === last ? 'disabled class="' + btnClassDisabled + ' tw-rounded-r-md"' : 'class="' + btnClass + ' tw-rounded-r-md" onclick="changePage(' + last + ')"'}>
            <span class="tw-sr-only">Última</span> <i class="fas fa-angle-double-right"></i>
        </button>`;

        html += '</nav>';
        $('#pagination-controls').html(html);
    }

    function updateStats(pagination) {
        $('#stat-showing').text(pagination.total);
    }

    function showLoading() {
        $('#temas-container').html(`
            <div class="tw-flex tw-justify-center tw-items-center tw-p-12">
                <i class="fas fa-spinner fa-spin tw-text-brand-600 tw-text-3xl tw-mr-3"></i>
                <span class="tw-text-gray-600 tw-text-lg">Carregando temas...</span>
            </div>
        `);
        $('#pagination-container').hide();
    }

    window.changePage = function (page) {
        currentPage = page;
        loadTemas();
    };

    // ==================== EVENT LISTENERS ====================

    function initEventListeners() {
        // Filtros
        $('#per-page, #filter-status, #order-by, #order-direction').on('change', function () {
            if (this.id === 'per-page') perPage = parseInt($(this).val());
            else if (this.id === 'filter-status') filterStatus = $(this).val();
            else if (this.id === 'order-by') orderBy = $(this).val();
            else if (this.id === 'order-direction') orderDirection = $(this).val();

            currentPage = 1;
            loadTemas();
        });

        // Busca
        const debouncedSearch = debounce(function () {
            searchTerm = $('#search-input').val();
            currentPage = 1;
            loadTemas();
        }, 500);

        $('#search-input').on('keyup', debouncedSearch);

        // Checkboxes
        $(document).on('change', '.tema-checkbox', function () {
            const $tr = $(this).closest('tr');
            if ($(this).is(':checked')) {
                $tr.addClass('tw-bg-blue-50');
            } else {
                $tr.removeClass('tw-bg-blue-50');
                // Restaurar cor de criado se necessário
                if ($tr.hasClass('hide-created')) $tr.addClass('tw-bg-green-50');
            }
            updateSelectionCount();
        });

        // Select All Header (novo)
        $(document).on('change', '#select-all-header', function () {
            $('.tema-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
        });

        // Legacy Select All buttons (mantidos por compatibilidade se o HTML usá-los)
        $('#select-all-page').on('click', function () {
            $('.tema-checkbox').prop('checked', true).trigger('change');
        });

        $('#deselect-all').on('click', function () {
            $('.tema-checkbox').prop('checked', false).trigger('change');
        });

        $('#delete-selected').on('click', deleteSelectedTemas);

        // Criar Página
        $(document).on('click', '.btn-create', function () {
            const index = $(this).data('index');
            const temaId = $(this).data('id');
            const keyword = $(this).data('keyword');

            $('#div-create-' + index).hide();
            $('#div-create-label-' + index).show();

            const $input = $('#create-input-' + temaId);
            const currentValue = $input.val();

            if (!currentValue || currentValue.startsWith('"') || currentValue.startsWith("'")) {
                const cleanedKeyword = cleanKeyword(keyword);
                $input.val(titleCase(cleanedKeyword));
            }

            $input.focus();
        });

        $(document).on('click', '.btn-cancel-create', function () {
            const index = $(this).data('index');
            $('#div-create-' + index).show();
            $('#div-create-label-' + index).hide();
        });

        $(document).on('click', '.btn-submit-create', function () {
            const temaId = $(this).data('id');
            const index = $(this).data('index');
            const label = $('#create-input-' + temaId).val().trim();
            const $btn = $(this);

            if (!label) {
                alert('Por favor, digite um label para a página.');
                return;
            }

            const data = {
                'id': temaId,
                'label': label,
                'create': 1,
                '_token': csrfToken
            };

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: storeUrl,
                type: "POST",
                data: data,
                success: function (response) {
                    if (response.hasOwnProperty('success') && response['success'] == 1) {
                        $('#div-create-' + index).hide();
                        $('#div-create-label-' + index).hide();
                        $('#td-tema-' + temaId).addClass('hide-created tw-bg-green-50');
                        loadTemas();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Erro ao criar página:', error);
                    alert('Erro ao criar página.');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i>');
                }
            });
        });

        // Toggle created
        $("#toggle-created").on('click', function () {
            const $createdRows = $('.hide-created');
            if ($createdRows.is(":hidden")) {
                $createdRows.show(); // jQuery show sets display: table-row for tr
                $(this).text('Esconder criados');
            } else {
                $createdRows.hide();
                $(this).text('Mostrar criados');
            }
        });
    }

    // ==================== SELEÇÃO EM MASSA ====================

    function updateSelectionCount() {
        const count = $('.tema-checkbox:checked').length;
        $('#selected-count').text(count);

        if (count > 0) {
            $('#batch-actions').removeClass('tw-hidden');
        } else {
            $('#batch-actions').addClass('tw-hidden');
        }
    }

    function deleteSelectedTemas() {
        const selectedIds = [];

        $('.tema-checkbox:checked').each(function () {
            selectedIds.push($(this).data('tema-id'));
        });

        if (selectedIds.length === 0) return;

        if (!confirm(`Tem certeza que deseja deletar ${selectedIds.length} temas? Esta ação não pode ser desfeita.`)) {
            return;
        }

        const $btn = $('#delete-selected');
        const originalContent = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deletando...');

        let deleted = 0;
        let errors = 0;

        const deletePromises = selectedIds.map(id => {
            return $.ajax({
                url: deleteUrl,
                type: "POST",
                data: {
                    'id': id,
                    '_token': csrfToken
                }
            }).then(
                () => deleted++,
                () => errors++
            );
        });

        Promise.all(deletePromises).finally(function () {
            $btn.prop('disabled', false).html(originalContent);
            alert(`Concluído! ${deleted} deletados, ${errors} erros.`);
            loadTemas();
            $('#select-all-header').prop('checked', false);
        });
    }

})(jQuery);
