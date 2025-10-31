/**
 * Admin Dashboard JavaScript
 * Lógica para gerenciamento da área administrativa
 */

(function($) {
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

    // ==================== FUNÇÕES UTILITÁRIAS ====================
    
    /**
     * Primeira letra maiúscula, salvo se a palavra tiver menos de 3 caracteres
     */
    function titleCase(str, limit = 3) {
        var splitStr = str.toLowerCase().split(' ');
        for (var i = 0; i < splitStr.length; i++) {
            if(splitStr[i].length < limit) {
                continue;
            }
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
        }
        return splitStr.join(' '); 
    }

    /**
     * Escape HTML para evitar problemas com aspas em atributos
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Limpar keyword removendo aspas e aplicando title case
     */
    function cleanKeyword(keyword) {
        // Remover aspas duplas e simples do início e fim
        return keyword.replace(/^["']|["']$/g, '').trim();
    }

    /**
     * Debounce para busca em tempo real
     */
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
    
    $(document).ready(function() {
        console.log('Admin Dashboard JavaScript carregado!');
        console.log('URLs configuradas:', {
            store: storeUrl,
            delete: deleteUrl,
            getTemas: getTemasUrl
        });
        
        // Inicializar estilos para temas criados
        $('.td-tema-created').css('background-color', '#d4edda');
        
        // Event Listeners (serão implementados nos próximos passos)
        initEventListeners();
        
        // Carregar temas inicialmente
        loadTemas();
    });

    // ==================== CARREGAMENTO DE DADOS ====================
    
    /**
     * Carregar temas via AJAX
     */
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
            success: function(response) {
                if (response.success) {
                    renderTemas(response.data);
                    renderPagination(response.pagination);
                    updateStats(response.pagination);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar temas:', error);
                $('#temas-container').html('<div class="alert alert-danger">Erro ao carregar temas. Por favor, recarregue a página.</div>');
            }
        });
    }
    
    /**
     * Renderizar temas na tabela
     */
    function renderTemas(temas) {
        if (temas.length === 0) {
            $('#temas-container').html('<div class="alert alert-info">Nenhum tema encontrado com os filtros selecionados.</div>');
            $('#pagination-container').hide();
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr>';
        html += '<th width="50px">Sel</th>';
        html += '<th width="60px">ID</th>';
        html += '<th>Keyword</th>';
        html += '<th>Label</th>';
        html += '<th width="100px">Resultados</th>';
        html += '<th width="150px">Criado em</th>';
        html += '<th width="200px">Ações</th>';
        html += '</tr></thead><tbody>';
        
        temas.forEach((t, k) => {
            const class_created = (t.created_at && t.slug) ? 'hide-created' : '';
            
            // Se não tem label, usar keyword sem aspas e com title case
            let labelValue = t.label;
            if (!labelValue) {
                const cleanedKeyword = cleanKeyword(t.keyword);
                labelValue = titleCase(cleanedKeyword);
            }
            
            const keyword_display = (t.created_at && t.slug) ? 
                `<a href="/tema/${t.slug}" target="_blank">${t.keyword}</a>` : 
                t.keyword;
            
            html += `
                <tr id="td-tema-${t.id}" class="td-tema ${class_created}" data-tema-id="${t.id}">
                    <td class="text-center">
                        <input type="checkbox" class="tema-checkbox" data-tema-id="${t.id}">
                    </td>
                    <td>${t.id}</td>
                    <td>${keyword_display}</td>
                    <td>${labelValue}</td>
                    <td class="text-center">${t.results}</td>
                    <td>${t.created_at || '-'}</td>
                    <td>
                        <div id="div-create-${k}" ${t.created_at ? 'style="display:none"' : ''}>
                            <button class="btn btn-sm btn-success btn-create" data-id="${t.id}" data-keyword="${t.keyword}" data-index="${k}">
                                <i class="fa fa-plus-circle"></i> Criar Página
                            </button>
                        </div>
                        <div id="div-create-label-${k}" style="display:none">
                            <input type="text" id="create-input-${t.id}" class="form-control form-control-sm mb-2" placeholder="Digite o label..." value="${escapeHtml(labelValue)}">
                            <button class="btn btn-sm btn-primary btn-submit-create" data-id="${t.id}" data-index="${k}">
                                <i class="fa fa-check"></i> Confirmar
                            </button>
                            <button class="btn btn-sm btn-secondary btn-cancel-create" data-index="${k}">
                                <i class="fa fa-times"></i> Cancelar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        
        $('#temas-container').html(html);
        
        // Atualizar contador de selecionados
        updateSelectionCount();
    }
    
    /**
     * Renderizar paginação
     */
    function renderPagination(pagination) {
        if (pagination.last_page <= 1) {
            $('#pagination-container').hide();
            return;
        }
        
        $('#pagination-container').show();
        
        // Info
        const info = `Exibindo ${pagination.from} a ${pagination.to} de ${pagination.total} temas`;
        $('#pagination-info').text(info);
        
        // Controles
        let html = '';
        const current = pagination.current_page;
        const last = pagination.last_page;
        
        // Botão: Ir para o início
        html += `<button ${current === 1 ? 'disabled' : ''} onclick="changePage(1)" title="Primeira página">
            ◄◄
        </button>`;
        
        // Botão: Anterior
        html += `<button ${current === 1 ? 'disabled' : ''} onclick="changePage(${current - 1})" title="Página anterior">
            ◄
        </button>`;
        
        // Páginas numeradas - algoritmo mais limpo
        let pagesToShow = [];
        
        if (last <= 10) {
            // Se tem 10 ou menos páginas, mostra todas
            for (let i = 1; i <= last; i++) {
                pagesToShow.push(i);
            }
        } else {
            // Sempre adiciona a primeira
            pagesToShow.push(1);
            
            // Lógica para páginas do meio
            if (current <= 4) {
                // Início: 1 2 3 4 5 ... última
                for (let i = 2; i <= 5; i++) {
                    pagesToShow.push(i);
                }
                pagesToShow.push('...');
                pagesToShow.push(last);
            } else if (current >= last - 3) {
                // Fim: 1 ... antepenúltima-3 antepenúltima-2 antepenúltima-1 antepenúltima última
                pagesToShow.push('...');
                for (let i = last - 4; i <= last; i++) {
                    pagesToShow.push(i);
                }
            } else {
                // Meio: 1 ... atual-1 atual atual+1 ... última
                pagesToShow.push('...');
                pagesToShow.push(current - 1);
                pagesToShow.push(current);
                pagesToShow.push(current + 1);
                pagesToShow.push('...');
                pagesToShow.push(last);
            }
        }
        
        // Renderizar páginas
        pagesToShow.forEach(page => {
            if (page === '...') {
                html += '<span style="padding: 6px 12px; color: #6c757d;">...</span>';
            } else {
                html += `<button class="${page === current ? 'active' : ''}" onclick="changePage(${page})">${page}</button>`;
            }
        });
        
        // Botão: Próximo
        html += `<button ${current === last ? 'disabled' : ''} onclick="changePage(${current + 1})" title="Próxima página">
            ►
        </button>`;
        
        // Botão: Ir para o fim
        html += `<button ${current === last ? 'disabled' : ''} onclick="changePage(${last})" title="Última página">
            ►►
        </button>`;
        
        $('#pagination-controls').html(html);
    }
    
    /**
     * Atualizar estatísticas
     */
    function updateStats(pagination) {
        $('#stat-showing').text(pagination.total);
    }
    
    /**
     * Mostrar loading
     */
    function showLoading() {
        $('#temas-container').html(`
            <div class="admin-loading">
                <i class="fa fa-spinner fa-spin"></i>
                <p>Carregando temas...</p>
            </div>
        `);
        $('#pagination-container').hide();
    }
    
    /**
     * Mudar página
     */
    window.changePage = function(page) {
        currentPage = page;
        loadTemas();
    };

    // ==================== EVENT LISTENERS ====================
    
    function initEventListeners() {
        
        // Filtros
        $('#per-page').on('change', function() {
            perPage = parseInt($(this).val());
            currentPage = 1;
            loadTemas();
        });
        
        $('#filter-status').on('change', function() {
            filterStatus = $(this).val();
            currentPage = 1;
            loadTemas();
        });
        
        $('#order-by').on('change', function() {
            orderBy = $(this).val();
            currentPage = 1;
            loadTemas();
        });
        
        $('#order-direction').on('change', function() {
            orderDirection = $(this).val();
            currentPage = 1;
            loadTemas();
        });
        
        // Busca com debounce
        const debouncedSearch = debounce(function() {
            searchTerm = $('#search-input').val();
            currentPage = 1;
            loadTemas();
        }, 500);
        
        $('#search-input').on('keyup', debouncedSearch);
        
        // Seleção em massa
        $(document).on('change', '.tema-checkbox', function() {
            const $tr = $(this).closest('tr');
            if ($(this).is(':checked')) {
                $tr.addClass('selected');
            } else {
                $tr.removeClass('selected');
            }
            updateSelectionCount();
        });
        
        $('#select-all-page').on('click', function() {
            $('.tema-checkbox').prop('checked', true).trigger('change');
        });
        
        $('#deselect-all').on('click', function() {
            $('.tema-checkbox').prop('checked', false).trigger('change');
        });
        
        $('#delete-selected').on('click', function() {
            deleteSelectedTemas();
        });
        
        // Botão "Criar Página"
        $(document).on('click', '.btn-create', function() {
            const index = $(this).data('index');
            const temaId = $(this).data('id');
            
            // Esconder botão "Criar Página"
            $('#div-create-' + index).hide();
            
            // Mostrar campo de input e botões de confirmação
            $('#div-create-label-' + index).show();
            
            // Pegar o valor atual do input (que já foi renderizado corretamente)
            const currentValue = $('#create-input-' + temaId).val();
            
            // Se o valor atual estiver vazio ou for igual à keyword com aspas,
            // limpar e aplicar title case
            if (!currentValue || currentValue.startsWith('"') || currentValue.startsWith("'")) {
                const keyword = $(this).data('keyword');
                const cleanedKeyword = cleanKeyword(keyword);
                $('#create-input-' + temaId).val(titleCase(cleanedKeyword));
            }
            
            // Dar foco no input
            $('#create-input-' + temaId).focus();
        });
        
        // Botão "Cancelar"
        $(document).on('click', '.btn-cancel-create', function() {
            const index = $(this).data('index');
            
            // Mostrar botão "Criar Página" novamente
            $('#div-create-' + index).show();
            
            // Esconder campo de input
            $('#div-create-label-' + index).hide();
        });
        
        // Botão "Confirmar Criação"
        $(document).on('click', '.btn-submit-create', function() {
            const temaId = $(this).data('id');
            const index = $(this).data('index');
            const label = $('#create-input-' + temaId).val().trim();
            
            if (!label) {
                alert('Por favor, digite um label para a página.');
                return;
            }
            
            const token = $("input[name='_token']").val();
            
            const data = {
                'id': temaId,
                'label': label,
                'create': 1,
                '_token': token
            };
            
            // Desabilitar botão durante o envio
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Criando...');
            
            $.ajax({
                url: storeUrl,
                type: "POST",
                data: data,
                success: function(response) {
                    if(response.hasOwnProperty('success') && response['success'] == 1) {
                        // Esconder toda a área de ações
                        $('#div-create-' + index).hide();
                        $('#div-create-label-' + index).hide();
                        
                        // Adicionar classe visual de "criado"
                        $('#td-tema-' + temaId).addClass('hide-created');
                        
                        // Recarregar para atualizar estatísticas
                        loadTemas();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao criar página:', error);
                    alert('Erro ao criar página. Por favor, tente novamente.');
                    
                    // Re-habilitar botão
                    $(this).prop('disabled', false).html('<i class="fa fa-check"></i> Confirmar');
                }
            });
        });
        
        // Toggle created
        $("#toggle-created").on('click', function() {
            if($('.hide-created').is(":hidden")) {
                $('.hide-created').show();
                $(this).text('hide created');
            } else {
                $('.hide-created').hide();
                $(this).text('show created');
            }
        });
    }

    // ==================== SELEÇÃO EM MASSA ====================
    
    /**
     * Atualizar contador de selecionados
     */
    function updateSelectionCount() {
        const count = $('.tema-checkbox:checked').length;
        $('#selected-count').text(count);
        
        if (count > 0) {
            $('#deselect-all').show();
            $('#batch-selection-info').show();
        } else {
            $('#deselect-all').hide();
            $('#batch-selection-info').hide();
        }
        
        // Mostrar/esconder "Marcar todos" baseado na quantidade selecionada
        const totalOnPage = $('.tema-checkbox').length;
        if (count === totalOnPage && totalOnPage > 0) {
            $('#select-all-page').hide();
        } else if (totalOnPage > 0) {
            $('#select-all-page').show();
        }
    }
    
    /**
     * Deletar temas selecionados
     */
    function deleteSelectedTemas() {
        const selectedIds = [];
        const selectedRows = [];
        
        $('.tema-checkbox:checked').each(function() {
            const $row = $(this).closest('tr');
            selectedIds.push($(this).data('tema-id'));
            selectedRows.push($row.find('td:eq(2)').text()); // Pega o keyword da 3ª coluna
        });
        
        if (selectedIds.length === 0) {
            alert('Nenhum tema selecionado para deletar.');
            return;
        }
        
        // Montar mensagem de confirmação
        let message = `Você está prestes a DELETAR ${selectedIds.length} tema(s):\n\n`;
        
        // Mostrar até 10 keywords na confirmação
        const previewCount = Math.min(10, selectedRows.length);
        for (let i = 0; i < previewCount; i++) {
            message += `- ${selectedRows[i]}\n`;
        }
        
        if (selectedRows.length > 10) {
            message += `... e mais ${selectedRows.length - 10} tema(s)\n`;
        }
        
        message += `\n⚠️ ESTA AÇÃO NÃO PODE SER DESFEITA!\n\nDeseja continuar?`;
        
        if (!confirm(message)) {
            return;
        }
        
        // Desabilitar botão enquanto processa
        $('#delete-selected').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deletando...');
        
        // Deletar em lote
        let deleted = 0;
        let errors = 0;
        
        const deletePromises = selectedIds.map(id => {
            return $.ajax({
                url: deleteUrl,
                type: "POST",
                data: {
                    'id': id,
                    '_token': $("meta[name='csrf-token']").attr('content')
                }
            }).then(
                function(response) {
                    if (response.success == 1) {
                        deleted++;
                    } else {
                        errors++;
                    }
                },
                function() {
                    errors++;
                }
            );
        });
        
        // Quando todos terminarem
        Promise.all(deletePromises).finally(function() {
            $('#delete-selected').prop('disabled', false).html('<i class="fa fa-trash"></i> Deletar selecionados');
            
            if (errors > 0) {
                alert(`Operação concluída!\n\n✓ ${deleted} tema(s) deletado(s)\n✗ ${errors} erro(s)`);
            } else {
                alert(`✓ ${deleted} tema(s) deletado(s) com sucesso!`);
            }
            
            // Recarregar a página atual
            loadTemas();
        });
    }

})(jQuery);
