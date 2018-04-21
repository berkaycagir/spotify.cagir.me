# Spotify Song Availability Checker

This is the https://spotify.cagir.me's repository. This service checks Spotify's song availabilities in different countries, using the [Spotify Web API](https://beta.developer.spotify.com/documentation/web-api/).

## Dependencies

The site runs with the listed components, so at the base you would require these:

* Python 2.7.3 with [requests](https://github.com/requests/requests)
* PHP 5.6.35
* a webserver (originally nginx is being used)
* a scheduled command daemon (originally crontab is being used)
* a mail account with SMTP access

## Installation

1. Clone the repository to your computer (the default location is `/var/www/spotify.cagir.me`).
2. Enter the required data to `checker_config.py` and `site/config.php`. To obtain Spotify's tokens, you have to go through their authorization process, outlined [here](https://beta.developer.spotify.com/documentation/general/guides/authorization-guide/).
3. Configure your webserver. For nginx, a standard PHP application config would be fine. The site's root directory should be `site`, **take care to not expose your Spotify tokens or mail address information to public!**
4. Configure your scheduled command daemon to run `check.py` with the desired interval.
5. Done!

## Data persistence

Python's [pickle](https://docs.python.org/2/library/pickle.html) module is being used for data persistence. If you want to, you can totally change it with a database or even plain file too.

## Contribution

The project is open to pull requests. Except for installing `requests`, there is no environment preparation or something. Just fork, clone, modify!

## License

You can do whatever you want under the GNU GPLv3. See the `LICENSE` file for more information.
