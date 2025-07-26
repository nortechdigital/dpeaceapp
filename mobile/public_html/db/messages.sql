-- Create the messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL
);

-- Insert a sample entry into the messages table
INSERT INTO messages (name, email, phone, subject, message) VALUES
('John Doe', 'johndoe@example.com', '123-456-7890', 'Inquiry about Product X', 'I am interested in purchasing Product X. Could you provide more information on pricing and availability?');

-- Sample additional entry for testing
INSERT INTO messages (name, email, phone, subject, message) VALUES
('Jane Smith', 'janesmith@example.com', '987-654-3210', 'Feedback on Service Y', 'The service I received was excellent, but I have some suggestions for improvement.');
