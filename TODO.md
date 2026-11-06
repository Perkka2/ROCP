# To do

## Aegis (twRO EP17.1 servers, ASB7 Beta 2 and later)

The twRO servers keep these in the database, but on twRO they were filled by the web shop and other web tools,
and nothing in the game fills them. Until the panel can, a GM has to set them by hand in SQL.

- **Mobile events**: twRO's mobile app/event rewards (the mobile event table the Character server reads). Only if
  there is a use for it; otherwise leave empty.

Done in 5.7.0: cash shop points (Cash Points, `CashItemShop.dbo.TwCash`) and account mail (Account Mail,
`GlobalInfo.dbo.RoDex`).
