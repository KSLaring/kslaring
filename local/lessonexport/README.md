Lesson export
===========

This plugin adds the ability to export Moodle lessons as PDF documents.
Many thanks to Davo Smith for developing the original base-code this plugin was ported from.

Usage
=====

Once the plugin is installed, you can visit a lesson, then click on the new 'Export as PDF' link that appears
in the activity administration block (with javascript enabled, similar links are inserted on the top-right corner of the page).

You may also add page-breaks in the content by giving any element (in HTML) the 'pagebreak' class. This will remove the element from the content that is used as a break so be careful here. Regular expressions are used to find the pagebreak elements and as such, it's more preferable to place these on self-closing tags. If not used on self-closing tags, such as `<br class="pagebreak" />`, then be careful not to place content inside of those elements as the trailing close tag will not be matched.

When editing footer content in the Lesson Export settings, there are several shortcodes you may use to add useful information to the
document.

|   Shortcode  	| Output                                          	| Notes                                                          	|
|:------------:	|-------------------------------------------------	|----------------------------------------------------------------	|
| [pagenumber] 	| The number of the page, per-page.               	| This is a single integer, without additional characters.       	|
|    [date]    	| The current date at the point of the export.    	| In the format "dd M YYYY". For example; 27 February 2017.      	|
| [coursename] 	| The name of the course, as-is listed on Moodle. 	| Printed without any additional characters such as punctuation. 	|
| [lessonname] 	| The name of the lesson, as-is listed on Moodle. 	| Printed without any additional characters such as punctuation. 	|

Settings
========

There are many globally configurable options for exporting PDF documents from a lesson including;

- Strict mode - Throw exceptions on content errors
- Cover photo banner colour
- Configurable PDF protection settings
- A user password
- A owner password
- A font family
- Configurable footer content

Customising
===========

If you want to customise the button that exports documents, please use the class `exportpdf` for custom css. The outer `div` element with
the class `exportpdf` is the container for an `a` element, which is the export link.

If you want to add your organisation's logo to the front page of the exported lesson, please replace the file
local/lessonexport/pix/logo.png with your logo. Do not alter the file dimensions, it must remain 514 by 182 pixels.

Customise the following language strings, to alter the embedded export information:
'printed' - set the description on the front page 'This doucment was downloaded on [date]'

(see https://docs.moodle.org/en/Language_customization for more details)

Disclaimer
=======

This repository is provided as-is and is open to pull requests or issues.
It was developed by Adam King for use at [SHEilds Health & Safety](http://sheilds.org/). Any development going forwards will be to abstract
the plugin to match many use-cases. Please be aware that security applied to the PDF documents will not be effective if you also use the
EPUB exporter for Lesson modules as EPUB has no standard security measures available at the time of development.