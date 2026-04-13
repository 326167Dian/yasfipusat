-- Optimasi histori hapus detail kasir.
-- Tujuan:
-- 1) Pastikan tabel trkasir_detail_hist ada.
-- 2) Tambahkan metadata user penghapus dan waktu hapus.

SET @db_name := DATABASE();

SET @sql_create_hist := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
        ) = 0,
        'CREATE TABLE trkasir_detail_hist LIKE trkasir_detail',
        'SELECT ''table trkasir_detail_hist already exists'''
    )
);
PREPARE stmt1 FROM @sql_create_hist;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql_add_deleted_by_id := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND COLUMN_NAME = 'deleted_by_id'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD COLUMN deleted_by_id INT(11) NULL AFTER id_dtrkasir',
        'SELECT ''column deleted_by_id already exists'''
    )
);
PREPARE stmt2 FROM @sql_add_deleted_by_id;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql_add_deleted_by_username := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND COLUMN_NAME = 'deleted_by_username'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD COLUMN deleted_by_username VARCHAR(100) NULL AFTER deleted_by_id',
        'SELECT ''column deleted_by_username already exists'''
    )
);
PREPARE stmt3 FROM @sql_add_deleted_by_username;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

SET @sql_add_deleted_by_name := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND COLUMN_NAME = 'deleted_by_name'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD COLUMN deleted_by_name VARCHAR(120) NULL AFTER deleted_by_username',
        'SELECT ''column deleted_by_name already exists'''
    )
);
PREPARE stmt4 FROM @sql_add_deleted_by_name;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

SET @sql_add_deleted_at := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND COLUMN_NAME = 'deleted_at'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD COLUMN deleted_at DATETIME NULL AFTER deleted_by_name',
        'SELECT ''column deleted_at already exists'''
    )
);
PREPARE stmt5 FROM @sql_add_deleted_at;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

SET @sql_add_delete_source := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND COLUMN_NAME = 'delete_source'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD COLUMN delete_source VARCHAR(80) NULL AFTER deleted_at',
        'SELECT ''column delete_source already exists'''
    )
);
PREPARE stmt6 FROM @sql_add_delete_source;
EXECUTE stmt6;
DEALLOCATE PREPARE stmt6;

SET @sql_add_idx_deleted_at := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND INDEX_NAME = 'idx_trkasir_detail_hist_deleted_at'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD INDEX idx_trkasir_detail_hist_deleted_at (deleted_at)',
        'SELECT ''index idx_trkasir_detail_hist_deleted_at already exists'''
    )
);
PREPARE stmt7 FROM @sql_add_idx_deleted_at;
EXECUTE stmt7;
DEALLOCATE PREPARE stmt7;

SET @sql_add_idx_kd := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'trkasir_detail_hist'
              AND INDEX_NAME = 'idx_trkasir_detail_hist_kd'
        ) = 0,
        'ALTER TABLE trkasir_detail_hist ADD INDEX idx_trkasir_detail_hist_kd (kd_trkasir)',
        'SELECT ''index idx_trkasir_detail_hist_kd already exists'''
    )
);
PREPARE stmt8 FROM @sql_add_idx_kd;
EXECUTE stmt8;
DEALLOCATE PREPARE stmt8;
