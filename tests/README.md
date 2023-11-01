# Testing
Since this is a [site-wide Drush command](https://docs.drush.org/en/latest/commands/#site-wide-commands), it will only be found when installed in certain directories. These directories are hard to mimic in the project structure. Therefore, we leverage the `commands` configuration to load the command in the System Under Test (SUT): https://github.com/drush-ops/drush/blob/12.x/src/Runtime/ServiceManager.php#L127

Note that this may be deprecated in the future: https://www.drush.org/12.x/commands/#global-commands-discovered-by-configuration

The example-drush-extension project used the `cwd` enviroment variable for this but that seemingly stopped working in 12.x: https://github.com/drush-ops/example-drush-extension/blob/master/sut/drush/drush.yml

The test bootstrap now writes out the absolute path to the command file in the `drush/drush.yml` file. It does this using the `GITHUB_WORKSPACE` environment variable, mostly for convenience in GitHub Actions CI. This variable must be set in `phpunit.xml` when running the tests locally.
