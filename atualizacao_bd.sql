-- Criar tabela para convocatórias
CREATE TABLE IF NOT EXISTS convocatorias (
    Id int(11) NOT NULL AUTO_INCREMENT,
    Titulo varchar(255) NOT NULL,
    Descricao text NOT NULL,
    Data datetime NOT NULL,
    Local varchar(255) NOT NULL,
    Id_departamento int(11) NOT NULL,
    Id_criador int(11) NOT NULL,
    Data_criacao timestamp NOT NULL DEFAULT current_timestamp(),
    Estado enum('Agendada','Realizada','Cancelada') NOT NULL DEFAULT 'Agendada',
    PRIMARY KEY (Id),
    FOREIGN KEY (Id_departamento) REFERENCES departamentos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Id_criador) REFERENCES usuario(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela para participantes das convocatórias
CREATE TABLE IF NOT EXISTS convocatoria_participantes (
    Id int(11) NOT NULL AUTO_INCREMENT,
    Id_convocatoria int(11) NOT NULL,
    Id_usuario int(11) NOT NULL,
    Confirmacao enum('Pendente','Confirmado','Recusado') NOT NULL DEFAULT 'Pendente',
    Data_confirmacao timestamp NULL DEFAULT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY unique_participante (Id_convocatoria, Id_usuario),
    FOREIGN KEY (Id_convocatoria) REFERENCES convocatorias(Id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (Id_usuario) REFERENCES usuario(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Criar tabela para anexos das convocatórias
CREATE TABLE IF NOT EXISTS convocatoria_anexos (
    Id int(11) NOT NULL AUTO_INCREMENT,
    Id_convocatoria int(11) NOT NULL,
    Nome_arquivo varchar(255) NOT NULL,
    Caminho_arquivo varchar(255) NOT NULL,
    Tipo_arquivo varchar(100) NOT NULL,
    Tamanho int(11) NOT NULL,
    Data_upload timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (Id),
    FOREIGN KEY (Id_convocatoria) REFERENCES convocatorias(Id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adicionar novo tipo de notificação para convocatórias
ALTER TABLE notificacoes 
MODIFY COLUMN Tipo enum('Documento','Aprovacao','Sistema','Requisicao','Partilha','Convocatoria') NOT NULL;

-- Criar índices para melhor performance
CREATE INDEX idx_convocatorias_departamento ON convocatorias(Id_departamento);
CREATE INDEX idx_convocatorias_criador ON convocatorias(Id_criador);
CREATE INDEX idx_convocatoria_participantes_usuario ON convocatoria_participantes(Id_usuario);
CREATE INDEX idx_convocatoria_anexos_convocatoria ON convocatoria_anexos(Id_convocatoria);

-- Adicionar triggers para notificações automáticas
DELIMITER //

CREATE TRIGGER after_convocatoria_insert 
AFTER INSERT ON convocatorias
FOR EACH ROW
BEGIN
    -- Criar notificação para o departamento
    INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
    SELECT 
        CONCAT('Nova convocatória: ', NEW.Titulo, '\nData: ', DATE_FORMAT(NEW.Data, '%d/%m/%Y %H:%i'), '\nLocal: ', NEW.Local),
        'Convocatoria',
        'Pendente',
        NEW.Id_criador,
        NEW.Id,
        'Convocatoria',
        u.Id
    FROM usuario u
    WHERE u.Id_Departamento = NEW.Id_departamento AND u.Id != NEW.Id_criador;
END //

CREATE TRIGGER after_participante_insert
AFTER INSERT ON convocatoria_participantes
FOR EACH ROW
BEGIN
    -- Buscar informações da convocatória
    DECLARE v_titulo VARCHAR(255);
    DECLARE v_data DATETIME;
    DECLARE v_criador INT;
    
    SELECT Titulo, Data, Id_criador 
    INTO v_titulo, v_data, v_criador
    FROM convocatorias 
    WHERE Id = NEW.Id_convocatoria;
    
    -- Criar notificação para o participante
    INSERT INTO notificacoes (Descricao, Tipo, Estado, Id_usuario, Id_origem, Tipo_origem, Para)
    VALUES (
        CONCAT('Você foi adicionado à convocatória: ', v_titulo, '\nData: ', DATE_FORMAT(v_data, '%d/%m/%Y %H:%i')),
        'Convocatoria',
        'Pendente',
        v_criador,
        NEW.Id_convocatoria,
        'Convocatoria',
        NEW.Id_usuario
    );
END //

DELIMITER ; 