## YUI module "lightbox"
## KommIT
# Technical Details

eFaktor AS, Brubakken 2,  2615 Lillehammer
Tlf: 41 42 15 30
E-post: post@efaktor.no

Author: Urs Hunkler


## Introduction
The "lightbox" YUI module opens a link in a modal dialogue in an iframe.

The YUI module is compiled with the standard Moodle YUI node script "shifter".

## Requirements
### Load the module
# The YUI lightbox module must be loaded from Moodle pages which use the lightbox.
$page->requires->yui_module(
    array('moodle-local_lightbox-lightbox'),
    'M.local_lightbox.lightbox.init_lightbox',
    array());

## Database
No database needed.

## Interfaces
No interface involved

## Libraries
No libraries involved

## Renderers
No renderers involved

## JavaScript
The source JavaScript file: "local/lightbox/yui/src/lightbox/js/lightbox.js".

For details please check the method documentation in the JS file.
