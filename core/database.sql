CREATE TABLE
    `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `phone` VARCHAR(20),
        `gender` ENUM ('male', 'female', 'other') DEFAULT NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM ('admin', 'customer') NOT NULL DEFAULT 'customer',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        status ENUM ('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150) NOT NULL UNIQUE,
        meta_description TEXT,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        status ENUM ('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
    );

CREATE TABLE
    product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255),
        is_primary BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

CREATE TABLE
    product_variants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        size VARCHAR(20),
        color VARCHAR(50),
        stock INT DEFAULT 0,
        additional_price DECIMAL(10, 2) DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

CREATE TABLE
    looks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        image VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    look_product (
        id INT AUTO_INCREMENT PRIMARY KEY,
        look_id INT,
        product_id INT,
        FOREIGN KEY (look_id) REFERENCES looks (id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
    );

CREATE TABLE
    tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    product_tag (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        tag_id INT NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
    );

ALTER TABLE tags ADD color VARCHAR(7) DEFAULT '#5d3fd3';

CREATE TABLE
    cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        variant_id INT DEFAULT NULL,
        quantity INT DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL
    );

CREATE TABLE
    orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(20) NOT NULL UNIQUE,
        total_amount DECIMAL(10, 2) NOT NULL,
        payment_method VARCHAR(50), -- e.g., "Cash on Delivery", "Card"
        payment_status ENUM ('pending', 'paid', 'failed') DEFAULT 'pending',
        order_status ENUM (
            'pending',
            'confirmed',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

CREATE TABLE
    order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        variant_id INT DEFAULT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL, -- final price at time of order
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products (id),
        FOREIGN KEY (variant_id) REFERENCES product_variants (id)
    );

CREATE TABLE
    shipping_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        full_name VARCHAR(150),
        phone VARCHAR(20),
        address_line1 VARCHAR(255),
        address_line2 VARCHAR(255),
        city VARCHAR(100),
        district VARCHAR(100),
        postal_code VARCHAR(20),
        country VARCHAR(100) DEFAULT 'Sri Lanka',
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
    );

CREATE TABLE
    order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status ENUM (
            'pending',
            'confirmed',
            'shipped',
            'delivered',
            'cancelled'
        ) NOT NULL,
        updated_by INT DEFAULT NULL, -- admin user ID (optional)
        remarks TEXT,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
    );

CREATE TABLE
    wishlist (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    );