
CREATE DATABASE IF NOT EXISTS kedai_pembukuan
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kedai_pembukuan;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin','kasir') DEFAULT 'kasir',
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kategori_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL DEFAULT 0,
    hpp DECIMAL(12,2) NOT NULL DEFAULT 0,
    gambar VARCHAR(255) NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_menu(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE bahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    stok DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok_minimum DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_satuan DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(30) UNIQUE NOT NULL,
    tanggal DATETIME NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    bayar DECIMAL(12,2) NOT NULL DEFAULT 0,
    kembalian DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_bayar ENUM('tunai','qris','transfer') DEFAULT 'tunai',
    kasir_id INT NULL,
    keterangan VARCHAR(255) NULL,
    status ENUM('selesai','batal') DEFAULT 'selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kasir_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE detail_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penjualan_id INT NOT NULL,
    menu_id INT NOT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    qty INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id)
) ENGINE=InnoDB;

CREATE TABLE kategori_pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATETIME NOT NULL,
    kategori_id INT NULL,
    deskripsi VARCHAR(255) NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    bukti VARCHAR(255) NULL,
    input_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_pengeluaran(id) ON DELETE SET NULL,
    FOREIGN KEY (input_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (username, password, nama_lengkap, role) VALUES
('admin', '$2b$10$bVXA0fKT1pBtsUfgipEF6.mhABRyLmlwhOZP9EqD1T14Ehi8WDg7.', 'Administrator', 'admin'),
('kasir1', '$2b$10$bVXA0fKT1pBtsUfgipEF6.mhABRyLmlwhOZP9EqD1T14Ehi8WDg7.', 'Kasir Satu', 'kasir');

INSERT INTO kategori_menu (nama_kategori) VALUES
('Susu Murni'),
('Aneka Ketan Susu'),
('Menu Lainnya'),
('Menu Pedas');

INSERT INTO menu (kategori_id, nama_menu, harga_jual, hpp) VALUES
(1, 'Susu Murni Original', 10000),
(1, 'Susu Murni Coklat', 13000),
(1, 'Susu Murni Milo', 13000),
(1, 'Susu Murni Kopi', 13000),
(1, 'Susu Murni Tjampolay', 13000),
(1, 'Susu Murni Strawberry', 13000),
(1, 'Susu Murni Taro', 13000),
(1, 'Susu Murni Telor', 14000),
(1, 'Susu Murni Jahe', 14000),
(1, 'Susu Murni Madu', 14000),
(1, 'Susu Murni Telor Madu Jahe', 21000),
(2, 'Ketan Susu Original', 8000),
(2, 'Ketan Susu Coklat', 10000),
(2, 'Ketan Susu Keju', 10000),
(2, 'Ketan Susu Matcha', 10000),
(2, 'Ketan Susu Choco Crunchy', 10000),
(2, 'Ketan Susu Tiramisu', 10000),
(2, 'Ketan Susu Coklat Keju', 12000),
(2, 'Ketan Susu Mangga Keju', 13000),
(3, 'Kentang Goreng', 8000),
(3, 'Pisang Bakar Coklat Susu', 10000);
(3, 'Pisang Bakar Coklat Keju', 12000);
(3, 'Roti Bakar Coklat Susu', 8000);
(3, 'Roti Bakar Keju Susu', 10000);
(3, 'Roti Bakar Coklat Keju Susu', 12000);
(4, 'Cilok Bumbu Kacang', 10000);
(4, 'Cilok Goang', 12000);

INSERT INTO kategori_pengeluaran (nama_kategori) VALUES
('Belanja Bahan Baku'),
('Listrik & Air'),
('Sewa Tempat'),
('Gaji Karyawan'),
('Operasional Lain');
