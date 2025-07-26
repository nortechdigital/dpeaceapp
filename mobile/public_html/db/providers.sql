-- Create the providers table
CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
);

-- Insert entries into the providers table
INSERT INTO providers (name, logo, description) VALUES
('9mobile', 'img/logo/9moble_logo.png', '9mobile data subscription plans.'),
('AIRTEL', 'img/logo/airtel_logo.png', 'Airtel data subscription plans.'),
('Glo', 'img/logo/glo_logo.jpg', 'Glo data subscription plans.'),
('MTN', 'img/logo/mtn_logo.png', 'MTN data subscription plans.'),
('Smile', 'img/logo/smile_logo.jpg', 'Smile data subscription plans.');