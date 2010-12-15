=== Readable Names ===
Contributors: doktorbro
Tags: comments, discussion, etiquette, grammar, language, readability, spam
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.0.4

Asks commenters to write their names in the language that your blog uses.

== Description ==

This plugin asks commenters to write their names in the language that your blog uses. Using readable names written in your blog's native language will enhance the discussion. Strangers who want to leave comments on *your* blog, have to speak *your* language.

Customizing predefined rules can create your own slang.

Less spam is a welcome side effect on non-Latin alphabets.

= Translations =

The plugin comes with a set of predefined standards for every translated language.

* Bulgarian `bg_BG`
* Finnish `fi` (by Christian Hellberg)
* German `de_DE`
* Hebrew `he_IL` (by [Yaron Shahrabani](https://edge.launchpad.net/~sh-yaron))
* Icelandic `is_IS` (by Hans Rúnar Snorrason) †
* Persian `fa_IR` (by [Alefba](http://alefba.us/)) †
* Russian `ru_RU`

† *incomplete*

= Technical Specifications =

* very lightweight
* fully localized
* secure input validation
* smooth upgrade procedure
* no extra data in the database
* restless deinstallation

== Installation ==

1. Upload the `readable-names` folder to the `/wp-content/plugins/` directory or download through the “Plugins” menu in WordPress.
1. Activate the plugin through the “Plugins” menu in WordPress.
1. Look for the “Settings” link to configure the options.
1. Make your own definitions on the option page.
1. Give visitors a hint about your rules.

== Frequently Asked Questions ==

Please use the [support forum](http://wordpress.org/tags/readable-names)
for problems or questions with this plugin. “Readable Names” topics must be
tagged with `readable-names`.

Support questions by email will be ignored.

= How can I translate the plugin in my native language? =

* Download the [development version](http://downloads.wordpress.org/plugin/readable-names.zip).
* Find the file `readable-names.pot` inside the subfolder `languages`.
* Translate it with the free editor “[Poedit](http://www.poedit.net/)”.
* Contact me through the dedicated [support forum](http://wordpress.org/tags/readable-names). 

= Why should I forbid the use of any foreign alphabet? =

You cannot read foreign alphabet characters. “Доктор Бро” is just not readable
for you, if you don't speak Russian. Other commenters don't expect
foreign alphabets on your site at all.

= Could it happen that ordinary comments get lost? =

No, it's impossible. Comments with ordinary names pass through without any side effect.

== Screenshots ==

1. The comment form gives a decent hint.

2. The error messages are user friendly.

3. The count of unreadable attempts is displayed on the dashboard.

4. The admin option page is clear.

5. The plugin has been translated even into Russian.

== Upgrade ==

Any *previous* settings should be *retained*. Some *new* options will be set to *default* settings.

== Roadmap ==

= 1.1 =

* Add option “hyphen” to allowed characters

= 1.2 =

* Add option “full stop” to allowed characters

== Upgrade Notice ==

= 1.0.2 =

This update makes the plugin compatible to WP 3.1

== Changelog ==

= 1.0.4 =
* Remove whitespace after PHP tags

= 1.0.2 =
* Use a static function in the uninstall hook (WP 3.1 compatible)
* Change Russian translation

= 1.0.1 =
* Add Bulgarian translation

= 1.0 =
* Display count of unreadable attempts on the dashboard
* Change default option “User” to false
* Improve memory usage

= 0.9.3 =
* Fix typos in `readme.txt`

= 0.9.2 =
* Add “Frequently Asked Questions” section
* Add “Upgrade Notice” section

= 0.9.1 =
* Fix Russian typo

= 0.9 =
* Add option “Required vowels”
* Add option “Modify the comment form”

= 0.8 =
* Add Finnish translation (by Christian Hellberg)
* Change Hebrew defaults

= 0.7 =
* Add Hebrew translation (by Yaron Shahrabani)
* Add Icelandic translation (by Hans Rúnar Snorrason)
* Delete Russian letters “Ь” and “Ъ” from the default settings 

= 0.6.1 =
* Fix a bug: not recognise the first letter of the alphabet

= 0.6 =
* Better error style
* Added section “Affected users” with options “User” and “Visitor” 
* Added the link to support forum
* Removed option “required_letters”

= 0.5.1 =
* Change default multibyte encoding to UTF-8

= 0.5 =
* Add Farsi support
* Change plugin activation

= 0.4.3 =
* Wrong check for allowed characters
* Change the message for invalid character

= 0.4.2 =
* Add text domain to error messages

= 0.4.1 =
* Typo in German and Russian translation

= 0.4 =
* Add German translation
* Change the plugin description

= 0.3.2 =
* Wrong text domain

= 0.3.1 =
* Delete temporary file
* Change description

= 0.3 =
* Add Russian translation

= 0.2 =
* Add screenshots
* Change plugin author name

= 0.1.1 =
* Add contributors to `readme.txt`

= 0.1 =
* Initial version
