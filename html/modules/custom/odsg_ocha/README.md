ODSG OCHA
=========

This module provides basic integration of the UN OCHA feeds and Contributions
Tracking System (OCT) through views query plugins.

OCHA Feeds
----------

As the feed data is really basic without possibility to control the number of
elements they contain nor sort them or filter them, OCHA feed views should
not set any `filter` or `sort` options.

To select the feed to use in a block/page, open the advanced settings for the
display and select "query settings" then the feed to use.

Available fields are:

- `id`: Node id on https://www.unocha.org
- `link`: Link to the document on https://www.unocha.org
- `title`: Title of the document
- `updated`: Timestamp of the last update of the document
- `file`: URL of the first attachment of the document
- `image`: URL of the cover preview of the attachment or article image

### Administration

The URLs of the feeds used for the views that use this OCHA feeds plugin can
be administered under `/admin/config/user-interface/ocha-feeds` for the roles
with the `administer ocha feeds` permission.

OCHA OCT
--------

As the OCT data is really basic without possibility to control the number of
elements they contain nor sort them, OCHA funding views should
not set any `sort` options and only filtering by `year` is possible.

Available fields are:

- `rank`: Donor rank
- `donor`: Donor name
- `code`: Identifier: iso2 code for countries or some string otherwise
- `earmarked`: Earmarked donations in USD
- `unearmarked`: UnEarmarked donations in USD
- `total`: Total donations in USD
- `year`: Donation year
