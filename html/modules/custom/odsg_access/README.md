ODSG Access
===========

Define permissions to **view** content types per bundle.

This module denies **view** access to node pages for users without the
corresponding `Node Bundle: view published content` permission.

To work, the `view published content` must be checked for anonymous and
authenticated users (otherwise they are already denied access).

This modules only deals with **published** content. Access to unpublished
content is managed by the `view unpubished content` permission independently
of this module.

Setup
-----

1. Enable the module.
2. Check `view published content` for anonymous and authenticated users.
3. Set granular `Node Bundle: view published content` for each node types.
