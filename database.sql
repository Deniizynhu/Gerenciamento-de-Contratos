/* =========================================================
   BANCO DE DADOS DULTEC SOLUÇÕES
   ========================================================= */

DROP DATABASE IF EXISTS dultec_db;
CREATE DATABASE dultec_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dultec_db;

/* =========================================================
   TABELA: users
   Armazena todos os usuários do sistema:
   - Administradores
   - Gerentes de contrato
   - Clientes
   ========================================================= */

CREATE TABLE users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(100) NOT NULL,
    rg           VARCHAR(20),
    cpf_cnpj     VARCHAR(20),
    contato      VARCHAR(100),
    telefone     VARCHAR(20),
    celular      VARCHAR(20),

    email        VARCHAR(100) NOT NULL UNIQUE,
    senha        VARCHAR(255) NOT NULL,

    cep          VARCHAR(10),
    logradouro   VARCHAR(150),
    numero       VARCHAR(20),
    complemento  VARCHAR(100),
    bairro       VARCHAR(100),
    cidade       VARCHAR(100),
    estado       CHAR(2),

    nivel        ENUM('admin','gerente','cliente') NOT NULL DEFAULT 'cliente',
    situacao     ENUM('ativo','inativo')            NOT NULL DEFAULT 'ativo',

    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

/* =========================================================
   TABELA: contratos
   Armazena os contratos vinculados a clientes
   ========================================================= */

CREATE TABLE contratos (
    id                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id             INT UNSIGNED NOT NULL,

    titulo                 VARCHAR(150) NOT NULL,
    servicos               TEXT,                 -- Lista de serviços contratados (editável por contrato)
    descricao              TEXT,                 -- Observações/complementos

    valor                  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    multa_rescisao_percent TINYINT       NOT NULL DEFAULT 30, -- 30,40,50 (% do valor restante)

    status                 ENUM('ativo','pendente','encerrado') NOT NULL DEFAULT 'pendente',

    data_inicio            DATE,
    data_fim               DATE,

    created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_contratos_cliente
      FOREIGN KEY (cliente_id)
      REFERENCES users(id)
      ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

/* =========================================================
   TABELA: system_config
   Configurações globais do sistema (tema, nome, logo)
   ========================================================= */

CREATE TABLE system_config (
    id           INT UNSIGNED PRIMARY KEY,
    sistema_nome VARCHAR(100)                              DEFAULT 'Dultec Soluções',
    cor_tema     ENUM('azul','dark','verde') NOT NULL      DEFAULT 'azul',
    logo_path    VARCHAR(255)               NOT NULL       DEFAULT 'assets/logo.png'
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

/* =========================================================
   DADOS INICIAIS
   ========================================================= */

/* Usuário administrador padrão
   E-mail: admin@dultec.com.br
   Senha : password
   (recomenda-se trocar imediatamente usando o script de reset) */

INSERT INTO users (nome, email, senha, nivel, situacao)
VALUES (
    'Administrador Dultec',
    'admin@dultec.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha "password"
    'admin',
    'ativo'
);

/* Cliente exemplo (pode apagar depois) */
INSERT INTO users (nome, email, senha, nivel, situacao, cpf_cnpj, cidade, estado)
VALUES (
    'Cliente Teste Ltda',
    'cliente@empresa.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha "password"
    'cliente',
    'ativo',
    '00.000.000/0001-91',
    'São Paulo',
    'SP'
);

/* Configuração inicial do sistema */
INSERT INTO system_config (id, sistema_nome, cor_tema, logo_path)
VALUES (1, 'Dultec Soluções', 'azul', 'assets/logo.png');