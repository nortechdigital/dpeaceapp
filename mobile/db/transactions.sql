-- Table structure for table `transactions`
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `type` varchar(50) NOT NULL,
  `product_description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Inserting sample transactions
INSERT INTO `transactions` (`user_id`, `username`, `phone_number`, `type`, `product_description`, `amount`, `status`, `created_at`) VALUES
(1, 'Kabiru Adamu', '08065615684', 'wallet funding', 'Funding wallet with 100.00', 100.00, 'successful', '2023-10-01 10:00:00'),
(2, 'Imonitie Aregbeyen', '09061680055', 'airtime purchase', 'Purchasing airtime for 50.00', 50.00, 'failed', '2023-10-02 11:00:00'),
(3, 'Nortech Digital', '08065615684', 'data subscription', 'Subscribing to data plan for 75.50', 75.50, 'successful', '2023-10-03 12:00:00'),
(1, 'Kabiru Adamu', '08065615684', 'electricity', 'Paying electricity bill for 200.00', 200.00, 'successful', '2023-10-04 13:00:00'),
(2, 'Imonitie Aregbeyen', '09061680055', 'wallet funding', 'Funding wallet with 150.00', 150.00, 'failed', '2023-10-05 14:00:00'),
(3, 'Nortech Digital', '08065615684', 'airtime purchase', 'Purchasing airtime for 300.00', 300.00, 'successful', '2023-10-06 15:00:00'),
(1, 'Kabiru Adamu', '08065615684', 'data subscription', 'Subscribing to data plan for 120.00', 120.00, 'successful', '2023-10-07 16:00:00'),
(2, 'Imonitie Aregbeyen', '09061680055', 'electricity', 'Paying electricity bill for 180.00', 180.00, 'failed', '2023-10-08 17:00:00'),
(3, 'Nortech Digital', '08065615684', 'wallet funding', 'Funding wallet with 250.00', 250.00, 'successful', '2023-10-09 18:00:00'),
(1, 'Kabiru Adamu', '08065615684', 'airtime purchase', 'Purchasing airtime for 90.00', 90.00, 'successful', '2023-10-10 19:00:00'),
(2, 'Imonitie Aregbeyen', '09061680055', 'wallet funding', 'Funding wallet with 200.00', 200.00, 'successful', '2023-10-11 20:00:00'),
(3, 'Nortech Digital', '08065615684', 'data subscription', 'Subscribing to data plan for 150.00', 150.00, 'successful', '2023-10-12 21:00:00'),
(1, 'Kabiru Adamu', '08065615684', 'electricity', 'Paying electricity bill for 220.00', 220.00, 'successful', '2023-10-13 22:00:00'),
(2, 'Imonitie Aregbeyen', '09061680055', 'airtime purchase', 'Purchasing airtime for 110.00', 110.00, 'failed', '2023-10-14 23:00:00'),
(3, 'Nortech Digital', '08065615684', 'wallet funding', 'Funding wallet with 300.00', 300.00, 'successful', '2023-10-15 09:00:00');