OCHA Common Design sub theme for the Drupal 8 ODSG site
=======================================================

See below for [generic information](#ocha-common-design-sub-theme-for-drupal-8)
about the OCHA Common Design sub theme.

Requirements
------------

The customizations for the ODSG site require the installation of the
[components](https://www.drupal.org/project/components) drupal module.

Issues
------

The heading hierarchy is "incorrect" because the user menu at the top has a `<h2>`
title while the `<h1>` appears later in the page.

For ODSG, currently the `<h1>` is the node or page title (not the site name in
the header), which works fine because the homepage is a node with its own title
corresponding to the site name. This is more "problematic" for other sites like
https://reliefweb.int where the homepage is a series of sections and having the
site logo/name as `<h1>` makes more sense. There is an open discussion about that
[here](https://humanitarian.atlassian.net/browse/WHD-2), though the problem with
the heading in the user menu is still an issue in terms of hierarchy.

Reference: https://www.w3.org/WAI/tutorials/page-structure/headings/

Notes
-----

Ensure `site_name` is selected in `/admin/structure/block/manage/sitebranding`
so that it's available in the `system-branding` block.

Customizations
--------------

The list below contains additions to the default common design subtheme:

**Base styling**

- [Blocks](sass/components/_blocks.css)

  Styling for the blocks.

- [Forms](sass/components/_forms.css)

  Styling for the drupal inline forms.

- [Layouts](sass/components/_layouts.css)

  Styling for the layouts.

- [Tables](sass/components/_tables.css)

  Styling for the tables. This is derived from the common_design/cd-table
  component and should be updated if relevant changes were made to the
  cd-table component to ensure consistency.

**Components**

- [components/odsg-announcement](components/odsg-announcement):

  Styling for the global announcement block.

- [components/odsg-donor-ranking-table](components/odsg-donor-ranking-table):

  Styling for the donor ranking tables.

- [components/odsg-hid-login](components/odsg-hid-login):

  Styling for the Log In With Humanitarian ID block.

- [components/odsg-landing-page-link-list](components/odsg-landing-page-link-list):

  Styling for the custom menus on landing pages on ODSG.

- [components/odsg-more-link](components/odsg-more-link):

  Styling for more links displayed on ODSG.

- [components/odsg-publication-list](components/odsg-publication-list):

  Styling for the OCHA publications displayed on ODSG.

**Page styling**

- [pages/odsg-homepage](components/odsg-homepage):

  Specific styling for the homepage.

**Layouts**

- [layouts/twocol_section](layouts/twocol_section):

  Overrides the layout builder two columns section to add margins and use the
  common_design breakpoints.

**Templates**

- [Node title block template](templates/block/block--field-block--node--title.html.twig):

  Block to display a node's title to be used inside layouts. This is currently
  used for the public and private pages in replacement of the `page-title`
  block. Ideally we'd like to use the `page-title` block directly in the layout
  but it's not available (https://www.drupal.org/project/drupal/issues/3029819).

- [Site logo block (system branding)](templates/block/block--system-branding-block.html.twig)

  This block is for the site logo with the link to the homepage. The overrides
  removes the wrapping `h1`.
  In the case of ODSG, the homepage is a node with the site title so we
  don't need to have a `h1` there. Other non-node pages use the `page-title`
  block which uses a `h1` tag as well. So that should be fairly consistent.

- [More link](templates/form/container--more-link.twig):

  Override of the more link template to use the `odsg-more-link` component.

- [OCHA Feeds views template](templates/views/views-view-list--ocha-feeds.html.twig):

  Override of the views list template to use the `odsg-publication-list`
  component for the OCHA Feeds (OCHA publications on ODSG).

- [OCHA Funding views template](templates/views/views-view-table--ocha-funding.html.twig):

  Override of the views table template to use the `odsg-donor-ranking-table`
  component for the OCHA Funding tables.

- [ODSG Landing page links template](templates/views/views-view-list--odsg-landing-page-links.html.twig):

  Override of the views list template to use the `odsg-landing-page-link-list`
  component for the custom menus on landing pages.

- [ODSG Announcement template](templates/views/views-view-unformatted--odsg-announcement.html.twig):

  Override of the views unformatted template to use the `odsg-announcement`
  component for the global announcement block.

- [Image template](templates/field/image.html.twig):

  Override of the image template to ensure there is an `alt` attribute.
  *TODO:* Remove if https://github.com/UN-OCHA/common_design/pull/96 is merged.

**Preprocessors**

- The [common_design_subtheme.theme](common_design_subtheme.theme) file contains
  a fiew preprocess hooks to work with the new components and page styling.

**Overrides**

- Header: [Logos](img/logos)
- Header: [OCHA services](templates/cd/cd-header/cd-ocha.html.twig)
- Footer: [Social menu](templates/cd/cd-footer/cd-social-menu.html.twig)

---

## OCHA Common Design sub theme for Drupal 8

A sub theme, extending [common_design](https://github.com/UN-OCHA/common_design) base theme.

This can be used as a starting point for implementations. Add components, override and extend base theme as needed. Refer to [Drupal 8 Theming documentation](https://www.drupal.org/docs/8/theming) for more.

Clone this repo to /themes/custom/ and rename the folder and associated theme files from
`common_design_subtheme` to your theme name.

### Customise the logo

- Set the logo `logo: 'img/logos/logo.svg'` in the `common_design_subtheme.info.yml` file, and in the `sass/cd-header/_cd-logo.scss` partial override file.
- Adjust the grid column width in `sass/cd-header/_cd-header.scss` partial override file to accommodate the logo.

### Change the path of the libraries

In the `common_design_subtheme.info.yml` change the path of the global style sheet to reflect the new sub theme name.

```
libraries:
- common_design_subtheme/global-styling
```

### Customise the favicon and homescreen icons

Replace the favicon in the theme's root, and the homescreen icons in `img/` with branded versions

### Customise colours

- Change colour-related variable names and values in `sass/cd/_cd_variables.scss` and replace in all references to in partial overrides in `common_design_subtheme/sass/cd/`

### Other customisations

Override sass partials and extend twig templates from the base theme as needed, copying them into the sub theme and linking them using `@import` for sass and `extend` or `embed` for twig templates.

Add new components by defining new libraries in `common_design_subtheme.libraries.yml` and attaching them to relevant templates. Or use existing components from `common_design.libraries.yml` base theme by attaching the libraries to twig template overrides in the sub theme.

Override theme preprocess functions by copying from common_design.theme and editing as needed. For example, if new icons are added, a new icon sprite will need to be generated and the `common_design_preprocess_html` hook used to attach the icon sprite to the page will need a new path to reflect the sub theme's icon sprite location.

Refer to [common_design README](https://github.com/UN-OCHA/common_design/#ocha-common-design-base-theme-for-drupal-8) for general details about base theme and instructions for compilation. There should be no need to compile the base theme, only the sub theme.
