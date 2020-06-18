ODSG OCHA
=========

This module provides basic integration of the UN OCHA feeds through a views
query plugin.

As the feed data is really basic without possibility to control the number of
elements they contain nor sort them or filter them, OCHA feed views should
not set any `filter` or `sort` options.

To select the feed to use in a block/page, open the advanced settings for the
display and select "query settings" then the feed to use.

Available fields are:

- `td`: node id on https://www.unocha.org
- `title`: title of the document
- `updated`: timestamp of the last update of the document
- `file`: URL of the first attachment of the document
- `preview`: URL of the cover preview of the attachment
