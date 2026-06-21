-- ==========================================
-- ATUALIZAÇÕES DO BANCO DE DADOS (Caixa)
-- Rodar estes comandos para atualizar a estrutura antiga
-- ==========================================

USE `controle_caixa`;

-- 1. Criação da tabela de categorias customizadas
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Adição dos novos campos de banco e conta na tabela de transações
-- 2. Criacao da tabela de bancos customizados
CREATE TABLE IF NOT EXISTS `banks` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banks_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Adicao dos novos campos de banco e conta na tabela de transacoes
ALTER TABLE `transactions` 
  ADD COLUMN `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `payment_method`,
  ADD COLUMN `bank_account` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `bank_name`;
