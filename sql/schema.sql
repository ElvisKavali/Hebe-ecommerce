-- HEBE Kitchen Store — MySQL Schema + Sample Data
-- Import via phpMyAdmin into database `hebe_store` (utf8mb4_unicode_ci)

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

-- ========== USERS ==========
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    avatar VARCHAR(255) DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verify_token VARCHAR(64) DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== SETTINGS ==========
CREATE TABLE settings (
    `key` VARCHAR(80) PRIMARY KEY,
    `value` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CATEGORIES ==========
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    image VARCHAR(255) DEFAULT NULL,
    description TEXT,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== BRANDS ==========
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    logo VARCHAR(255) DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== PRODUCTS ==========
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    sku VARCHAR(60) NOT NULL UNIQUE,
    barcode VARCHAR(60) DEFAULT NULL,
    category_id INT DEFAULT NULL,
    brand_id INT DEFAULT NULL,
    description TEXT,
    specifications TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    weight DECIMAL(8,2) DEFAULT NULL,
    dimensions VARCHAR(80) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(3,2) NOT NULL DEFAULT 0,
    reviews_count INT NOT NULL DEFAULT 0,
    tags VARCHAR(255) DEFAULT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    bestseller TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cat (category_id),
    INDEX idx_brand (brand_id),
    INDEX idx_featured (featured),
    INDEX idx_status (status),
    FULLTEXT KEY ft_name_desc (name, description),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ADDRESSES ==========
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(60) DEFAULT 'Home',
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    line1 VARCHAR(200) NOT NULL,
    line2 VARCHAR(200) DEFAULT NULL,
    city VARCHAR(80) NOT NULL,
    state VARCHAR(80) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    country VARCHAR(80) NOT NULL DEFAULT 'Kenya',
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CART ==========
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== WISHLIST ==========
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== COUPONS ==========
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(10,2) NOT NULL,
    min_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT NOT NULL DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ORDERS ==========
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    user_id INT DEFAULT NULL,
    guest_email VARCHAR(160) DEFAULT NULL,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    city VARCHAR(80) NOT NULL,
    country VARCHAR(80) NOT NULL DEFAULT 'Kenya',
    postal_code VARCHAR(20) DEFAULT NULL,
    notes TEXT,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    coupon_code VARCHAR(40) DEFAULT NULL,
    payment_method ENUM('stripe','mpesa','paypal','cod','bank') NOT NULL DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    status ENUM('pending','confirmed','processing','packed','shipped','delivered','cancelled','returned','refunded') NOT NULL DEFAULT 'pending',
    tracking_number VARCHAR(80) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_payment (payment_status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    name VARCHAR(200) NOT NULL,
    sku VARCHAR(60) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method VARCHAR(40) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reference VARCHAR(120) DEFAULT NULL,
    status ENUM('pending','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
    payload TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== REVIEWS ==========
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    author_name VARCHAR(120) NOT NULL,
    rating TINYINT NOT NULL,
    title VARCHAR(160) DEFAULT NULL,
    body TEXT,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== CONTACTS ==========
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    read_flag TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== NOTIFICATIONS ==========
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT,
    `read` TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== ACTIVITY LOGS ==========
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(120) NOT NULL,
    description TEXT,
    ip VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Admin password: Admin@123  |  Customer password: Customer@123
-- (Bcrypt hashes precomputed)
INSERT INTO users (name, email, password, phone, role, email_verified) VALUES
('HEBE Admin',   'hebeappliances@gmail.com', '$2y$10$hBUZaYpXzckXiBnqMAtTcOEhMWyQUdoOHIs0RBc7/ESjhwOvbYlFq', '+254700000000', 'admin', 1),
('Beatrice Wathiu', 'bettywathiu7@gmail.com', '$2y$10$whqFaR9cihEjbyjmGhF9KOXwc2aktBER/KDySRnx84hBNEaBwY/d6', '+254711222333', 'customer', 1),
('Elvis Ndanu', 'sharkymidder@gmail.com','$2y$10$whqFaR9cihEjbyjmGhF9KOXwc2aktBER/KDySRnx84hBNEaBwY/d6', '+254722333444', 'customer', 1);

INSERT INTO settings (`key`,`value`) VALUES
('site_name','HEBE Kitchen Store'),
('site_email','hebeappliances@gmail.com'),
('site_phone','+254 720487799'),
('site_address','Nairobi, Kenya'),
('currency','KSH'),
('about','HEBE is your destination for premium kitchenware and small kitchen electronics. Curated, durable, and beautifully designed.');

INSERT INTO categories (name, slug, description) VALUES
('Cookware',         'cookware',         'Pans, pots, woks and Dutch ovens for everyday cooking.'),
('Cutlery',          'cutlery',          'Chef knives, paring knives and full knife blocks.'),
('Dinner Sets',      'dinner-sets',      'Plates, bowls and serving dishes for every occasion.'),
('Small Appliances', 'small-appliances', 'Blenders, toasters, kettles and coffee makers.'),
('Storage',          'storage',          'Food storage, canisters and pantry organization.'),
('Bakeware',         'bakeware',         'Trays, molds, baking sheets and cooling racks.');

INSERT INTO brands (name, slug) VALUES
('HEBE Pro',  'hebe-pro'),
('CulinaX',   'culinax'),
('NordChef',  'nordchef'),
('AromaHome', 'aromahome'),
('SteelEdge', 'steeledge');

INSERT INTO products (name, slug, sku, category_id, brand_id, description, specifications, price, discount_price, stock, weight, dimensions, image, rating, reviews_count, tags, featured, bestseller) VALUES
('Tri-Ply Stainless Steel Frying Pan 28cm', 'triply-frying-pan-28', 'COOK-001', 1, 1, 'Professional tri-ply construction with an aluminum core sandwiched between stainless steel. Even heat distribution, oven-safe up to 260°C.', 'Material: Stainless steel; Diameter: 28cm; Induction: Yes; Dishwasher safe: Yes', 89.00, 69.00, 24, 1.2, '28x28x6 cm', 'samples/pan.jpg', 4.7, 18, 'frying,pan,stainless', 1, 1),
('Cast Iron Dutch Oven 5L Enameled',         'cast-iron-dutch-oven-5l', 'COOK-002', 1, 3, 'Enameled cast iron Dutch oven, perfect for braises, stews and slow cooking. Retains heat beautifully.', 'Capacity: 5L; Material: Enameled cast iron; Oven safe: Yes', 149.00, NULL, 12, 5.4, '32x26x16 cm', 'samples/dutch-oven.jpg', 4.9, 26, 'dutch,oven,cast-iron', 1, 1),
('8-Inch Damascus Chef Knife',               'damascus-chef-knife-8', 'CUT-001', 2, 5, '67-layer Damascus steel chef knife with pakkawood handle. Razor-sharp edge, ergonomic grip.', 'Blade: 8 inch; Steel: VG-10 Damascus; Hardness: HRC 60', 119.00, 95.00, 9, 0.35, '34x5x3 cm', 'samples/knife.jpg', 4.8, 41, 'knife,damascus,chef', 1, 1),
('16-Piece Stoneware Dinner Set',            '16pc-stoneware-dinner-set', 'DIN-001', 3, 2, 'Beautifully glazed stoneware dinner set for 4. Includes plates, bowls and mugs.', 'Pieces: 16; Material: Stoneware; Microwave safe: Yes', 129.00, 99.00, 18, 9.0, '40x30x30 cm', 'samples/dinner-set.jpg', 4.5, 12, 'dinner,plates,stoneware', 1, 0),
('1.7L Stainless Electric Kettle',           'stainless-electric-kettle', 'APP-001', 4, 4, 'Fast-boil 1.7L electric kettle with auto shut-off and boil-dry protection. Sleek brushed steel.', 'Capacity: 1.7L; Power: 2200W; Cordless: Yes', 49.00, 39.00, 35, 1.1, '23x16x23 cm', 'samples/kettle.jpg', 4.4, 33, 'kettle,electric,appliance', 0, 1),
('High-Speed Glass Blender 1500W',           'high-speed-blender-1500w', 'APP-002', 4, 2, 'Powerful 1500W motor with 6 stainless steel blades. Crushes ice, blends smoothies, makes nut butter.', 'Power: 1500W; Jar: 1.5L glass; Speeds: 5 + pulse', 119.00, NULL, 14, 4.8, '20x20x42 cm', 'samples/blender.jpg', 4.6, 21, 'blender,smoothie,appliance', 1, 0),
('Airtight Glass Storage Jars (Set of 6)',   'airtight-glass-jars-6', 'STO-001', 5, 1, 'Set of 6 borosilicate glass jars with bamboo lids and silicone seals. Pantry organization made easy.', 'Pieces: 6; Material: Borosilicate glass + bamboo', 35.00, 28.00, 60, 2.5, '30x20x20 cm', 'samples/jars.jpg', 4.3, 9, 'storage,jars,glass', 0, 0),
('Non-Stick Baking Sheet Trio',              'nonstick-baking-sheet-trio', 'BAK-001', 6, 3, 'Three heavy-gauge non-stick baking sheets. Warp-resistant, easy release, dishwasher safe.', 'Pieces: 3; Material: Carbon steel + non-stick coating', 39.00, NULL, 42, 1.9, '40x30x2 cm', 'samples/baking-sheet.jpg', 4.5, 14, 'bakeware,baking,sheet', 0, 1),
('Pour-Over Coffee Dripper + Server',        'pour-over-coffee-set', 'APP-003', 4, 4, 'Glass pour-over dripper with 600ml server. Brews up to 4 cups of beautifully balanced coffee.', 'Capacity: 600ml; Material: Borosilicate glass', 32.00, NULL, 28, 0.7, '15x15x20 cm', 'samples/pourover.jpg', 4.7, 17, 'coffee,pourover,glass', 0, 0),
('Bamboo Cutting Board Set',                 'bamboo-cutting-board-set', 'STO-002', 5, 1, 'Three solid bamboo cutting boards in small, medium, large. Knife-friendly and naturally antimicrobial.', 'Pieces: 3; Material: Bamboo', 29.00, 22.00, 70, 2.0, '40x30x4 cm', 'samples/cutting-board.jpg', 4.4, 11, 'bamboo,cutting,board', 0, 0),
('Silicone Cooking Utensil Set (10pc)',      'silicone-utensil-set-10', 'COOK-003', 1, 2, '10-piece food-grade silicone utensil set with beech handles. Heat-resistant to 230°C.', 'Pieces: 10; Heat: 230°C; Dishwasher safe: Yes', 45.00, 35.00, 50, 1.4, '35x12x12 cm', 'samples/utensils.jpg', 4.5, 19, 'utensils,silicone,set', 0, 0),
('Professional Stand Mixer 5.5L',            'stand-mixer-5-5l', 'APP-004', 4, 3, '5.5L tilt-head stand mixer with 10 speeds. Includes whisk, dough hook, and flat beater.', 'Capacity: 5.5L; Power: 1000W; Speeds: 10', 349.00, 299.00, 6, 11.5, '38x22x36 cm', 'samples/mixer.jpg', 4.9, 38, 'mixer,baking,appliance', 1, 1);

INSERT INTO coupons (code, type, value, min_total, max_uses, expires_at) VALUES
('WELCOME10', 'percent', 10.00, 50.00, 1000, '2030-12-31'),
('FREESHIP',  'fixed',   5.00,  30.00, 1000, '2030-12-31'),
('HEBE25',    'percent', 25.00, 200.00, 500, '2030-12-31');

INSERT INTO addresses (user_id, label, full_name, phone, line1, city, country, postal_code, is_default) VALUES
(2, 'Home', 'Beatrice Wathiu', '+254720487799', 'Buruburu', 'Nairobi', 'Kenya', '00100', 1);

INSERT INTO reviews (product_id, user_id, author_name, rating, title, body) VALUES
(1, 2, 'Jane W.', 5, 'Outstanding pan', 'Heats evenly, nothing sticks, looks gorgeous on the stove.'),
(1, 3, 'Peter O.', 4, 'Solid build', 'Heavy and well made. Handle stays cool.'),
(3, 2, 'Jane W.', 5, 'Sharpest knife I own', 'Cuts tomatoes like butter. Worth every cent.'),
(12,3, 'Peter O.', 5, 'Bakers dream', 'My breads have never been better.');

INSERT INTO orders (order_number, user_id, full_name, phone, address_line, city, country, postal_code, subtotal, shipping, tax, discount, total, payment_method, payment_status, status) VALUES
('HEBE-100001', 2, 'Beatrice Wathiu', '+254720487799', 'Buruburu', 'Nairobi', 'Kenya', '00100', 164.00, 0.00, 13.12, 16.40, 160.72, 'stripe', 'paid', 'delivered'),
('HEBE-100002', 3, 'Elvis Ndanu', '+254787790754', 'Buruburu', 'Nairobi', 'Kenya', '00100',  95.00, 5.00,  7.60, 0.00, 107.60, 'cod', 'pending', 'processing');

INSERT INTO order_items (order_id, product_id, name, sku, price, quantity, line_total) VALUES
(1, 1, 'Tri-Ply Stainless Steel Frying Pan 28cm', 'COOK-001', 69.00, 1, 69.00),
(1, 4, '16-Piece Stoneware Dinner Set', 'DIN-001', 95.00, 1, 95.00),
(2, 3, '8-Inch Damascus Chef Knife', 'CUT-001', 95.00, 1, 95.00);
