-- Typy souborů.
UPDATE `attachment_type` SET `type`= 'unknow', `mime`= '' WHERE `id`='1';
INSERT INTO `attachment_type` (`type`, `mime`) VALUES 
('image', 'gif'),
('image', 'png');
