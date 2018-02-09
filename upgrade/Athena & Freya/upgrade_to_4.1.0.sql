# Adjust cp.pending table to length 32 to make room for md5. Run this in CP database.

ALTER TABLE `pending` CHANGE `user_pass` `user_pass` VARCHAR( 32 ) NOT NULL 