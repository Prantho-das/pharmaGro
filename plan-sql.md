-- ==========================================
-- 1. CONFIGURATION & CATEGORIZATION
-- ==========================================

-- ক্যাটাগরি: এটি দিয়ে 'Pharmacy Mode' অন/অফ লজিক কন্ট্রোল হবে
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    type ENUM('pharma', 'grocery', 'cosmetics', 'general') DEFAULT 'pharma',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ব্র্যান্ড/কোম্পানি: ম্যানুফ্যাকচারার ট্র্যাকিং
CREATE TABLE brands (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ইউনিট: পিস, বক্স, স্ট্রিপ, কেজি ইত্যাদি
CREATE TABLE units (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- e.g., Piece, Strip, Box, Kg
    short_name VARCHAR(10) NOT NULL, -- e.g., pc, str, bx, kg
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 2. PRODUCT & VARIANT SYSTEM (Mobile Ready)
-- ==========================================

-- মাস্টার প্রোডাক্ট: ওষুধের জেনেরিক নাম ও বেসিক তথ্য
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    brand_id INTEGER REFERENCES brands(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL, -- e.g., Napa
    generic_name VARCHAR(255) NULL, -- e.g., Paracetamol
    is_medicine BOOLEAN DEFAULT FALSE, -- POS স্ক্রিন ডাইনামিক করার জন্য
    has_variants BOOLEAN DEFAULT FALSE,
    image_path TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- প্রোডাক্ট ভ্যারিয়েন্ট: বিভিন্ন সাইজ বা প্যাক (৫০০মিগ্রা বনাম ৬৫০মিগ্রা)
CREATE TABLE product_variants (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    unit_id INTEGER REFERENCES units(id),
    variant_name VARCHAR(255) NOT NULL, -- e.g., 500mg Strip, 100g Soap
    sku VARCHAR(100) UNIQUE NOT NULL,    -- বারকোড স্ক্যানিংয়ের জন্য (Mobile Ready)
    min_stock_alert INTEGER DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 3. INVENTORY & BATCH (FEFO Logic)
-- ==========================================

-- ইনভেন্টরি ব্যাচ: প্রতিটি লটের জন্য আলাদা মেয়াদ ও কেনা দাম
CREATE TABLE inventory_batches (
    id SERIAL PRIMARY KEY,
    variant_id INTEGER REFERENCES product_variants(id) ON DELETE CASCADE,
    batch_no VARCHAR(100) NOT NULL,
    expiry_date DATE NOT NULL,
    purchase_price DECIMAL(15, 2) NOT NULL, -- নিট লাভ হিসাবের জন্য
    selling_price DECIMAL(15, 2) NOT NULL,
    current_stock INTEGER NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 4. SALES & TRANSACTIONS (POS Engine)
-- ==========================================

-- কাস্টমার ডাটা
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) DEFAULT 'Walking Customer',
    phone VARCHAR(20) UNIQUE NULL,
    points INTEGER DEFAULT 0, -- লয়্যালটি সিস্টেমের জন্য
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- মেইন সেলস/ইনভয়েস
CREATE TABLE sales (
    id SERIAL PRIMARY KEY,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    customer_id INTEGER REFERENCES customers(id),
    sub_total DECIMAL(15, 2) NOT NULL,
    tax_amount DECIMAL(15, 2) DEFAULT 0,
    discount_amount DECIMAL(15, 2) DEFAULT 0,
    grand_total DECIMAL(15, 2) NOT NULL,
    paid_amount DECIMAL(15, 2) NOT NULL,
    due_amount DECIMAL(15, 2) DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT 'cash', -- cash, bkash, nagad, card
    sold_by_id INTEGER, -- Staff ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- সেলস আইটেম ডিটেইলস
CREATE TABLE sale_items (
    id SERIAL PRIMARY KEY,
    sale_id INTEGER REFERENCES sales(id) ON DELETE CASCADE,
    variant_id INTEGER REFERENCES product_variants(id),
    batch_id INTEGER REFERENCES inventory_batches(id),
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15, 2) NOT NULL,
    total_price DECIMAL(15, 2) NOT NULL
);

-- ==========================================
-- 5. SYSTEM SETTINGS (Dynamic POS Toggle)
-- ==========================================

CREATE TABLE settings (
    id SERIAL PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ডিফল্ট সেটিং ইনসার্ট (Pharmacy Mode Enabled)
INSERT INTO settings (key, value) VALUES ('is_pharmacy_active', 'true');