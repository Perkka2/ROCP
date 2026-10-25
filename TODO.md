# To do

## Aegis (twRO EP17.1 servers, ASB7 Beta 2 and later)

The twRO servers keep these in the database, but on twRO they were filled by the web shop and other web tools,
and nothing in the game fills them. Until the panel can, a GM has to set them by hand in SQL.

- **Cash shop points**: `CashItemShop.dbo.TwCash`, the points the in-game cash shop spends (twRO's web shop sold
  them). A page to view and give points per account (and maybe a log).
- **Account mail**: RoDEX mail to a whole account (all its characters), in `GlobalInfo.dbo.RoDex`. A page to send it,
  with zeny and items, to one account or all accounts.
- **Mobile events**: twRO's mobile app/event rewards (the mobile event table the Character server reads). Only if
  there is a use for it; otherwise leave empty.
