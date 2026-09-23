CREATE TABLE IF NOT EXISTS `products` (
  `ProductID` INT AUTO_INCREMENT PRIMARY KEY,
  `ProductName` VARCHAR(250) NOT NULL,
  `CategoryName` VARCHAR(100) DEFAULT 'Bakery',
  `UnitPrice` DECIMAL(10,2) NOT NULL,
  `UnitsInStock` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`ProductName`, `CategoryName`, `UnitPrice`, `UnitsInStock`) VALUES
('เค้กสตรอว์เบอร์รี (Strawberry Shortcake)', 'Bakery', 120.00, 15),
('ครัวซองต์เนยสด (Butter Croissant)', 'Bakery', 65.00, 30),
('ช็อกโกแลตลาวา (Chocolate Lava)', 'Bakery', 95.00, 10),
('มัจฉะลาเต้ (Matcha Latte)', 'Beverages', 55.00, 50);dbUYribAfSnxaHcpuwuhXaIxIfMvkCGW