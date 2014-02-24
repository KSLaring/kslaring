YUI plugins used in themes are saved centrally in the "local/themes/yui/src/pluginname/" directory. They are created and need to be maintained the Moodle way with "shifter".

Mainly four components are relevant for the YUI theme plugins:
1. the YUI plugin code
2. the Reference to the YUI plugin to have Moodle load it
3. the plugin styles
4. the language strings

1.) The plugin code just stays where it is and can be called form everywhere. Moodle handles the loading.
2.) The YUI plugin needs to be included from "themename/lib.php"  (See how "scrollyanchorsplugin" is included as an example).
3.) The less file for the plugin needs to be copied to the "less/theme/plugins/" directory and the files needs then to be included in the "less/theme/plugins.less" file.
4.) The lang strings used by the plugins are saved in the "local/themes/lang" files.
