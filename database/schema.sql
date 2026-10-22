-- ============================================================
-- Clínica Aurora — Sistema de Agendamentos e Clientes
-- MySQL / MariaDB  —  charset utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS clinica_aurora
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE clinica_aurora;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS agendamentos;
DROP TABLE IF EXISTS servicos;
DROP TABLE IF EXISTS clientes;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Clientes da clínica / salão
-- ------------------------------------------------------------
CREATE TABLE clientes (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(120) NOT NULL,
    telefone         VARCHAR(20)  NOT NULL,
    email            VARCHAR(120) NOT NULL,
    cpf              VARCHAR(14)  NULL,
    data_nascimento  DATE         NULL,
    status           ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    observacoes      TEXT         NULL,
    criado_em        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_clientes_email (email),
    UNIQUE KEY uk_clientes_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Catálogo de serviços oferecidos
-- ------------------------------------------------------------
CREATE TABLE servicos (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome             VARCHAR(100) NOT NULL,
    duracao_minutos  INT UNSIGNED NOT NULL DEFAULT 30,
    preco            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status           ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Agendamentos (módulo principal) — FKs para clientes e serviços
-- ------------------------------------------------------------
CREATE TABLE agendamentos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id   INT UNSIGNED NOT NULL,
    servico_id   INT UNSIGNED NOT NULL,
    data_hora    DATETIME     NOT NULL,
    status       ENUM('agendado', 'confirmado', 'concluido', 'cancelado')
                 NOT NULL DEFAULT 'agendado',
    observacoes  VARCHAR(255) NULL,
    criado_em    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agendamentos_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_agendamentos_servico
        FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_agendamentos_data (data_hora),
    INDEX idx_agendamentos_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seeders — 5 registros em cada tabela
-- ============================================================

INSERT INTO clientes (nome, telefone, email, cpf, data_nascimento, status, observacoes) VALUES
('Ana Beatriz Souza',    '(11) 98811-1001', 'ana.souza@email.com',     '111.222.333-44', '1992-03-14', 'ativo',   'Prefere horários pela manhã.'),
('Carlos Eduardo Lima',  '(11) 97722-2002', 'carlos.lima@email.com',   '222.333.444-55', '1988-07-22', 'ativo',   'Alérgico a produtos com fragrância forte.'),
('Fernanda Alves Rocha', '(21) 99633-3003', 'fernanda.rocha@email.com','333.444.555-66', '1995-11-05', 'ativo',   NULL),
('João Pedro Martins',   '(11) 98544-4004', 'joao.martins@email.com',  '444.555.666-77', '1984-01-30', 'inativo', 'Mudou de cidade — manter cadastro.'),
('Marina Costa Oliveira','(31) 98455-5005', 'marina.oliveira@email.com','555.666.777-88','1998-09-18', 'ativo',   'Cliente VIP — desconto de fidelidade.');

INSERT INTO servicos (nome, duracao_minutos, preco, status) VALUES
('Corte e Escova',           60,  90.00,  'ativo'),
('Coloração Completa',       120, 220.00, 'ativo'),
('Manicure e Pedicure',      75,  70.00,  'ativo'),
('Limpeza de Pele',          50,  150.00, 'ativo'),
('Massagem Relaxante',       45,  130.00, 'inativo');

INSERT INTO agendamentos (cliente_id, servico_id, data_hora, status, observacoes) VALUES
(1, 1, '2026-10-22 09:00:00', 'confirmado', 'Primeira visita do mês.'),
(2, 4, '2026-10-22 11:00:00', 'agendado',   NULL),
(3, 2, '2026-10-23 14:30:00', 'agendado',   'Trazer referência da cor.'),
(5, 3, '2026-10-21 16:00:00', 'concluido',  'Atendimento realizado sem intercorrências.'),
(1, 4, '2026-10-20 10:00:00', 'cancelado',  'Cliente reagendou por conflito de horário.');
