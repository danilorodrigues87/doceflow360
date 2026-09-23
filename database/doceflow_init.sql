-- DoceFlow — schema inicial (multi-tenant via tenant_id = xd360.clientes_assinantes.id)
-- mysql -u root < database/doceflow_init.sql

CREATE DATABASE IF NOT EXISTS doceflow
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE doceflow;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS df_clientes (
  id CHAR(36) NOT NULL,
  tenant_id INT UNSIGNED NOT NULL,
  nome VARCHAR(255) NOT NULL DEFAULT '',
  whatsapp VARCHAR(40) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  endereco TEXT NULL,
  observacoes TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_df_clientes_tenant (tenant_id, atualizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS df_produtos (
  id CHAR(36) NOT NULL,
  tenant_id INT UNSIGNED NOT NULL,
  nome VARCHAR(255) NOT NULL DEFAULT '',
  categoria VARCHAR(120) NOT NULL DEFAULT '',
  preco_base DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  custo_estimado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  observacoes TEXT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_df_produtos_tenant (tenant_id, categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS df_encomendas (
  id CHAR(36) NOT NULL,
  tenant_id INT UNSIGNED NOT NULL,
  cliente_id CHAR(36) NOT NULL,
  produto_id CHAR(36) NOT NULL,
  tema VARCHAR(255) NOT NULL DEFAULT '',
  massa VARCHAR(255) NOT NULL DEFAULT '',
  recheio VARCHAR(255) NOT NULL DEFAULT '',
  tamanho_peso VARCHAR(120) NOT NULL DEFAULT '',
  quantidade INT NOT NULL DEFAULT 1,
  data_pedido DATE NULL,
  data_entrega DATE NULL,
  horario_entrega VARCHAR(10) NOT NULL DEFAULT '',
  valor_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  forma_pagamento VARCHAR(60) NOT NULL DEFAULT '',
  status VARCHAR(60) NOT NULL DEFAULT 'Orçamento',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_df_enc_tenant_entrega (tenant_id, data_entrega, status),
  KEY idx_df_enc_cliente (tenant_id, cliente_id),
  KEY idx_df_enc_produto (tenant_id, produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS df_estoque (
  id CHAR(36) NOT NULL,
  tenant_id INT UNSIGNED NOT NULL,
  ingrediente VARCHAR(255) NOT NULL DEFAULT '',
  quantidade DECIMAL(12,3) NOT NULL DEFAULT 0.000,
  unidade VARCHAR(20) NOT NULL DEFAULT '',
  estoque_minimo DECIMAL(12,3) NOT NULL DEFAULT 0.000,
  fornecedor VARCHAR(255) NOT NULL DEFAULT '',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_df_estoque_tenant (tenant_id, ingrediente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS df_tenant_settings (
  tenant_id INT UNSIGNED NOT NULL,
  theme VARCHAR(40) NOT NULL DEFAULT 'rosa',
  prefs_json JSON NULL,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
