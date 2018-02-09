-- Use these queries if you ever want to reset CP logs, although it is not recommended.
-- Only works with Query Analyzer. Enterprise Manager does not allow DBCC commands.

TRUNCATE TABLE access_log
DBCC CHECKIDENT(access_log, RESEED, 1)

TRUNCATE TABLE admin_announce
DBCC CHECKIDENT(admin_announce, RESEED, 1)

TRUNCATE TABLE admin_log
DBCC CHECKIDENT(admin_log, RESEED, 1)

TRUNCATE TABLE ban_log
DBCC CHECKIDENT(ban_log, RESEED, 1)

TRUNCATE TABLE exploit_log
DBCC CHECKIDENT(exploit_log, RESEED, 1)

TRUNCATE TABLE gm_announce
DBCC CHECKIDENT(gm_announce, RESEED, 1)

TRUNCATE TABLE money_log
DBCC CHECKIDENT(money_log, RESEED, 1)

TRUNCATE TABLE query_log
DBCC CHECKIDENT(query_log, RESEED, 1)

TRUNCATE TABLE register_log
DBCC CHECKIDENT(register_log, RESEED, 1)

TRUNCATE TABLE user_announce
DBCC CHECKIDENT(user_announce, RESEED, 1)

TRUNCATE TABLE user_log
DBCC CHECKIDENT(user_log, RESEED, 1)