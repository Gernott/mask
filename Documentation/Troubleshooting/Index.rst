.. include:: ../Includes.txt

.. _troubleshooting:

===============
Troubleshooting
===============

Things go wrong and you don't know why? Maybe this troubleshooting guide knows!

Database Analyzer: Invalid default value
========================================

If you run the database analyzer and it complains about a column having an
invalid default value then you should check a few things.

SQL_MODE
--------

First check your :sql:`SQL_MODE` by running :sql:`SHOW VARIABLES LIKE 'SQL_MODE';`.
There are some candidates that cause trouble when having a too strict
environment in a MySQL server.
These are for example :sql:`text` types, which might disallow default values per
se or :sql:`date` types which might disallow `0000-00-00` values as default.

In order to fix this, remove the restrictions that cause this to happen. For
dates it is :sql:`NO_ZERO_DATE` for example. Or just sweep them altogether by
running :sql:`SET GLOBAL SQL_MODE = '';`.

Update tables
-------------

Else it just might be you are changing a fields definition when it's already
filled with values. In this case either completely delete the column and run
the database analyzer anew or update the old default values with the new one.
For example :sql:`UPDATE tt_content SET tx_mask_field = 0 WHERE tx_mask_field IS NULL`.
