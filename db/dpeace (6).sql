-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 10:59 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dpeace`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_balances`
--

CREATE TABLE `api_balances` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `api_balances`
--

INSERT INTO `api_balances` (`id`, `provider`, `balance`, `updated_at`) VALUES
(1, 'smile', '0.00', '2025-03-01 04:00:21'),
(2, 'glo', '0.00', '2025-03-01 04:00:28'),
(3, '9mobile', '0.00', '2025-03-01 04:00:33'),
(4, 'airtel', '0.00', '2025-03-01 04:00:38'),
(5, 'mtn', '0.00', '2025-03-01 04:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `api_management`
--

CREATE TABLE `api_management` (
  `id` int(11) NOT NULL,
  `provider` varchar(100) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `api_management`
--

INSERT INTO `api_management` (`id`, `provider`, `api_key`, `endpoint`, `created_at`) VALUES
(1, 'VAS2NETS', ' ', 'https://b2bapi.v2napi.com/dev/meta/', '2025-02-11 08:42:28'),
(4, 'Smile Communications', ' ', 'https://smile.com.ng/TPGW/ThirdPartyGateway?wsdl', '2025-02-13 08:59:58'),
(5, 'SWIFT Network', ' ', 'http://swiftng.com:3000/', '2025-03-04 10:42:54'),
(6, 'Glo ', ' ', ' ', '2025-03-19 18:59:45'),
(7, 'AIRTEL', ' ', ' ', '2025-03-19 19:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `data_plans`
--

CREATE TABLE `data_plans` (
  `id` int(11) NOT NULL,
  `plan_id` int(5) DEFAULT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `plan_name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `validity` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_plans`
--

INSERT INTO `data_plans` (`id`, `plan_id`, `provider`, `plan_name`, `price`, `validity`) VALUES
(1, 116, 'MTN', 'MTN -SME 3GB', '1950.00', '30 Days'),
(2, 113, 'MTN', 'MTN - SME 500MB', '335.00', '30 Days'),
(3, 114, 'MTN', 'MTN - SME 1GB', '650.00', '30 Days'),
(4, 160, 'MTN', 'MTN - SME 10GB', '6500.00', '30 Days'),
(5, 174, 'MTN', 'MTN - GIFTING 1GB +3mnt', '400.00', '1 Days'),
(6, 177, 'MTN', 'MTN - GIFTING 7GB', '3150.00', '7 Days'),
(7, 178, 'MTN', 'MTN - GIFTING 75GB', '26250.00', '30 Days'),
(8, 175, 'MTN', 'MTN - GIFTING 3.2GB', '1250.00', '2 Days'),
(9, 179, 'MTN', 'MTN - CG (10GB)', '4300.00', '30 Days'),
(10, 181, 'MTN', 'MTN - CG (15GB)', '6400.00', '30 Days'),
(11, 176, 'MTN', 'MTN - GIFTING 5GB', '1900.00', '7 Days'),
(12, 115, 'MTN', 'MTN - SME 2GB', '1300.00', '30 Days'),
(13, 140, 'GLO', 'GLO CG 500MB', '175.00', '30 Days'),
(14, 158, 'GLO', 'GLO CG 3.072GB', '1050.00', '30 Days'),
(15, 157, 'GLO', 'GLO CG 2GB', '700.00', '30 Days'),
(16, 124, 'GLO', 'GLO CG 200MB', '80.00', '30 Days'),
(17, 164, 'GLO', 'GLO CG 10.2 GB', '3500.00', '30 Days'),
(18, 141, 'GLO', 'GLO CG 1.024GB', '350.00', '30 Days'),
(19, 159, 'GLO', 'GLO CG 5.12GB', '1750.00', '30 Days'),
(20, 184, 'Airtel', 'Airtel GIFTING 600MB', '255.00', '2 days'),
(21, 183, 'Airtel', 'Airtel GIFTING 500MB', '160.00', '14 days'),
(22, 186, 'Airtel', 'Airtel GIFTING 5.0GB', '1400.00', '14 days'),
(23, 190, 'Airtel', 'Airtel GIFTING 300MB', '150.00', '1 days'),
(24, 188, 'Airtel', 'Airtel GIFTING 20.0GB', '5600.00', '30 days'),
(25, 185, 'Airtel', 'Airtel GIFTING 2.0GB', '560.00', '2 days'),
(26, 189, 'Airtel', 'Airtel GIFTING 100MB', '80.00', '1 days'),
(27, 187, 'Airtel', 'Airtel GIFTING 10.0GB', '2800.00', '30 days'),
(28, 162, 'Airtel', 'AIRTEL CG 5GB', '3000.00', '30 days'),
(29, 121, 'Airtel', 'AIRTEL CG 500MB', '360.00', '30 days'),
(30, 119, 'Airtel', 'AIRTEL CG 300MB', '215.00', '30 days'),
(31, 153, 'Airtel', 'AIRTEL CG 2GB', '1400.00', '30 days'),
(32, 122, 'Airtel', 'AIRTEL CG 1GB', '700.00', '30 days'),
(33, 172, 'Airtel', 'AIRTEL CG 10GB', '7000.00', '30 days'),
(34, 191, 'Airtel', 'AIRTEL CG 100MB', '90.00', '30 Days'),
(35, 134, '9MOBILE', '9MOBILE 5GB', '950.00', '30 Days'),
(36, 129, '9MOBILE', '9MOBILE 500MB', '96.00', '30 Days'),
(37, 133, '9MOBILE', '9MOBILE 3GB', '570.00', '30 Days'),
(38, 132, '9MOBILE', '9MOBILE 2GB', '380.00', '30 Days'),
(39, 130, '9MOBILE', '9MOBILE 1GB', '190.00', '30 Days'),
(40, 161, '9MOBILE', '9MOBILE 10GB', '1600.00', '30 Days'),
(41, 131, '9MOBILE', '9MOBILE 1.5GB', '285.00', '30 Days');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'John Doe', 'johndoe@example.com', '123-456-7890', 'Inquiry about Product X', 'I am interested in purchasing Product X. Could you provide more information on pricing and availability?', '2025-02-11 09:41:49'),
(2, 'Jane Smith', 'janesmith@example.com', '987-654-3210', 'Feedback on Service Y', 'The service I received was excellent, but I have some suggestions for improvement.', '2025-02-11 09:41:49');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `name`, `logo`, `description`) VALUES
(1, '9mobile', '../uploads/9moble_logo.png', '9mobile airtime and data subscription plans.'),
(2, 'AIRTEL', '../uploads/airtel_logo.png', 'Airtel airtime and data subscription plans.'),
(3, 'Glo', '../uploads/glo_logo.jpg', 'Glo airtime and data subscription plans.'),
(4, 'MTN', '../uploads/mtn_logo.png', 'MTN airtime and data subscription plans.'),
(5, 'Smile', '../uploads/smile_logo.jpg', 'Smile voice and data bundle.'),
(6, 'SWIFT', '../uploads/swift_logo.jpg', 'Swift voice and data bundle');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reply` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `description`, `price`, `created_at`) VALUES
(1, 'Airtime Topup', 'Virtual airtime topup for major telecom operators in Nigeria', '0.00', '2025-02-11 08:50:15'),
(2, 'Data Subscription', 'Data subscription for major telecom operators in Nigeria', '0.00', '2025-02-11 08:50:15'),
(3, 'Utility Bil Payment', 'Electricity Post-Paid bill payment and Prepaid meter subscription', '0.00', '2025-02-11 08:50:15'),
(6, 'Cable TV Subscription', 'Cable TV subscription for DStv, GOtv and Startimes and others', '0.00', '2025-02-11 09:04:53');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `buyingPriceMTNData` decimal(10,2) DEFAULT NULL,
  `sellingPriceMTNData` decimal(10,2) DEFAULT NULL,
  `profitMTNData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountMTNData` decimal(5,2) DEFAULT NULL,
  `agentDiscountMTNData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountMTNData` decimal(5,2) DEFAULT NULL,
  `buyingPriceMTNAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceMTNAirtime` decimal(10,2) DEFAULT NULL,
  `profitMTNAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountMTNAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountMTNAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountMTNAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPriceAirtelData` decimal(10,2) DEFAULT NULL,
  `sellingPriceAirtelData` decimal(10,2) DEFAULT NULL,
  `profitAirtelData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountAirtelData` decimal(5,2) DEFAULT NULL,
  `agentDiscountAirtelData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountAirtelData` decimal(5,2) DEFAULT NULL,
  `buyingPriceAirtelAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceAirtelAirtime` decimal(10,2) DEFAULT NULL,
  `profitAirtelAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountAirtelAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountAirtelAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountAirtelAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPriceGloData` decimal(10,2) DEFAULT NULL,
  `sellingPriceGloData` decimal(10,2) DEFAULT NULL,
  `profitGloData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountGloData` decimal(5,2) DEFAULT NULL,
  `agentDiscountGloData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountGloData` decimal(5,2) DEFAULT NULL,
  `buyingPriceGloAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceGloAirtime` decimal(10,2) DEFAULT NULL,
  `profitGloAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountGloAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountGloAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountGloAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPrice9mobileData` decimal(10,2) DEFAULT NULL,
  `sellingPrice9mobileData` decimal(10,2) DEFAULT NULL,
  `profit9mobileData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscount9mobileData` decimal(5,2) DEFAULT NULL,
  `agentDiscount9mobileData` decimal(5,2) DEFAULT NULL,
  `vendorDiscount9mobileData` decimal(5,2) DEFAULT NULL,
  `buyingPrice9mobileAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPrice9mobileAirtime` decimal(10,2) DEFAULT NULL,
  `profit9mobileAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscount9mobileAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscount9mobileAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscount9mobileAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPriceSmileData` decimal(10,2) DEFAULT NULL,
  `sellingPriceSmileData` decimal(10,2) DEFAULT NULL,
  `profitSmileData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSmileData` decimal(5,2) DEFAULT NULL,
  `agentDiscountSmileData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSmileData` decimal(5,2) DEFAULT NULL,
  `buyingPriceSmileAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceSmileAirtime` decimal(10,2) DEFAULT NULL,
  `profitSmileAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSmileAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountSmileAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSmileAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPriceSpectranetData` decimal(10,2) DEFAULT NULL,
  `sellingPriceSpectranetData` decimal(10,2) DEFAULT NULL,
  `profitSpectranetData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSpectranetData` decimal(5,2) DEFAULT NULL,
  `agentDiscountSpectranetData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSpectranetData` decimal(5,2) DEFAULT NULL,
  `buyingPriceSpectranetAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceSpectranetAirtime` decimal(10,2) DEFAULT NULL,
  `profitSpectranetAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSpectranetAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountSpectranetAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSpectranetAirtime` decimal(5,2) DEFAULT NULL,
  `buyingPriceSwiftData` decimal(10,2) DEFAULT NULL,
  `sellingPriceSwiftData` decimal(10,2) DEFAULT NULL,
  `profitSwiftData` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSwiftData` decimal(5,2) DEFAULT NULL,
  `agentDiscountSwiftData` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSwiftData` decimal(5,2) DEFAULT NULL,
  `buyingPriceSwiftAirtime` decimal(10,2) DEFAULT NULL,
  `sellingPriceSwiftAirtime` decimal(10,2) DEFAULT NULL,
  `profitSwiftAirtime` decimal(10,2) DEFAULT NULL,
  `subscriberDiscountSwiftAirtime` decimal(5,2) DEFAULT NULL,
  `agentDiscountSwiftAirtime` decimal(5,2) DEFAULT NULL,
  `vendorDiscountSwiftAirtime` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `buyingPriceMTNData`, `sellingPriceMTNData`, `profitMTNData`, `subscriberDiscountMTNData`, `agentDiscountMTNData`, `vendorDiscountMTNData`, `buyingPriceMTNAirtime`, `sellingPriceMTNAirtime`, `profitMTNAirtime`, `subscriberDiscountMTNAirtime`, `agentDiscountMTNAirtime`, `vendorDiscountMTNAirtime`, `buyingPriceAirtelData`, `sellingPriceAirtelData`, `profitAirtelData`, `subscriberDiscountAirtelData`, `agentDiscountAirtelData`, `vendorDiscountAirtelData`, `buyingPriceAirtelAirtime`, `sellingPriceAirtelAirtime`, `profitAirtelAirtime`, `subscriberDiscountAirtelAirtime`, `agentDiscountAirtelAirtime`, `vendorDiscountAirtelAirtime`, `buyingPriceGloData`, `sellingPriceGloData`, `profitGloData`, `subscriberDiscountGloData`, `agentDiscountGloData`, `vendorDiscountGloData`, `buyingPriceGloAirtime`, `sellingPriceGloAirtime`, `profitGloAirtime`, `subscriberDiscountGloAirtime`, `agentDiscountGloAirtime`, `vendorDiscountGloAirtime`, `buyingPrice9mobileData`, `sellingPrice9mobileData`, `profit9mobileData`, `subscriberDiscount9mobileData`, `agentDiscount9mobileData`, `vendorDiscount9mobileData`, `buyingPrice9mobileAirtime`, `sellingPrice9mobileAirtime`, `profit9mobileAirtime`, `subscriberDiscount9mobileAirtime`, `agentDiscount9mobileAirtime`, `vendorDiscount9mobileAirtime`, `buyingPriceSmileData`, `sellingPriceSmileData`, `profitSmileData`, `subscriberDiscountSmileData`, `agentDiscountSmileData`, `vendorDiscountSmileData`, `buyingPriceSmileAirtime`, `sellingPriceSmileAirtime`, `profitSmileAirtime`, `subscriberDiscountSmileAirtime`, `agentDiscountSmileAirtime`, `vendorDiscountSmileAirtime`, `buyingPriceSpectranetData`, `sellingPriceSpectranetData`, `profitSpectranetData`, `subscriberDiscountSpectranetData`, `agentDiscountSpectranetData`, `vendorDiscountSpectranetData`, `buyingPriceSpectranetAirtime`, `sellingPriceSpectranetAirtime`, `profitSpectranetAirtime`, `subscriberDiscountSpectranetAirtime`, `agentDiscountSpectranetAirtime`, `vendorDiscountSpectranetAirtime`, `buyingPriceSwiftData`, `sellingPriceSwiftData`, `profitSwiftData`, `subscriberDiscountSwiftData`, `agentDiscountSwiftData`, `vendorDiscountSwiftData`, `buyingPriceSwiftAirtime`, `sellingPriceSwiftAirtime`, `profitSwiftAirtime`, `subscriberDiscountSwiftAirtime`, `agentDiscountSwiftAirtime`, `vendorDiscountSwiftAirtime`) VALUES
(1, '250.00', '300.00', '50.00', '2.00', '3.00', '0.00', '90.00', '100.00', '10.00', '1.00', '2.00', '3.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `swift_data_plans`
--

CREATE TABLE `swift_data_plans` (
  `id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `roll_over` varchar(20) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `validity` varchar(20) NOT NULL,
  `time_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swift_data_plans`
--

INSERT INTO `swift_data_plans` (`id`, `plan_name`, `roll_over`, `price`, `validity`, `time_details`) VALUES
(1, 'SWIFT LIBERTY', '2GB', 1800, '30days', '24hrs daily'),
(2, 'SWIFT LIBERTY PLUS', '2.5GB', 2400, '30days', '24hrs daily'),
(3, 'SWIFT BASIC WEEKLY', '7GB', 2850, '7 days', '24hrs daily'),
(4, 'SWIFT BASIC MINI', '4GB', 2850, '30days', '24hrs daily'),
(5, 'SWIFT BASIC', '6GB', 3900, '30days', '24hrs daily'),
(6, 'SWIFT CLASSIC', '8GB', 4650, '30days', '24hrs daily'),
(7, 'SWIFT MERIT', '10GB', 9150, '14 days', '24hrs plus Free Night (1am – 7am)'),
(8, 'SWIFT UNLIMITED WHATSAPP', '12GB', 6750, '30 days', '24hrs plus Unlimited and free Whats App Access'),
(9, 'SWIFT MINI PLUS', '12GB', 7200, '30days', '24hrs daily'),
(10, 'SWIFT ESSENTIAL MINI', '20GB', 10950, '30days', '24hrs daily'),
(11, 'SWIFT ESSENTIAL', '37GB', 15900, '30days', '24hrs daily'),
(12, 'SWIFT ESSENTIAL PLUS', '27GB', 21900, '30days', '24hrs daily plus ***Free Night'),
(13, 'SWIFT EVENING', '40GB', 14100, '30days', '5pm – 8am (Mon – Fri) ; 24hrs on weekends'),
(14, 'SWIFT EVENING PRO', '18GB', 15000, '30days', '24hrs daily plus free night browsing (12am- 6am daily)'),
(15, 'SWIFT EVENING PLUS', '68GB', 22050, '30days', '5pm – 9am (Mon – Fri) ; 24hrs on weekends'),
(16, 'SWIFT WEEKEND LITE', '37GB', 16650, '30days', '24hrs daily plus ***(Free Sunday Browsing)'),
(17, 'SWIFT PROFESSIONAL', '45GB', 18750, '30days', '24hrs daily'),
(18, 'SWIFT CLUB', '55GB', 22350, '30days', '24hrs daily'),
(19, 'SWIFT CLUB PLUS', '40GB', 23400, '30days', '24hrs daily plus ***Free Night'),
(20, 'SWIFT WEEKEND MAX', '55GB', 24000, '30days', '24hrs daily plus ***Free Weekend (Saturday & Sunday)'),
(21, 'SWIFT BUSINESS', '60GB', 24300, '30days', '7am – 7pm daily'),
(22, 'SWIFT PREMIUM', '90GB', 32700, '30days', '24hrs daily'),
(23, 'SWIFT NIGHT LITE', '60GB', 34200, '30days', '24hrs daily plus ***Free Night'),
(24, 'SWIFT MAX LITE', '120GB', 40920, '30days', '24hrs daily'),
(25, 'SWIFT MAX PLUS', '150GB', 49335, '30days', '24hrs daily'),
(26, 'SWIFT NIGHT MAX', '100GB', 49800, '30days', '24hrs daily plus ***Free Night'),
(27, 'SWIFT MAX PRO', '200GB', 55770, '30days', '24hrs daily'),
(28, 'SWIFT LEISURE PLUS', '100GB', 41850, '30days', '24hrs daily plus ***Free Night and public holiday'),
(29, 'SWIFT LEISURE PRO', '150GB', 45450, '30days', '24hrs daily plus ***Free Night and public holiday'),
(30, 'SWIFT UNLIMITED WEEKLY', 'UNLIMITED- 5MBPS', 11850, '7 days', '24hrs daily'),
(31, 'SWIFT UNLIMITED', 'UNLIMITED- 5MBPS', 45750, '30days', '24hrs daily'),
(32, 'SWIFT QUARTERLY DATA PLAN', '200GB', 58200, '90 days', '24hrs daily'),
(33, 'SWIFT QUARTERLY UNLIMITEDPLAN', 'UNLIMITED- 5MBPS', 136200, '105 days', '24hrs daily'),
(34, 'SWIFT ANNUAL PLAN', '1 TERABYTE', 217800, '365 days', '24hrs daily');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `transaction_ref` varchar(50) NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `product_description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `profit` varchar(10) NOT NULL,
  `type` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `fullname`, `phone_number`, `transaction_ref`, `request_id`, `product_description`, `amount`, `status`, `profit`, `type`, `created_at`) VALUES
(1, 2, '', '', '', '', 'Purchased  airtime for 08065615684', '1000.00', 'failed', '', 'Airtime Purchase', '2025-03-04 15:33:18'),
(2, 2, '', '', '', '', 'Purchased airtime for 08065615684', '0.00', 'failed', '', '', '2025-03-04 15:34:25'),
(3, 2, '', '', '', '', 'Purchased Glo data for 09061680055', '0.00', 'failed', '', '', '2025-03-04 15:47:51'),
(4, 2, '', '', '', '', 'Purchased  airtime for 08067627256', '1000.00', 'failed', '', '', '2025-03-05 10:01:42'),
(5, 2, '', '', '', '', 'Purchased  airtime for 09061680055', '50.00', 'failed', '', '', '2025-03-07 14:17:53'),
(6, 2, 'Kabiru Adamu', '08065615684', '', '', 'Purchased  airtime for 09061680055', '50.00', 'failed', '', '', '2025-03-07 14:32:32'),
(7, 2, 'Kabiru Adamu', '08065615684', '', '', 'Purchased MTN airtime for 09061680055', '50.00', 'failed', '', '', '2025-03-07 14:35:55'),
(8, 2, 'Kabiru Adamu', '08065615684', '', '', 'Airtime purchase for 08065615684', '1000.00', 'Success', '', 'Airtime Purchase', '2025-03-24 13:31:20'),
(9, 2, 'Kabiru Adamu', '07069063901', '', '', 'Airtime purchase for 07069063901', '500.00', 'Success', '', 'Airtime Purchase', '2025-03-24 13:34:24'),
(10, 2, 'Kabiru Adamu', '08065615684', '', '', 'Airtime purchase for 08065615684', '2500.00', 'Success', '', 'Airtime Purchase', '2025-03-26 18:12:20'),
(11, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtime purchase for 09044572815', '500.00', 'Success', '', 'Airtime Purchase', '2025-03-27 16:16:23'),
(12, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtime purchase for 09044572815', '1000.00', 'Success', '', 'Airtime Purchase', '2025-03-28 07:37:03'),
(13, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtime purchase for 09044572815', '1000.00', 'Success', '', 'Airtime Purchase', '2025-03-28 07:37:29'),
(14, 2, 'Kabiru Adamu', '08065615684', '', '', 'Airtime purchase for 08065615684', '500.00', 'Success', '', 'Airtime Purchase', '2025-03-28 07:41:36'),
(15, 2, 'Kabiru Adamu', '09044572815', '', '', ' purchase for 09044572815', '1000.00', 'Success', '', 'Data Purchase', '2025-03-28 07:46:27'),
(16, 2, 'Kabiru Adamu', '09044572815', '', '', ' purchase for 09044572815', '1000.00', 'Success', '', 'Data Purchase', '2025-03-28 07:48:17'),
(17, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-AIRTIME purchase for 08065615684', '500.00', 'Success', '', 'Airtime Purchase', '2025-03-28 07:51:27'),
(18, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '15000.00', 'Success', '', 'Electricity Subscription', '2025-03-28 08:03:34'),
(19, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '150000.00', 'Success', '', 'Cable TV Subscription', '2025-03-28 08:06:58'),
(20, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '100.00', 'Success', '', 'Data Purchase', '2025-03-28 08:10:54'),
(21, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '10000.00', 'Success', '', 'Cable TV Subscription', '2025-03-28 09:59:53'),
(22, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-DATA purchase for 08065615684', '11000.00', 'Success', '', 'Data Purchase', '2025-03-28 10:02:02'),
(23, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-AIRTIME purchase for 08065615684', '1000.00', 'Success', '', 'Airtime Purchase', '2025-03-28 16:10:47'),
(24, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-DATA purchase for 08065615684', '2500.00', 'Success', '', 'Data Purchase', '2025-03-28 16:13:37'),
(25, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-DATA purchase for 08065615684', '1000.00', 'Success', '', 'Data Purchase', '2025-04-04 17:31:15'),
(26, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '100.00', 'Success', '', 'Data Purchase', '2025-04-05 09:10:21'),
(27, 2, 'Kabiru Adamu', '08065615684', '', '', 'MTN-AIRTIME purchase for 08065615684', '1000.00', 'Success', '', 'Airtime Purchase', '2025-04-05 10:06:49'),
(28, 2, 'Kabiru Adamu', '09044572815', '', '', 'Airtel-DATA purchase for 09044572815', '100.00', 'Success', '', 'Data Purchase', '2025-04-05 10:30:53'),
(29, 2, 'Kabiru Adamu', '08098482929', '', '', 'MTN-DATA purchase for 08098482929', '1000.00', 'Success', '', 'Data Purchase', '2025-04-05 10:31:38'),
(30, 2, 'Kabiru Adamu', '09051512037', '', '', 'GLO-DATA purchase for 09051512037', '100.00', 'Success', '', 'Data Purchase', '2025-04-05 10:37:08'),
(33, 2, 'Kabiru Adamu', '08065615684', '1745442461865714021', '', 'Airtime purchase for 08065615684', '200.00', 'Failed', '', '', '2025-04-23 21:06:53'),
(34, 2, 'Kabiru Adamu', '09051512037', '680959021170d', '', '', '196.00', 'failed', '', 'Airtime Purchase', '2025-04-23 21:17:55'),
(35, 2, 'Kabiru Adamu', '09044572815', '68095916ed7e7', '', '', '490.00', 'failed', '', 'Airtime Purchase', '2025-04-23 21:18:15'),
(36, 2, 'Kabiru Adamu', '09051512037', '1745451955994128827', '', 'Airtime purchase for 09051512037', '490.00', 'success', '', '', '2025-04-23 23:45:08'),
(37, 2, 'Kabiru Adamu', '08065615684', '1745452299761427675', '1745452299761427675', 'Purchase of MTN-AIRTIME for 08065615684', '197.00', '404', '', 'Airtime Purchase', '2025-04-23 23:50:51'),
(38, 2, 'Kabiru Adamu', '09051512037', '1745452370108512571', '', 'Purchase of GLO-AIRTIME for 09051512037', '490.00', 'failed', '', 'Airtime Purchase', '2025-04-23 23:52:01'),
(39, 2, 'Kabiru Adamu', '08031807325', '1745452526702380355', '', 'Purchase of MTN-AIRTIME for 08031807325', '492.50', 'success', '', 'Airtime Purchase', '2025-04-23 23:54:38'),
(40, 2, 'Kabiru Adamu', '08031807325', '1745457343042342482', '', 'Purchase of MTN-DATA for 08031807325', '196.00', 'failed', '', 'Data Purchase', '2025-04-24 01:14:54'),
(41, 2, 'Kabiru Adamu', '08031807325', '1745457469501141112', '', 'Purchase of MTN-DATA for 08031807325', '1960.00', 'success', '', 'Data Purchase', '2025-04-24 01:17:04'),
(42, 2, 'Kabiru Adamu', '09051512037', '1745457900109161696', '', 'Purchase of GLO-DATA for 09051512037', '1960.00', 'success', '', 'Data Purchase', '2025-04-24 01:24:13'),
(43, 2, '', '04280379696', '1745458723844733398303', '', 'Electricity payment for 04280379696', '997.00', 'success', '', 'Electricity Subscription', '2025-04-24 01:38:01'),
(44, 2, '', '04280379696', 'pay_1745461631_269cb611', '', 'AEDCA', '1000.00', '', '', 'Electricity Subscription', '2025-04-24 02:27:20'),
(45, 2, 'Kabiru Adamu', '09051512037', '1745573507740422283', '', 'Purchase of GLO-AIRTIME for 09051512037', '980.00', 'failed', '', 'Airtime Purchase', '2025-04-25 09:30:57'),
(46, 2, 'Kabiru Adamu', '08065615684', '1745573927458223307', '', 'Purchase of MTN-AIRTIME for 08065615684', '985.00', 'success', '', 'Airtime Purchase', '2025-04-25 09:37:57'),
(47, 2, 'Kabiru Adamu', '08065615684', '1745574055131670337', '', 'Purchase of MTN-AIRTIME for 08065615684', '985.00', 'failed', '', 'Airtime Purchase', '2025-04-25 09:40:04'),
(48, 2, 'Kabiru Adamu', '08065615684', '1745574057803429873', '', 'Purchase of MTN-AIRTIME for 08065615684', '985.00', 'failed', '', 'Airtime Purchase', '2025-04-25 09:40:07'),
(49, 2, 'Kabiru Adamu', '08038470400', '1745574365675596308', '', 'Purchase of MTN-DATA for 08038470400', '10780.00', 'success', '', 'Data Purchase', '2025-04-25 09:45:17'),
(50, 2, '', '04280379696', 'pay_1745574453_88dd33f9', '', 'IKEDCA', '25000.00', '', '', 'Electricity Subscription', '2025-04-25 09:47:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'subscriber',
  `status` varchar(5) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `phone`, `password`, `role`, `category`, `status`, `created_at`) VALUES
(1, 'Dpeace', 'Oversight', 'oversight', 'oversight@dpeaceapp.com', '08065615684', '$2y$10$j9GZbCZ0l8dPlqEzvEZjm.M6GAc7.nu/GMPHpnpwSGithcBkeHmMS', 'admin', 'vendor', '', '2025-02-10 17:32:09'),
(2, 'Kabiru', 'Adamu', 'kabiradam', 'adamkabeer24@outlook.com', '08065615684', '$2y$10$QpqRiyNPlpBqk1kJksm8HeMlLvRW9CHoQb3V710qHy92dPLkmFCcS', 'user', 'subscriber', '1', '2025-03-04 15:30:38'),
(3, 'Zainab', 'Abubakar', 'zainaabub', 'zainababubakar05@gmail.com', '07068181490', '$2y$10$gn6B6frCruBhpDp29FypI.ur9kC9wV.HjuNsKX6VlRB92dtunTvMK', 'customer_care', 'subscriber', '1', '2025-04-24 09:18:29'),
(4, 'Danjuma', 'Okeme', 'danjuokem', 'o.christopherd@yahoo.com', '08098482929', '$2y$10$wBDHLpoYojnOPMUiVwScJ.CcL3LLrGYv.8l8pieeHyjhQXQ3ON1/.', 'user', 'subscriber', '1', '2025-04-25 09:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `balance`, `created_at`, `updated_at`) VALUES
(1, 2, '71482.87', '2025-03-22 13:53:33', '2025-04-25 09:47:35'),
(4, 3, '0.00', '2025-04-24 10:08:47', '2025-04-24 10:08:47'),
(5, 4, '0.00', '2025-04-25 09:21:21', '2025-04-25 09:21:21');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_accounts`
--

CREATE TABLE `wallet_accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `account_name` varchar(50) NOT NULL,
  `bank_code` varchar(10) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `reservation_reference` varchar(255) DEFAULT NULL,
  `reserved_account_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `bvn` varchar(11) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_accounts`
--

INSERT INTO `wallet_accounts` (`id`, `user_id`, `account_number`, `account_name`, `bank_code`, `bank_name`, `reservation_reference`, `reserved_account_type`, `status`, `bvn`, `created_on`) VALUES
(1, 2, '6038690609', 'DPeaceApp-kabiru', '035', 'Moniepoint Microfinance Bank', '', 'GENERAL', 'ACTIVE', '22197936450', '2025-03-04 15:31:03'),
(4, 3, '6039010530', 'Zai', '50515', 'Moniepoint Microfinance Bank', '66ASREBRXD8V61W03237', 'GENERAL', 'ACTIVE', '22232038770', '2025-04-24 09:51:07'),
(5, 4, '6039731367', 'Dan', '50515', 'Moniepoint Microfinance Bank', '28HEH8W15H5776M05649', 'GENERAL', 'ACTIVE', '40321586341', '2025-04-25 09:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_balances`
--
ALTER TABLE `api_balances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_management`
--
ALTER TABLE `api_management`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_plans`
--
ALTER TABLE `data_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `swift_data_plans`
--
ALTER TABLE `swift_data_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wallet_accounts`
--
ALTER TABLE `wallet_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_balances`
--
ALTER TABLE `api_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `api_management`
--
ALTER TABLE `api_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `data_plans`
--
ALTER TABLE `data_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `swift_data_plans`
--
ALTER TABLE `swift_data_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wallet_accounts`
--
ALTER TABLE `wallet_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wallet_accounts`
--
ALTER TABLE `wallet_accounts`
  ADD CONSTRAINT `wallet_accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
