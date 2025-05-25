$(document).ready(function() {
    // Carregar convocatórias ao iniciar
    carregarConvocatorias();
    
    // Configurar verificação periódica de convocatórias próximas
    setInterval(verificarConvocatoriasProximas, 60000); // Verifica a cada minuto
    verificarConvocatoriasProximas(); // Verifica imediatamente ao carregar
    
    // Configurar o campo de data/hora para não permitir datas passadas
    function configurarDataMinima() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const dataMinima = `${year}-${month}-${day}T${hours}:${minutes}`;
        $('#data, #novaData').attr('min', dataMinima);
    }
    
    configurarDataMinima();
    
    // Atualizar a data mínima a cada minuto
    setInterval(configurarDataMinima, 60000);
    
    // Validar formulário antes do envio
    $('#formConvocatoria').on('submit', function(e) {
        e.preventDefault();
        
        const titulo = $('#titulo').val().trim();
        const descricao = $('#descricao').val().trim();
        const dataStr = $('#data').val();
        const local = $('#local').val().trim();
        
        // Validar campos obrigatórios
        if(!titulo || !descricao || !dataStr || !local) {
            mostrarErro('Todos os campos são obrigatórios');
            return false;
        }
        
        // Validar data e hora
        const dataConvocatoria = new Date(dataStr);
        const agora = new Date();
        
        // Verificar se é uma data válida
        if(isNaN(dataConvocatoria.getTime())) {
            mostrarErro('Data inválida');
            return false;
        }
        
        // Comparar apenas as datas (sem hora)
        const dataConvocatoriaSemHora = new Date(dataConvocatoria.getFullYear(), dataConvocatoria.getMonth(), dataConvocatoria.getDate());
        const agoraSemHora = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate());
        
        if(dataConvocatoriaSemHora < agoraSemHora) {
            mostrarErro('A data não pode ser no passado');
            return false;
        } else if(dataConvocatoriaSemHora.getTime() === agoraSemHora.getTime()) {
            // Se for hoje, validar a hora
            if(dataConvocatoria.getHours() < agora.getHours() || 
              (dataConvocatoria.getHours() === agora.getHours() && 
               dataConvocatoria.getMinutes() <= agora.getMinutes())) {
                mostrarErro('Para hoje, a hora deve ser maior que a hora atual');
                return false;
            }
        }
        
        // Validar seleção de participantes
        if(!$('#todosMembrosDept').is(':checked') && !$('.membro-checkbox:checked').length) {
            mostrarErro('Selecione pelo menos um participante');
            return false;
        }
        
        // Se passou por todas as validações, enviar o formulário
        const formData = new FormData(this);
        
        $.ajax({
            url: 'salvar_convocatoria.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if(data.success) {
                        mostrarSucesso(data.message);
                        $('#novaConvocatoriaModal').modal('hide');
                        $('#formConvocatoria')[0].reset();
                        window.location.reload(); // Recarregar a página para mostrar a nova convocatória
                    } else {
                        mostrarErro(data.error || 'Erro ao salvar convocatória');
                    }
                } catch(e) {
                    mostrarErro('Erro ao processar resposta do servidor');
                }
            },
            error: function() {
                mostrarErro('Erro de conexão com o servidor');
            }
        });
    });
    
    // Função para carregar convocatórias
    function carregarConvocatorias() {
        $.ajax({
            url: 'listar_convocatorias.php',
            type: 'GET',
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    if(data.success) {
                        const tbody = $('#tabelaConvocatorias tbody');
                        tbody.empty();
                        
                        data.convocatorias.forEach(function(conv) {
                            const data = new Date(conv.Data);
                            const dataFormatada = data.toLocaleDateString('pt-BR') + ' ' + 
                                                data.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
                            
                            const row = $('<tr>');
                            row.append($('<td>').text(conv.Titulo));
                            row.append($('<td>').text(dataFormatada));
                            row.append($('<td>').text(conv.Local));
                            row.append($('<td>').text(conv.Estado));
                            
                            const acoes = $('<td>');
                            acoes.append(`
                                <button class="btn btn-info btn-sm" onclick="verDetalhes(${conv.Id})">
                                    <i class="fa fa-eye"></i>
                                </button>
                            `);
                            
                            if(conv.Estado === 'Agendada') {
                                acoes.append(`
                                    <button class="btn btn-success btn-sm ml-1" onclick="marcarRealizada(${conv.Id})">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm ml-1" onclick="cancelarConvocatoria(${conv.Id})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                `);
                            }
                            
                            row.append(acoes);
                            tbody.append(row);
                        });
                    } else {
                        mostrarErro('Erro ao carregar convocatórias');
                    }
                } catch(e) {
                    mostrarErro('Erro ao processar lista de convocatórias');
                }
            },
            error: function() {
                mostrarErro('Erro de conexão ao carregar convocatórias');
            }
        });
    }
    
    // Função para mostrar mensagens de erro
    function mostrarErro(mensagem) {
        const alertDiv = $('<div>')
            .addClass('alert alert-danger alert-dismissible fade show')
            .html(`
                ${mensagem}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `);
        
        $('#mensagens').html(alertDiv);
        
        // Remover alerta após 5 segundos
        setTimeout(function() {
            alertDiv.alert('close');
        }, 5000);
    }
    
    // Função para mostrar mensagens de sucesso
    function mostrarSucesso(mensagem) {
        const alertDiv = $('<div>')
            .addClass('alert alert-success alert-dismissible fade show')
            .html(`
                ${mensagem}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `);
        
        $('#mensagens').html(alertDiv);
    }
    
    // Gerenciar seleção de participantes
    $('#todosMembrosDept').change(function() {
        if($(this).is(':checked')) {
            $('.membro-checkbox').prop('checked', false).prop('disabled', true);
        } else {
            $('.membro-checkbox').prop('disabled', false);
        }
    });
    
    // Limpar formulário quando o modal for fechado
    $('#novaConvocatoriaModal').on('hidden.bs.modal', function() {
        $('#formConvocatoria')[0].reset();
        $('.membro-checkbox').prop('disabled', false);
        $('#mensagens').empty();
    });
});

