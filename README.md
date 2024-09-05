![CI Status](https://github.com/richardbporter/drush-users-commands/actions/workflows/ci.yml/badge.svg)

# drush-users-commands
Drush commands to interact with multiple Drupal users.

## Requirements
- Drush ^12.2
- Drupal ^10.2

## Installation
Since this is a [site-wide Drush command](https://www.drush.org/latest/commands/#site-wide-commands), it will only be found when installed in certain directories. It is recommended to update your Composer installers path for drupal-drush packages to:
 ```
 "drush/Commands/{$name}": ["type:drupal-drush"]
 ```
 Then install it as usual:
 ```
 composer require richardbporter/drush-users-commands
 ```
 Note that the directory the package is installed to (UsersCommands) differs from the repository name (drush-users-commands) due to the [installer name property]( https://github.com/composer/installers#custom-install-names).

## Commands

### drush users:list
List all Drupal users in a table format. See `drush users:list --help`
for filtering options.

Aliases: ulist, user-list, list-users

### drush users:toggle
Block/unblock all users while keeping track of previous state.

For example, say you have the following five users with corresponding
statuses:

- admin -> active
- foo   -> blocked
- bar   -> active
- baz   -> active
- qux   -> blocked

Running `drush users:toggle` will block admin, bar and baz. Running
`drush users:toggle` again will unblock admin, bar and baz but foo and
qux stay blocked since that was their previous status.

Aliases: utog
