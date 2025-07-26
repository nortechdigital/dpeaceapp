-- Table structure for table `api_management`
CREATE TABLE `api_management` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(100) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Inserting sample data into `api_management`
INSERT INTO `api_management` (`provider`, `api_key`, `endpoint`) VALUES
('Provider1', 'API_KEY_1', 'https://api.provider1.com/endpoint'),
('Provider2', 'API_KEY_2', 'https://api.provider2.com/endpoint'),
('Provider3', 'API_KEY_3', 'https://api.provider3.com/endpoint');