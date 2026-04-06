-- Optimasi index untuk endpoint lapstok (barang-serverside.php)
-- Aman dijalankan berulang (idempotent), kompatibel MySQL/MariaDB lama.

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'trkasir_detail'
           AND index_name = 'idx_trkasir_detail_id_barang') = 0,
        'ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_id_barang (id_barang)',
        'SELECT ''idx_trkasir_detail_id_barang already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'trkasir_detail'
           AND index_name = 'idx_trkasir_detail_waktu_id_barang') = 0,
        'ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_waktu_id_barang (waktu, id_barang)',
        'SELECT ''idx_trkasir_detail_waktu_id_barang already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'trkasir_detail'
           AND index_name = 'idx_trkasir_detail_waktu_kd_barang') = 0,
        'ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_waktu_kd_barang (waktu, kd_barang)',
        'SELECT ''idx_trkasir_detail_waktu_kd_barang already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'trkasir_detail'
           AND index_name = 'idx_trkasir_detail_kd_trkasir') = 0,
        'ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_kd_trkasir (kd_trkasir)',
        'SELECT ''idx_trkasir_detail_kd_trkasir already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'trkasir'
           AND index_name = 'idx_trkasir_kd_trkasir') = 0,
        'ALTER TABLE trkasir ADD INDEX idx_trkasir_kd_trkasir (kd_trkasir)',
        'SELECT ''idx_trkasir_kd_trkasir already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        (SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = 'barang'
           AND index_name = 'idx_barang_id_barang') = 0,
        'ALTER TABLE barang ADD INDEX idx_barang_id_barang (id_barang)',
        'SELECT ''idx_barang_id_barang already exists'''
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Validasi setelah eksekusi:
-- SHOW INDEX FROM trkasir_detail;
-- SHOW INDEX FROM trkasir;
-- SHOW INDEX FROM barang;
