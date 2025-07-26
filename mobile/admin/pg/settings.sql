CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL
);

INSERT INTO settings (name, value) VALUES
('buyingPriceData', ''),
('sellingPriceData', ''),
('profitData', ''),
('subscriberDiscountData', ''),
('agentDiscountData', ''),
('vendorDiscountData', ''),
-- ...repeat for other fields...
;
