## Block “course_navigation”
## KommIT
# Technical Details

eFaktor AS, Brubakken 2,  2615 Lillehammer
Tlf: 41 42 15 30
E-post: post@efaktor.no


## Introduction
The block is based on the Moodle “navigation” block. The menu works the same way. The menu items show only the sections and the modules of the current course.

The block can be used once in a course.
Block settings are automatically changed to show the block as the first block in the column and to show it on the course page and on all module pages.
The following settings are changed

* “weight” and “defaultweight” = “-10”
* “pagetypepattern” = “*”
* “showinsubcontexts” = “1”

## Requirements
The “Course layout” setting must be set to “Show one section per page”, else the block will not show the menu.

## Database
No database needed.

## Block “course_navigation”
Files
```
├── block_course_navigation.php   		// block class
├── db
│   ├── access.php										// block management rights
│   └── upgrade.php										// not used
├── edit_form.php
├── lang
│   └── en
│       └── block_course_navigation.php
├── renderer.php											// navigation tree
├── styles.css
├── documentation.md									// this docu
├── version.php
└── yui
    ├── build
    │   └── moodle-block_course_navigation-course_navigation
    │       ├── moodle-block_course_navigation-course_navigation-debug.js
    │       ├── moodle-block_course_navigation-course_navigation-min.js
    │       └── moodle-block_course_navigation-course_navigation.js
    └── src
        └── course_navigation
            ├── build.json
            ├── js
            │   └── course_navigation.js
            └── meta
                └── course_navigation.json
```

## Interfaces
“edit_form.php” with the same navigation menu settings as the Moodle “navigation” block.

## Libraries
none

## Renderers
“renderer.php” with the navigation tree renderer.

* class: block_course_navigation_renderer
* method: course_navigation_tree(navigation_node $course_navigation, $expansionlimit, array $options = array()) - Returns the content of the course_navigation tree.
* method: course_navigation_node($items, $attrs = array(),$expansionlimit = null, array $options = array(), $depth = 1) - Produces a course_navigation node for the course_navigation tree

## JavaScript
“moodle-block_course_navigation-course_navigation.js” - This file contains the Course navigation block JS.
The same Javascript as the Moodle “navigation” block to handle the navigation tree. Complied with the changed block name.
