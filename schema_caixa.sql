
-- Criação do banco de dados pra vcs copiarem no phpmyadmin

CREATE DATABASE IF NOT EXISTS `controle_caixa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `controle_caixa`;


CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','operador') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operador' COMMENT 'Nível de acesso do usuário',
  `status` enum('ativo','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo' COMMENT 'Ativo = pode logar; Bloqueado = acesso negado',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `cash_registers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID do operador que abriu o caixa',
  `status` enum('aberto','fechado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `opening_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo em dinheiro na hora de abrir a gaveta',
  `closing_balance` decimal(10,2) NULL DEFAULT NULL COMMENT 'Saldo contado e validado na hora de fechar',
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cash_registers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cash_register_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Referência ao turno do caixa logado',
  `type` enum('entrada','saida') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Define se o dinheiro entrou ou saiu',
  `amount` decimal(10,2) NOT NULL COMMENT 'Valor da transação',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Motivo / Descrição do lançamento',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NULL COMMENT 'Pix, Dinheiro, Cartão Crédito, etc',
  `receipt_path` varchar(500) COLLATE utf8mb4_unicode_ci NULL COMMENT 'Caminho do comprovante/recibo salvo no storage',
  `competencia_date` date NULL COMMENT 'Data de competência (pode diferir da data de registro)',
  `notes` text COLLATE utf8mb4_unicode_ci NULL COMMENT 'Observações complementares',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `transactions_cash_register_id_foreign` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- opcional: se quiserem a inserção de um usuário Admin padrão
-- a senha de teste padrão é 'password', mas no Laravel é gravada como hash.

INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
('Administrador', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
