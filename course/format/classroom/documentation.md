## Course format “classroom”
## KommIT
# Technical Details

eFaktor AS, Brubakken 2,  2615 Lillehammer
Tlf: 41 42 15 30
E-post: post@efaktor.no


## Introduction
The KommIT online course format influenced by the https://www.edx.org online course format.

The course offers a main “navigation bar” with some main navigation buttons at the top, a navigation menu to the left and the section description or activity content to the right.

When a user clicks on the course link on her “my” page she is redirected to the last visited course section or activity.

## Requirements
### Course settings
The “Course layout” setting must be set to “Show one section per page”, else the “course_navigation” block will not show the menu.

### Hide the blocks “Navigation” and “Administration” for students
To hide the “Navigation” and “Administration” block for students it is necessary to change the block rights. The steps to change the rights are:

1. Turn course editing on.
2. Select the  “Assign roles in Navigation Block” menu item in the block “Actions” menu.
3. Select the “Permissions” link in the “Administartion” block on the opened “Assign roles in Block: Navigation” page.
4. Remove the “Student” role and all roles with lower rights for the “View block” (moodle/block:view) capability.

Repeat the same steps for the “Administration” block.

## Database
No database needed.

## Block “course_navigation”
Files
```
classroom/
```

## Interfaces
text

## Libraries
text

## Renderers
“renderer.php” with the renderer.

## JavaScript
“format.js” -
