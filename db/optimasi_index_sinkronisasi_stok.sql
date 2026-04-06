-- Optimasi index untuk sinkronisasi stok
-- Aman dijalankan berulang (idempotent)

SET @schema_name = DATABASE();

SET @sql = IF(
    (SELECT COUNT(1)
     FROM information_schema.statistics
     WHERE table_schema = @schema_name
       AND table_name = 'barang'
       AND index_name = 'idx_barang_kd_barang') = 0,
    'ALTER TABLE barang ADD INDEX idx_barang_kd_barang (kd_barang)',
    'SELECT ''idx_barang_kd_barang sudah ada'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(1)
     FROM information_schema.statistics
     WHERE table_schema = @schema_name
       AND table_name = 'trbmasuk_detail'
       AND index_name = 'idx_trbmasuk_detail_kd_barang') = 0,
    'ALTER TABLE trbmasuk_detail ADD INDEX idx_trbmasuk_detail_kd_barang (kd_barang)',
    'SELECT ''idx_trbmasuk_detail_kd_barang sudah ada'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(1)
     FROM information_schema.statistics
     WHERE table_schema = @schema_name
       AND table_name = 'trkasir_detail'
       AND index_name = 'idx_trkasir_detail_kd_barang') = 0,
    'ALTER TABLE trkasir_detail ADD INDEX idx_trkasir_detail_kd_barang (kd_barang)',
    'SELECT ''idx_trkasir_detail_kd_barang sudah ada'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
