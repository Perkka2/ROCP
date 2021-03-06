if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[DecToBin]') and xtype in (N'FN', N'IF', N'TF'))
drop function [dbo].[DecToBin]
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

