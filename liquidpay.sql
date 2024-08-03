-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03/08/2024 às 22:38
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `liquidpay`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_histories`
--

CREATE TABLE `password_histories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `password_histories`
--

INSERT INTO `password_histories` (`id`, `user_id`, `password_hash`, `changed_at`) VALUES
(1, 1, '$2y$10$/R77qZZ.sS96XITR38p6bOTwudPbfvk6a4hPQakUM92VJGf0t69SK', '2024-08-03 01:29:30'),
(2, 1, '$2y$10$/R77qZZ.sS96XITR38p6bOTwudPbfvk6a4hPQakUM92VJGf0t69SK', '2024-08-03 01:50:32'),
(3, 1, '$2y$10$Y9oqKt8/nqcErHdw5Scg7uvzpdR5SpFIbr7ZarScKWzXbpEsdVOq.', '2024-08-03 01:51:13'),
(4, 1, '$2y$10$3nrr3F8bMLsFdPMu6nSrMOuqJdlrJuLh3O4fu.oq.jw1wS.sOkZIW', '2024-08-03 01:51:22'),
(5, 1, '$2y$10$L0gmBa8GrqEyVKCljCAabOmlOTFYhUCbUgmNtVItQr13s4tQppZya', '2024-08-03 13:15:36'),
(6, 1, '$2y$10$m6vXkBO3wnCU5vL3h0IpeeLy4Hzb.usoaJ9PNrU8/qhAWJcmhCYNe', '2024-08-03 20:00:37');

-- --------------------------------------------------------

--
-- Estrutura para tabela `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `typeCard` enum('credit','debit') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `status`, `transaction_id`, `created_at`, `typeCard`) VALUES
(1, 1, 100.00, 'approved', '6cdecd6e-7451-4a90-aaee-38fe9ff5ba33', '2024-08-02 00:23:34', 'debit'),
(2, 1, 100.00, '1', '4671056a-d588-4966-9347-39aa053e7201', '2024-08-02 22:56:31', 'credit'),
(3, 1, 100.00, '1', '00efb530-8e55-4ef2-8c31-d3ee9b79a2a0', '2024-08-02 23:01:03', 'credit'),
(4, 1, 100.00, '1', '9416d6e1-89af-4671-8bd5-1ca8fe4f71ab', '2024-08-02 23:03:13', 'credit'),
(5, 1, 100.00, '1', 'e6c6b987-2edd-49a0-8781-d4ca3afb0aba', '2024-08-02 23:04:24', 'credit'),
(6, 1, 100.00, '1', 'e96fcb2f-b135-48a8-b0cb-47fa9d295895', '2024-08-02 23:07:30', 'credit'),
(7, 1, 100.00, '1', 'eaca9070-f92c-4125-b621-f380c51b3998', '2024-08-02 23:10:21', 'credit'),
(8, 1, 100.00, '1', '36bc2afb-75b0-4c9e-8fd9-670141b7d287', '2024-08-02 23:10:37', 'credit'),
(9, 1, 100.00, '1', '250f9a05-3c55-4042-bd1c-31fee5fcfae7', '2024-08-02 23:11:47', 'credit'),
(10, 1, 100.00, '1', 'ce9669ad-797e-4e98-8b71-e0688179b4a7', '2024-08-02 23:13:01', 'credit'),
(11, 1, 100.00, '1', '7f6343e5-92e2-4ef6-93a5-54bf1118f0c3', '2024-08-02 23:18:58', 'credit'),
(12, 1, 100.00, '1', '642151d9-167d-4492-9dfb-7c696209d724', '2024-08-03 20:02:16', 'credit');

-- --------------------------------------------------------

--
-- Estrutura para tabela `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `transfers`
--

INSERT INTO `transfers` (`id`, `sender_id`, `receiver_id`, `amount`, `transferred_at`) VALUES
(1, 1, 2, 2000.00, '2024-08-03 19:45:47'),
(2, 1, 2, 100.00, '2024-08-03 20:02:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `name`, `cpf`, `email`, `password`, `balance`) VALUES
(1, 'Leonardo', '00949196071', 'leonardolino@gmail.com', '$2y$10$nqGX1HaaSqSeizmYgC2ThODb8o4ch6bJnv3Kld1Lh6/2VoZEnuiVW', 200.00),
(2, 'Martins', '00949196072', 'martinso@gmail.com', '$2y$10$zljBBSSk0gEXJrotgV7Vwu4f91.a6wClZph9/1n0Ut15reW4NW7ZO', 2100.00),
(3, 'Giovana', '00949196073', 'giovana@gmail.com', '$2y$10$8KkOPQBGW9jl1GQtZjBS8ulwLnr2J7/fq4JzH//ycl7ZSMfgj/PGa', 0.00);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `password_histories`
--
ALTER TABLE `password_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `password_histories`
--
ALTER TABLE `password_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `password_histories`
--
ALTER TABLE `password_histories`
  ADD CONSTRAINT `password_histories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
