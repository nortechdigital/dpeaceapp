-- Create the users table if it doesn't exist
CREATE TABLE IF NOT EXISTS wallet_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    account_number VARCHAR(20),
    bank_code VARCHAR(10),
    bank_name VARCHAR(255),
    reservation_reference VARCHAR(255),
    reserved_account_type VARCHAR(50),
    status VARCHAR(50),
    bvn VARCHAR(11)
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

