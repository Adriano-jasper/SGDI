-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23-Maio-2025 às 15:38
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pgdi`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `acessos`
--

CREATE TABLE `acessos` (
  `Id` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL COMMENT 'Login, Download, Visualizacao, etc',
  `Descricao` text DEFAULT NULL,
  `Ip` varchar(45) DEFAULT NULL,
  `Data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `Nome` varchar(150) NOT NULL,
  `Descricao` varchar(300) NOT NULL,
  `Id_Chefe` int(11) DEFAULT NULL,
  `Data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `Ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `departamentos`
--

INSERT INTO `departamentos` (`id`, `Nome`, `Descricao`, `Id_Chefe`, `Data_criacao`, `Ativo`) VALUES
(27, 'Finanças', 'Todos os assuntos relacionados com Finanças', 3, '2025-04-20 23:00:00', 1),
(29, 'REDES', 'lidar com a infraestrutura', NULL, '2025-04-30 12:09:45', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `documentos`
--

CREATE TABLE `documentos` (
  `Id` int(11) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Descricao` varchar(255) NOT NULL,
  `Data` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Id_usuario` int(11) NOT NULL,
  `Caminho_Doc` varchar(150) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Tamanho` int(11) NOT NULL COMMENT 'Tamanho em bytes',
  `Estado` enum('Rascunho','Pendente','Aprovado','Rejeitado') NOT NULL DEFAULT 'Rascunho',
  `Id_aprovador` int(11) DEFAULT NULL,
  `Data_aprovacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `documentos`
--

INSERT INTO `documentos` (`Id`, `Titulo`, `Descricao`, `Data`, `Id_usuario`, `Caminho_Doc`, `Tipo`, `Tamanho`, `Estado`, `Id_aprovador`, `Data_aprovacao`) VALUES
(5, 'detalhes', 'qwertyuio', '2025-04-30 12:37:16', 5, '681218e36bb0d.pdf', '', 0, 'Aprovado', NULL, NULL),
(8, 'exemplo12', 'novo doc', '2025-05-10 17:06:26', 3, '681f8792a6c6a.pdf', '', 0, 'Rascunho', NULL, NULL),
(10, 'Enelvin', '3e4rrr', '2025-05-19 00:03:05', 2, '682a671057361.pdf', '', 0, 'Aprovado', NULL, NULL),
(11, 'word exemplo', 'descrição 1', '2025-05-19 11:57:00', 2, '682b1c8c2f936.xlsx', '', 0, 'Rascunho', NULL, NULL),
(12, 'ariano', 'rffg', '2025-05-19 12:38:51', 2, '682b265bb9d22.doc', '', 0, 'Rascunho', NULL, NULL),
(13, 'teste1', 'teste1', '2025-05-20 15:33:53', 3, '682ca0e153b4f.xlsx', '', 0, 'Rascunho', NULL, NULL),
(14, 'teste4', 'teste', '2025-05-23 12:45:37', 2, '682ca4cecd61c.docx', '', 0, 'Aprovado', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `documento_departamento`
--

CREATE TABLE `documento_departamento` (
  `Id` int(11) NOT NULL,
  `Id_documento` int(11) NOT NULL,
  `Id_departamento` int(11) NOT NULL,
  `Data_associacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `documento_departamento`
--

INSERT INTO `documento_departamento` (`Id`, `Id_documento`, `Id_departamento`, `Data_associacao`) VALUES
(7, 5, 29, '2025-04-30 12:37:16'),
(16, 10, 29, '2025-05-19 00:03:05'),
(17, 10, 29, '2025-05-19 00:04:50'),
(18, 14, 27, '2025-05-23 12:45:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fluxo_trabalho`
--

CREATE TABLE `fluxo_trabalho` (
  `Id` int(11) NOT NULL,
  `Id_documento` int(11) NOT NULL,
  `Id_de` int(11) NOT NULL COMMENT 'Usuário que enviou',
  `Id_para` int(11) NOT NULL COMMENT 'Usuário/departamento destinatário',
  `Tipo_destino` enum('Usuario','Departamento') NOT NULL,
  `Acao` enum('Enviar','Aprovar','Rejeitar','Devolver') NOT NULL,
  `Comentario` text DEFAULT NULL,
  `Data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `fluxo_trabalho`
--

INSERT INTO `fluxo_trabalho` (`Id`, `Id_documento`, `Id_de`, `Id_para`, `Tipo_destino`, `Acao`, `Comentario`, `Data`) VALUES
(32, 10, 3, 4, 'Usuario', 'Enviar', 'Aprovado por jorge - Enviado para chefe de Finanças', '2025-05-18 23:53:24'),
(36, 14, 3, 27, 'Departamento', 'Aprovar', 'Documento aprovado e compartilhado com departamento', '2025-05-23 12:45:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_visualizacao`
--

CREATE TABLE `historico_visualizacao` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_documento` int(11) NOT NULL,
  `data_visualizacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `Id` int(11) NOT NULL,
  `Descricao` varchar(300) NOT NULL,
  `Tipo` enum('Documento','Aprovacao','Sistema','Requisicao','Partilha') NOT NULL,
  `Estado` enum('Pendente','Aceite','Negada') NOT NULL DEFAULT 'Pendente',
  `Id_usuario` int(11) DEFAULT NULL,
  `Data` timestamp NOT NULL DEFAULT current_timestamp(),
  `Id_origem` int(11) DEFAULT NULL COMMENT 'ID do documento ou item relacionado',
  `Tipo_origem` varchar(50) DEFAULT NULL COMMENT 'Tipo do item relacionado',
  `Visualizada` tinyint(1) DEFAULT 0,
  `Para` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `notificacoes`
--

INSERT INTO `notificacoes` (`Id`, `Descricao`, `Tipo`, `Estado`, `Id_usuario`, `Data`, `Id_origem`, `Tipo_origem`, `Visualizada`, `Para`) VALUES
(37, 'Você aprovou a solicitação de partilha do documento: grafico\nSolicitante: Adriano\nData: 18/05/2025 22:06', 'Requisicao', 'Aceite', 3, '2025-05-12 14:32:10', 4, 'Solicitacao', 1, 3),
(38, 'NOVO DOCUMENTO COMPARTILHADO\n\nDocumento: grafico\nDepartamento: Finanças\nAprovado por: jorge\n\nEste documento foi compartilhado com todo o departamento.', 'Documento', 'Aceite', 2, '2025-05-18 20:06:26', 4, 'Documento', 1, 2),
(39, 'Sua solicitação de partilha do documento \'grafico\' foi aprovada por jorge.', 'Aprovacao', 'Aceite', 2, '2025-05-18 20:06:26', 4, 'Documento', 1, 2),
(43, 'Sua solicitação para o departamento Finanças foi aprovada por jorge e encaminhada para o chefe do departamento de destino.', 'Aprovacao', 'Pendente', 2, '2025-05-18 21:06:14', 1, 'Documento', 1, 2),
(54, 'Sua solicitação para o departamento Finanças foi aprovada por jorge e encaminhada para o chefe do departamento de destino.', 'Aprovacao', 'Pendente', 2, '2025-05-18 23:53:24', 10, 'Documento', 1, NULL),
(57, 'Sua solicitação de partilha do documento \'Enelvin\' foi aprovada por Benvindo Matias.', 'Aprovacao', 'Aceite', 2, '2025-05-19 00:03:05', 10, 'Documento', 1, NULL),
(59, 'Sua solicitação de partilha do documento \'Enelvin\' foi aprovada por Benvindo Matias.', 'Aprovacao', 'Aceite', 2, '2025-05-19 00:04:51', 10, 'Documento', 1, NULL),
(60, 'Você aprovou a solicitação de partilha do documento: teste4\nSolicitante: Adriano\nData: 23/05/2025 14:45', 'Requisicao', 'Aceite', 3, '2025-05-20 15:50:52', 14, 'Solicitacao', 1, NULL),
(61, 'NOVO DOCUMENTO COMPARTILHADO\n\nDepartamento: Finanças\nAprovado por: jorge\n\nEste documento foi compartilhado com todo o departamento.', 'Documento', 'Aceite', 2, '2025-05-23 12:45:37', 14, 'Documento', 1, NULL),
(62, 'Sua solicitação de partilha do documento \'teste4\' foi aprovada por jorge.', 'Aprovacao', 'Aceite', 2, '2025-05-23 12:45:37', 14, 'Documento', 1, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(6) NOT NULL,
  `expira_em` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `Id` int(11) NOT NULL,
  `Descricao` varchar(150) DEFAULT NULL,
  `Tipo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `permissoes`
--

INSERT INTO `permissoes` (`Id`, `Descricao`, `Tipo`) VALUES
(1, 'prmite visualizar Documentos ', 'visualizar'),
(2, 'permite o usuario deletar documentos', 'Deletar'),
(3, 'permite o usuario partilhar documentos', 'partilhar'),
(4, 'permite o usuario descarregar documentos dentro da PGDI', 'Download'),
(5, 'permite que o usuario edite documentos dentro da plataforma', 'Editar');

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios`
--

CREATE TABLE `relatorios` (
  `Id` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Parametros` text DEFAULT NULL COMMENT 'JSON com parâmetros usados',
  `Caminho_arquivo` varchar(255) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `Id` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `Telefone` varchar(14) NOT NULL,
  `Senha` varchar(255) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Permissao` enum('0','1','2') NOT NULL COMMENT '0=Admin, 2=Chefe, 1=Normal',
  `Genero` enum('M','F','O') NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Id_Departamento` int(11) DEFAULT NULL,
  `Caminho_da_Ft` varchar(150) DEFAULT NULL,
  `Ativo` tinyint(1) NOT NULL DEFAULT 1,
  `Ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`Id`, `Nome`, `Telefone`, `Senha`, `Email`, `Permissao`, `Genero`, `Created_at`, `Id_Departamento`, `Caminho_da_Ft`, `Ativo`, `Ultimo_login`) VALUES
(1, 'Adriano', '936323888', 'admin', 'adrianopalanca03@gmail.com', '0', 'M', '2024-11-07 16:08:28', NULL, NULL, 1, NULL),
(2, 'Adriano', '92345678', '1234', 'anacasiana70@gmail.com', '1', 'M', '2025-04-25 23:20:15', 27, '6830702124d48.jpg', 1, NULL),
(3, 'jorge', '926052415', '1234', 'abellutoma6@gmail.com', '2', 'M', '2025-04-25 23:32:28', 27, NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario_permissoes`
--

CREATE TABLE `usuario_permissoes` (
  `Id` int(11) NOT NULL,
  `Id_usuario` int(11) DEFAULT NULL,
  `Id_permissao` int(11) NOT NULL,
  `Estado` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario_permissoes`
--

INSERT INTO `usuario_permissoes` (`Id`, `Id_usuario`, `Id_permissao`, `Estado`) VALUES
(7, 2, 1, 1),
(8, 2, 2, 1),
(9, 2, 3, 1),
(10, 2, 4, 1),
(11, 2, 5, 1),
(12, 3, 1, 1),
(13, 3, 2, 1),
(14, 3, 3, 1),
(15, 3, 4, 1),
(16, 3, 5, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `acessos`
--
ALTER TABLE `acessos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Índices para tabela `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Id_Chefe` (`Id_Chefe`);

--
-- Índices para tabela `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_usuario` (`Id_usuario`),
  ADD KEY `Id_aprovador` (`Id_aprovador`);

--
-- Índices para tabela `documento_departamento`
--
ALTER TABLE `documento_departamento`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_documento` (`Id_documento`),
  ADD KEY `Id_departamento` (`Id_departamento`);

--
-- Índices para tabela `fluxo_trabalho`
--
ALTER TABLE `fluxo_trabalho`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_documento` (`Id_documento`),
  ADD KEY `Id_de` (`Id_de`),
  ADD KEY `Id_para` (`Id_para`);

--
-- Índices para tabela `historico_visualizacao`
--
ALTER TABLE `historico_visualizacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_documento` (`id_documento`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_usuario` (`Id_usuario`),
  ADD KEY `notificacoes_ibfk_3` (`Para`);

--
-- Índices para tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`Id`);

--
-- Índices para tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Id_usuario` (`Id_usuario`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `usuario_ibfk_1` (`Id_Departamento`);

--
-- Índices para tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `usuario_permissoes_ibfk_1` (`Id_usuario`),
  ADD KEY `usuario_permissoes_ibfk_2` (`Id_permissao`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `acessos`
--
ALTER TABLE `acessos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `documentos`
--
ALTER TABLE `documentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `documento_departamento`
--
ALTER TABLE `documento_departamento`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `fluxo_trabalho`
--
ALTER TABLE `fluxo_trabalho`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `historico_visualizacao`
--
ALTER TABLE `historico_visualizacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `acessos`
--
ALTER TABLE `acessos`
  ADD CONSTRAINT `acessos_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id`);

--
-- Limitadores para a tabela `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`Id_Chefe`) REFERENCES `usuario` (`Id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Limitadores para a tabela `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`Id_aprovador`) REFERENCES `usuario` (`Id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `fluxo_trabalho`
--
ALTER TABLE `fluxo_trabalho`
  ADD CONSTRAINT `fluxo_trabalho_ibfk_1` FOREIGN KEY (`Id_documento`) REFERENCES `documentos` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fluxo_trabalho_ibfk_2` FOREIGN KEY (`Id_de`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `historico_visualizacao`
--
ALTER TABLE `historico_visualizacao`
  ADD CONSTRAINT `historico_visualizacao_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historico_visualizacao_ibfk_2` FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_2` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notificacoes_ibfk_3` FOREIGN KEY (`Para`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD CONSTRAINT `relatorios_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id`);

--
-- Limitadores para a tabela `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`Id_Departamento`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limitadores para a tabela `usuario_permissoes`
--
ALTER TABLE `usuario_permissoes`
  ADD CONSTRAINT `usuario_permissoes_ibfk_1` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuario_permissoes_ibfk_2` FOREIGN KEY (`Id_permissao`) REFERENCES `permissoes` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
