-- Table structure for table `api_balances`
CREATE TABLE `api_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(50) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Inserting sample balances
INSERT INTO `api_balances` (`provider`, `balance`, `updated_at`) VALUES
('smile', 5000.00, '2023-10-01 10:00:00'),
('glo', 3000.00, '2023-10-02 11:00:00'),
('9mobile', 4500.00, '2023-10-03 12:00:00'),
('airtel', 6000.00, '2023-10-04 13:00:00'),
('mtn', 7000.00, '2023-10-05 14:00:00');