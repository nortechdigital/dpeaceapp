-- Table structure for table `services`
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Inserting sample data into `services`
INSERT INTO `services` (`service_name`, `description`, `price`) VALUES
('Web Development', 'Building and maintaining websites', 1500.00),
('SEO Optimization', 'Improving the visibility of a website on search engines', 800.00),
('Graphic Design', 'Creating visual content to communicate messages', 500.00),
('Digital Marketing', 'Promoting products or brands via digital channels', 1200.00),
('Content Writing', 'Creating written content for websites and other media', 300.00);