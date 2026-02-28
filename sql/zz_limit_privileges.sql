-- ຈຳກັດສິດ MySQL User (ໃຊ້ເຉພາະ SELECT, INSERT, UPDATE, DELETE)
-- ບໍ່ໃຫ້ສິດ DROP, ALTER, CREATE ເພື່ອຄວາມປອດໄພ

REVOKE ALL PRIVILEGES ON `itam_system`.* FROM 'itam_user'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `itam_system`.* TO 'itam_user'@'%';
FLUSH PRIVILEGES;
