## Moodle block for marking your favourite pages as bookmarks

In brief, the ldesignmedia block `user_favorites` gives the user a method to mark pages as favourite.

Special thanks to Gemma Lesterhuis ([Lesterhuis Training & Consultancy](https://ltnc.nl/)) for develop & design, useful
input, bug reports and beta testing

![ldesignmedia.nl](https://ldesignmedia.nl/logo_small.png)
![Lesterhuis Training & Consultancy](https://ldesignmedia.nl/logo_small_ltnc.png)

* Author: Luuk Verhoeven, [ldesignmedia.nl](https://ldesignmedia.nl/)
* Author: Gemma Lesterhuis, [Lesterhuis Training & Consultancy](https://ltnc.nl/)
* Min. required: Moodle 4.2
* Supports PHP:  7.4

![Moodle402](https://img.shields.io/badge/moodle-4.2-brightgreen.svg)
![Moodle403](https://img.shields.io/badge/moodle-4.3-brightgreen.svg)
![Moodle404](https://img.shields.io/badge/moodle-4.4-brightgreen.svg)

![PHP74](https://img.shields.io/badge/php-7.4-teal.svg)
![PHP80](https://img.shields.io/badge/php-8.0-teal.svg)
![PHP81](https://img.shields.io/badge/php-8.1-teal.svg)

## Screens
![10 27 2018-12 40](https://github.com/Lesterhuis-Training-en-Consultancy/moodle-block-user_favorites/assets/995760/2f2c2157-dbfa-4a17-9c5b-cd77ba55070c)

## List of features
- Using external AJAX requests for saving loading and ordering user favourites.
- Using mustache templates.
- Fast and easy to work with.
- Marks current page in favorites if exists.
- Allow marking as favorite the url link with `#hash` (In case of marking multiple links for the same page, A web page reload needed before).
- Make use of external API web services as in the tracker MDL-76583.

## Installation

1. Copy this plugin to the `blocks\user_favorites` folder on the server
2. Login as administrator
3. Go to Site Administrator > Notification
4. Install the plugin


## Changelog

See [Changelog](CHANGELOG.md) file for details

## Security

If you discover any security related issues, please email [luuk@ldesignmedia.nl](mailto:luuk@ldesignmedia.nl) instead of using the
issue tracker.

## License

The GNU GENERAL PUBLIC LICENSE. Please see [License File](LICENSE) for more information.

## Contributing

Contributions are welcome and will be fully credited. We accept contributions via Pull Requests on Github.

- Thanks for the inspiration [block_user_bookmarks](https://moodle.org/plugins/block_user_bookmarks)
- Thank you to @stopfstedt, 2gjb2048, and @SashaAnastasi for your valuable contributions to enhancing the plugin.
