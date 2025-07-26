-- phpMyAdmin SQL Dump
-- version 5.2.0
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2025 at 09:57 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  firstname varchar(100) NOT NULL,
  lastname varchar(100) NOT NULL,
  username varchar(50) NOT NULL UNIQUE,
  email varchar(100) NOT NULL UNIQUE,  -- This already defines a unique constraint on email
  phone varchar(15) DEFAULT NULL,
  password varchar(255) NOT NULL,
  role varchar(50) NOT NULL,
  category varchar(50) NOT NULL DEFAULT 'subscriber',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- Insert Sample Users
INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `phone`, `password`, `role`, `category`, `created_at`) VALUES
(1, 'Nortech', 'Digital', 'nortech_admin', 'oversight@nortechdigital.com.ng', '08065615684', '$2y$10$j9GZbCZ0l8dPlqEzvEZjm.M6GAc7.nu/GMPHpnpwSGithcBkeHmMS', 'admin', 'vendor', '2025-02-10 17:32:09'),
(2, 'Kabiru', 'Adamu', 'kabiru_a', 'adamkabeer24@outlook.com', '08065615684', '$2y$10$j9GZbCZ0l8dPlqEzvEZjm.M6GAc7.nu/GMPHpnpwSGithcBkeHmMS', 'admin', 'agent', '2025-01-27 20:07:44'),
(3, 'Imonitie', 'Aregbeyen', 'imonitie_ar', 'aregbeyenimonitie@gmail.com', '09061680055', '$2y$10$2UsqW6.nXg4tkSSajUd8VOSZZh7Z0ehJD19eQXWZVd5gpRfgSWf/O', 'user', 'subscriber', '2025-01-29 08:40:24');

COMMIT;
