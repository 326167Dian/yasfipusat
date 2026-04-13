-- Tambahkan lock berbasis session browser untuk mencegah akun sama memakai transaksi ON yang sama.

SET @db_name := DATABASE();

SET @sql_add_session := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'kdtk'
              AND COLUMN_NAME = 'session_kasir'
        ) = 0,
        'ALTER TABLE kdtk ADD COLUMN session_kasir VARCHAR(128) NULL AFTER id_admin',
        'SELECT ''column session_kasir already exists'''
    )
);
PREPARE stmt1 FROM @sql_add_session;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql_add_idx_admin_status := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'kdtk'
              AND INDEX_NAME = 'idx_kdtk_admin_status'
        ) = 0,
        'ALTER TABLE kdtk ADD INDEX idx_kdtk_admin_status (id_admin, stt_kdtk)',
        'SELECT ''index idx_kdtk_admin_status already exists'''
    )
);
PREPARE stmt2 FROM @sql_add_idx_admin_status;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql_add_idx_session := (
    SELECT IF(
        (
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = @db_name
              AND TABLE_NAME = 'kdtk'
              AND INDEX_NAME = 'idx_kdtk_session_kasir'
        ) = 0,
        'ALTER TABLE kdtk ADD INDEX idx_kdtk_session_kasir (session_kasir)',
        'SELECT ''index idx_kdtk_session_kasir already exists'''
    )
);
PREPARE stmt3 FROM @sql_add_idx_session;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
