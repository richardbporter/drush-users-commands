# drush-users-commands
Drush commands to interact with multiple Drupal users.

## Requirements
- Drush 9
- Drupal 8

## Installation
`composer require richardbporter/drush-users-commands`

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
`drush users:toggle` will unblock admin, bar and baz but foo and qux stay
blocked since that was their previous status.

Aliases: utog