.. include:: ../Includes.txt

.. _troubleshooting:

===============
Troubleshooting
===============

Things go wrong and you don't know why? Maybe this troubleshooting guide knows!

.. contents:: Table of Contents
   :depth: 1
   :local:

Error in backend after upgrading Mask
=====================================

In some version upgrades Mask changes things, where a simple cache clearing is
not enough. First of all try clearing the cache in **Maintenance > Flush Cache**.
If you can't access the module, delete the folder `typo3temp` or `var/cache`.
Also try to :ref:`deactivate and reactivate the extension <upgrade-from-6>` if
you have upgraded to v7.

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

.. _row-size-too-large:

On save error: Row size too large
=================================

Explanation
-----------

There is a limit on how much can fit into a database row. When using overflow tables it is usually `64kb <https://mariadb.com/kb/en/innodb-system-variables/#innodb_page_size>`__.
As Mask uses the table `tt_content`, we can only work with the maximum size minus TYPO3 core fields (~9500 bytes).
That leaves us with ~56kb. When using a utf-8 collation like `utf8_general_ci`, one character has the maximum size of 3 bytes.
Meaning the maximum extra :ref:`string <fields-string>` fields are **73** (`73 * 255 bytes * 3 = 55845 bytes`).

.. note::

   The number can vary depending on installed system and third-party extensions.

Other field types like int (4 bytes), mediumtext (3 bytes) and text (2 bytes) are very small in comparison. The reason
text fields are so small is that they are always stored on overflow pages. Read `this mariadb guide <https://mariadb.com/kb/en/troubleshooting-row-size-too-large-errors-with-innodb/>`__
for in depth explanation.

Solutions
---------

* Try to minimize the usage of :ref:`string <fields-string>`, :ref:`link <fields-link>` and :ref:`select <fields-select>` fields. They all use `varchar(255)`.

* If possible, reuse existing TYPO3 core and Mask fields.

* You can manipulate mask.json and set lower max values for varchar.

* If applicable, use :ref:`inline <fields-inline>` fields, as they create a new table.

* Otherwise consider creating an own extension with custom tables if your Mask elements are getting too complex.
