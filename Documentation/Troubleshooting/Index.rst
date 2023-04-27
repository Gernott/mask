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

On save error: Row size too large (MariaDB)
===========================================

Explanation
-----------

There is a limit on how much can fit into a single InnoDB database row. Read `here <https://mariadb.com/kb/en/innodb-row-formats-overview/#maximum-row-size>`__ for more technical insight.
As Mask uses the table :sql:`tt_content`, it must be ensured, that the table does not grow indefinitely.

Solutions
---------

First, check if you are using the `DYNAMIC row format <https://mariadb.com/kb/en/troubleshooting-row-size-too-large-errors-with-innodb/#converting-the-table-to-the-dynamic-row-format>`__.
If not, alter your tables to use this format, in order to store more data on overflow pages.

.. code-block:: sql

   ALTER TABLE tt_content ROW_FORMAT=DYNAMIC;

Else, here are some tips to save table row size:

* Reuse existing TYPO3 core and Mask fields as much as possible.
* Try to minimize the usage of new :ref:`string <fields-string>`, :ref:`link <fields-link>` and :ref:`select <fields-select>` fields. They all use `varchar(255)`.
* You can manually manipulate your json definitions and change the sql :sql:`varchar` fields to :sql:`text`, as suggested `here <https://mariadb.com/kb/en/troubleshooting-row-size-too-large-errors-with-innodb/#converting-some-columns-to-blob-or-text>`__.
* If applicable, use :ref:`inline <fields-inline>` fields, as they create a new table.
* Otherwise consider creating an own extension with custom tables if your Mask elements are getting too complex.

Read `this mariadb troubleshooting guide <https://mariadb.com/kb/en/troubleshooting-row-size-too-large-errors-with-innodb/>`__ for in depth explanation and more tips.
