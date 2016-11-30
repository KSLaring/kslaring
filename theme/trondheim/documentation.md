## Theme trondheim
## KommIT
# Technical Details

eFaktor AS, Brubakken 2,  2615 Lillehammer
Tlf: 41 42 15 30
E-post: post@efaktor.no

Author: Urs Hunkler


## Introduction
The theme is a child theme of the »kommit« theme.

The theme adds an event handler for the **user_created** event to set the user preferences for the »navigation« and »settings« blocks to collapsed. The preferences are only set when the »trondheim« theme is activated.


## Events
The theme activates an event observer for the **user_created** event. When a new user is created and the »user_created« event is triggered, the user's preferences to collapse the »navigation« and »settings« block are created. The user sees the  two blocks **collapsed** until she extends it.

## Requirements
Themes: bootstrapbase, kommit

## Database
No database needed.

## Interfaces
No interface involved

## Libraries
Several functions in lib.php.

## Renderers
Several core renderer functions are overridden.

## JavaScript
No JavaScript involved.
