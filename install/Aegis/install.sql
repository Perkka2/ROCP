CREATE TABLE [dbo].[access_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[User/IP] [text] COLLATE Latin1_General_CI_AS NULL ,
	[Action] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[admin_announce] (
	[post_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[message] [text] COLLATE Latin1_General_CI_AS NULL ,
	[poster] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[admin_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[User] [text] COLLATE Latin1_General_CI_AS NULL ,
	[Action] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[anti_bot] (
	[reg_id] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[reg_code] [int] NULL ,
	[ctime] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[ban_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[set_ID] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[ban_ID] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[reason] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[exploit_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[User/IP] [text] COLLATE Latin1_General_CI_AS NOT NULL ,
	[Action] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[gm_announce] (
	[post_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[message] [text] COLLATE Latin1_General_CI_AS NULL ,
	[poster] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[item_db] (
	[ID] [smallint] NOT NULL ,
	[Name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[Type] [smallint] NOT NULL ,
	[Price] [int] NULL ,
	[Weight] [int] NULL ,
	[ATK] [int] NULL ,
	[DEF] [int] NULL ,
	[Range] [int] NULL ,
	[Slots] [int] NULL ,
	[Equip_jobs] [int] NULL ,
	[Equip_genders] [int] NULL ,
	[Equip_location] [int] NULL ,
	[weapon_level] [int] NULL ,
	[equipableLevel] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[ladder_ignore] (
	[AID] [int] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[money_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[From] [int] NULL ,
	[To] [int] NULL ,
	[Action] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[pending] (
	[Date] [datetime] NULL ,
	[auth_code] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[ID] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[passwd] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[gender] [char] (1) COLLATE Latin1_General_CI_AS NULL ,
	[email] [varchar] (50) COLLATE Latin1_General_CI_AS NULL 
	[ip] [varchar] (15) NULL
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[privilege] (
	[AID] [int] NOT NULL ,
	[privilege] [tinyint] NOT NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[query_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[User] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[IP] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[Page] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[Query] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[register_log] (
	[reg_id] [int] IDENTITY (1, 1) NOT NULL ,
	[account_name] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[ip] [int] NULL ,
	[reg_time] [datetime] NULL ,
	[Email] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[skins] (
	[Name] [varchar] (50) COLLATE Latin1_General_CI_AS NULL ,
	[skin] [varchar] (50) COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[status] (
	[last_checked] [datetime] NULL ,
	[login_serv] [tinyint] NULL ,
	[char_serv] [tinyint] NULL ,
	[zone_serv] [tinyint] NULL 
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[user_announce] (
	[post_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[message] [text] COLLATE Latin1_General_CI_AS NULL ,
	[poster] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[user_log] (
	[action_id] [int] IDENTITY (1, 1) NOT NULL ,
	[Date] [datetime] NULL ,
	[User] [text] COLLATE Latin1_General_CI_AS NULL ,
	[Action] [text] COLLATE Latin1_General_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[access_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_access_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[admin_announce] WITH NOCHECK ADD 
	CONSTRAINT [PK_admin_announce] PRIMARY KEY  CLUSTERED 
	(
		[post_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[admin_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_admin_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[ban_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_ban_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[exploit_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_exploit_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[gm_announce] WITH NOCHECK ADD 
	CONSTRAINT [PK_gm_announce] PRIMARY KEY  CLUSTERED 
	(
		[post_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[money_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_money_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[query_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_query_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[register_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_register_log] PRIMARY KEY  CLUSTERED 
	(
		[reg_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[user_announce] WITH NOCHECK ADD 
	CONSTRAINT [PK_user_announce] PRIMARY KEY  CLUSTERED 
	(
		[post_id]
	)  ON [PRIMARY] 
GO

ALTER TABLE [dbo].[user_log] WITH NOCHECK ADD 
	CONSTRAINT [PK_user_log] PRIMARY KEY  CLUSTERED 
	(
		[action_id]
	)  ON [PRIMARY] 
GO

SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

CREATE FUNCTION [dbo].[DecToBin] (@Number BIGINT, @Length TINYINT)
RETURNS CHAR(20) AS
BEGIN
	DECLARE @Result CHAR(20)
	SET @Result = ''
	WHILE NOT @Number = 0
		BEGIN
			SET @Result = SUBSTRING('01', (@Number % 2) + 1, 1) + @Result
			SET @Number = floor(@Number / 2)
		END
	SET @Result = REPLICATE('0', @Length - LEN(@Result)) + @Result
	Return(@Result)
END


GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

CREATE FUNCTION ValidateInput (@INPUT varchar(24))  
RETURNS INT AS  
BEGIN 

DECLARE @position INT
DECLARE @code INT
SET @position = 1
WHILE @position <= LEN(@INPUT)
BEGIN
     	SET @code = ASCII(SUBSTRING(@INPUT, @position, 1))
	IF (@code >= 65 AND @code <= 90) OR (@code >= 97 AND @code <=122) 
	OR (@code >= 48 AND @code <= 57)
	OR @code = 32 OR @code = 45 OR @code = 95
		SET @code = 0 --Just wasting a block
	ELSE
		BEGIN
			RETURN 0
		END
     	SET @position = @position + 1
END
RETURN 1
END
GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS OFF 
GO

CREATE FUNCTION [dbo].[fn_md5] (@string VARCHAR(8000))
RETURNS CHAR(32) AS
BEGIN
  DECLARE @hash CHAR(32)
  EXEC master.dbo.xp_md5 @string, @hash OUTPUT
  RETURN @hash
END
GO
SET QUOTED_IDENTIFIER OFF 
GO
SET ANSI_NULLS ON 
GO

CREATE PROCEDURE CartItemSearch 
@SEARCH_STRING VARCHAR(4)
AS

DECLARE @search varbinary(4000), @search2 varchar(8000)
SET @search = CONVERT(varbinary(4000), @SEARCH_STRING) 
SET @search2 = @search

SELECT     character.dbo.cartItem.GID, character.dbo.charinfo.charname
FROM          character.dbo.cartItem
LEFT JOIN character.dbo.charinfo ON character.dbo.charinfo.GID = character.dbo.cartItem.GID
WHERE      (cartitem LIKE '%' + @search2 + '%')
GO
CREATE PROCEDURE CharItemSearch 
@SEARCH_STRING VARCHAR(4)
AS

DECLARE @search varbinary(4000), @search2 varchar(8000)
SET @search = CONVERT(varbinary(4000), @SEARCH_STRING) 
SET @search2 = @search

SELECT     character.dbo.item.GID, character.dbo.charinfo.charname
FROM          character.dbo.item
LEFT JOIN character.dbo.charinfo ON character.dbo.charinfo.GID = character.dbo.item.GID
WHERE      (equipItem LIKE '%' + @search2 + '%')
GO
CREATE PROCEDURE StorageItemSearch 
@SEARCH_STRING VARCHAR(4)
AS

DECLARE @search varbinary(4000), @search2 varchar(8000)
SET @search = CONVERT(varbinary(4000), @SEARCH_STRING) 
SET @search2 = @search

SELECT     character.dbo.storeitem.AID, nLogin.dbo.login.ID
FROM          character.dbo.storeitem
LEFT JOIN nLogin.dbo.login ON nLogin.dbo.login.AID = character.dbo.storeitem.AID
WHERE      (storedItem LIKE '%' + @search2 + '%')
GO