[![Build Status](https://travis-ci.org/richardbporter/drush-users-commands.svg?branch=master)](https://travis-ci.org/richardbporter/drush-users-commands)

# drush-users-commands
Drush commands to interact with multiple Drupal users.

## Requirements
- Drush ^9.4
- Drupal 8

## Installation
Since this is a [global Drush command](http://docs.drush.org/en/master/commands/#global-drush-commands), it will only be
found when installed in certain directories. It is recommended to update your Composer installers path for drupal-drush
packages to:
 ```
 "drush/Commands/{$name}": ["type:drupal-drush"]
 ```
 Then install it as usual: 
 ```
 composer require richardbporter/drush-users-commands
 ```

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