// Funções globais para ações da tabela
function verDetalhes(id) {
    $.ajax({
        url: 'Convocatorias.php',
        type: 'GET',
        data: { 
            action: 'detalhes',
            id: id 
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if(data.success) {
                    const convocatoria = data.convocatoria;
                    $('#detalhesConvocatoria').html(`
                        <div class="row">
                            <div class="col-md-12">
                                <h4>${convocatoria.Titulo}</h4>
                                <p class="text-muted">
                                    <strong>Data:</strong> ${convocatoria.Data_formatada}<br>
                                    <strong>Local:</strong> ${convocatoria.Local}<br>
                                    <strong>Estado:</strong> ${convocatoria.Estado}
                                </p>
                                <div class="descricao-convocatoria">
                                    ${convocatoria.Descricao}
                                </div>
                                <hr>
                                <div class="participantes">
                                    <h5>Participantes (${convocatoria.total_participantes})</h5>
                                    <ul class="list-unstyled">
                                        ${convocatoria.participantes.map(p => `
                                            <li>
                                                ${p.Nome} 
                                                ${p.Confirmacao ? `
                                                    <span class="badge badge-${p.Confirmacao === 'Confirmado' ? 'success' : 
                                                                                p.Confirmacao === 'Recusado' ? 'danger' : 'warning'}">
                                                        ${p.Confirmacao}
                                                    </span>
                                                ` : ''}
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                                ${convocatoria.total_anexos > 0 ? `
                                    <hr>
                                    <div class="anexos">
                                        <h5>Anexos (${convocatoria.total_anexos})</h5>
                                        <ul class="list-unstyled">
                                            ${convocatoria.anexos.map(a => `
                                                <li>
                                                    <a href="downloads.php?arquivo=${a.Caminho_arquivo}" target="_blank">
                                                        <i class="fa fa-file"></i> ${a.Nome_arquivo}
                                                    </a>
                                                </li>
                                            `).join('')}
                                        </ul>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `);

                    // Mostrar/ocultar botões de ação baseado no estado
                    const btnAdiar = $('#btnAdiar');
                    const btnCancelar = $('#btnCancelar');
                    
                    if(convocatoria.Estado === 'Agendada') {
                        btnAdiar.show();
                        btnCancelar.show();
                        
                        // Configurar handlers para os botões
                        btnAdiar.off('click').on('click', function() {
                            $('#convocatoriaId').val(convocatoria.Id);
                            $('#modalDetalhes').modal('hide');
                            $('#modalAdiar').modal('show');
                        });
                        
                        btnCancelar.off('click').on('click', function() {
                            if(confirm('Tem certeza que deseja cancelar esta convocatória?')) {
                                cancelarConvocatoria(convocatoria.Id);
                            }
                        });
                    } else {
                        btnAdiar.hide();
                        btnCancelar.hide();
                    }
                    
                    $('#modalDetalhes').modal('show');
                } else {
                    mostrarErro('Erro ao carregar detalhes');
                }
            } catch(e) {
                mostrarErro('Erro ao processar detalhes');
            }
        },
        error: function() {
            mostrarErro('Erro de conexão');
        }
    });
}

