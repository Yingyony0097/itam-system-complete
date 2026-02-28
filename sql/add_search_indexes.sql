-- ເພີ່ມ Index ສຳລັບການຄົ້ນຫາທີ່ໃຊ້ເລື້ອຍໆ
-- ເພື່ອເພີ່ມປະສິດທິພາບການ query

-- Index ຊື່ຊັບສິນ (ໃຊ້ໃນການຄົ້ນຫາ)
ALTER TABLE `assets` ADD INDEX `idx_asset_name` (`asset_name`);

-- Index ຊື່ຜູ້ໃຊ້ (ໃຊ້ໃນການຄົ້ນຫາ)
ALTER TABLE `users` ADD INDEX `idx_user_name` (`name`);

-- Composite index ສຳລັບ check_logs (ໃຊ້ໃນ history filter)
ALTER TABLE `check_logs` ADD INDEX `idx_action_type_date` (`action_type`, `action_date`);
