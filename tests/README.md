# Testing
Since this is a [site-wide Drush command](https://docs.drush.org/en/master/commands/#site-wide-drush-commands), it will only be
found when installed in certain directories. Cloning this repository into a directory named "Commands/UsersCommands" is the easiest option, e.g. path/to/Commands/UsersCommands. This directory is included when running tests via the `--include` option in TestBase.php.

Instead of creating our own SUT, we piggyback off of Drush's by parsing and installing Drush's dev dependencies using wikimedia/composer-merge-plugin. This was a little quicker to set up and allows us to use Unish internals like `$this->webroot` and others. In order to do this, you must install Drush from source, i.e. `composer install --prefer-source`.