function marcarRealizada(id) {
    if(confirm('Confirma que esta convocatória foi realizada?')) {
        atualizarEstado(id, 'Realizada');
    }
}

function cancelarConvocatoria(id) {
    if(confirm('Tem certeza que deseja cancelar esta convocatória?')) {
        atualizarEstado(id, 'Cancelada');
    }
}

function atualizarEstado(id, estado) {
    $.ajax({
        url: 'Convocatorias.php',
        type: 'POST',
        data: { 
            action: 'atualizar_estado',
            id: id,
            estado: estado
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if(data.success) {
                    window.location.reload();
                } else {
                    mostrarErro(data.error || 'Erro ao atualizar estado');
                }
            } catch(e) {
                mostrarErro('Erro ao processar resposta');
            }
        },
        error: function() {
            mostrarErro('Erro de conexão');
        }
    });
}

function verificarConvocatoriasProximas() {
    $.ajax({
        url: 'verificar_convocatorias.php',
        type: 'GET',
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if(data.convocatorias_proximas && data.convocatorias_proximas.length > 0) {
                    data.convocatorias_proximas.forEach(function(conv) {
                        if(!conv.confirmacao_solicitada) {
                            $('#convocatoriaIdConfirmacao').val(conv.Id);
                            $('#modalConfirmacaoRealizacao').modal('show');
                        }
                    });
                }
            } catch(e) {
                console.error('Erro ao verificar convocatórias próximas:', e);
            }
        }
    });
}

function confirmarAdiamento() {
    const id = $('#convocatoriaId').val();
    const novaData = $('#novaData').val();
    const motivo = $('#motivoAdiamento').val();
    
    if(!novaData || !motivo) {
        mostrarErro('Preencha todos os campos obrigatórios');
        return;
    }
    
    $.ajax({
        url: 'Convocatorias.php',
        type: 'POST',
        data: {
            action: 'adiar',
            id: id,
            nova_data: novaData,
            motivo: motivo
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if(data.success) {
                    mostrarSucesso('Convocatória adiada com sucesso');
                    $('#modalAdiar').modal('hide');
                    window.location.reload();
                } else {
                    mostrarErro(data.error || 'Erro ao adiar convocatória');
                }
            } catch(e) {
                mostrarErro('Erro ao processar resposta');
            }
        },
        error: function() {
            mostrarErro('Erro de conexão');
        }
    });
}

function confirmarRealizacao() {
    const id = $('#convocatoriaIdConfirmacao').val();
    const observacoes = $('#observacoes').val();
    
    $.ajax({
        url: 'Convocatorias.php',
        type: 'POST',
        data: {
            action: 'atualizar_estado',
            id: id,
            estado: 'Realizada',
            observacoes: observacoes
        },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if(data.success) {
                    mostrarSucesso('Convocatória marcada como realizada');
                    $('#modalConfirmacaoRealizacao').modal('hide');
                    window.location.reload();
                } else {
                    mostrarErro(data.error || 'Erro ao atualizar estado');
                }
            } catch(e) {
                mostrarErro('Erro ao processar resposta');
            }
        },
        error: function() {
            mostrarErro('Erro de conexão');
        }
    });
}

function naoRealizada() {
    const id = $('#convocatoriaIdConfirmacao').val();
    $('#modalConfirmacaoRealizacao').modal('hide');
    $('#convocatoriaId').val(id);
    $('#modalAdiar').modal('show');
} 