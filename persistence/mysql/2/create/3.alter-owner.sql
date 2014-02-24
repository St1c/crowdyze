--
-- Alter owner of database
--
GRANT ALL ON *.* TO '%USERLOGIN%'@'localhost' IDENTIFIED BY '%DBPASSWORD%';
FLUSH PRIVILEGES;
