<?php
namespace Drush\Commands\UsersCommands;

use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\user\Entity\User;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Drush\Exceptions\UserAbortException;
use Drush\SiteAlias\SiteAliasManagerAwareInterface;
use Symfony\Component\Console\Input\InputOption;

class UsersCommands extends DrushCommands implements SiteAliasManagerAwareInterface
{
    use SiteAliasManagerAwareTrait;

  /**
   * Display a list of Drupal users.
   *
   * @command users:list
   * @param array $options An associative array of options.
   * @option status Filter by status of the account. Can be active or blocked.
   * @option roles Filter by accounts having a role. Use a comma-separated list for more than one.
   * @option no-roles Filter by accounts not having a role. Use a comma-separated list for more than one.
   * @option last-login Filter by last login date. Can be relative.
   * @usage users:list
   *   Display all users on the site.
   * @usage users:list --status=blocked
   *   Displays a list of blocked users.
   * @usage users:list --roles=admin
   *   Displays a list of users with the admin role.
   * @usage users:list --last-login="1 year ago"
   *   Displays a list of users who have logged in within a year.
   * @aliases ulist, user-list, list-users
   * @bootstrap full
   * @field-labels
   *   uid: User ID
   *   name: User name
   *   pass: Password
   *   mail: User mail
   *   theme: User theme
   *   signature: Signature
   *   signature_format: Signature format
   *   user_created: User created
   *   created: Created
   *   user_access: User last access
   *   access: Last access
   *   user_login: User last login
   *   login: Last login
   *   user_status: User status
   *   status: Status
   *   timezone: Time zone
   *   picture: User picture
   *   init: Initial user mail
   *   roles: User roles
   *   group_audience: Group Audience
   *   langcode: Language code
   *   uuid: Uuid
   * @table-style default
   * @default-fields uid,name,mail,roles,status,login
   * @throws \Exception
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
    public function listAll($options = ['status' => InputOption::VALUE_REQUIRED, 'roles' => InputOption::VALUE_REQUIRED, 'no-roles' => InputOption::VALUE_REQUIRED, 'last-login' => InputOption::VALUE_REQUIRED])
    {
        // Use an entityQuery to dynamically set property conditions.
        $query = \Drupal::entityQuery('user')
            ->condition('uid', 0, '!=');

        if (isset($options['status'])) {
            $query->condition('status', $options['status'], '=');
        }

        if (isset($options['roles'])) {
            $query->condition('roles', $options['roles'], 'IN');
        }

      if (isset($options['no-roles'])) {
        $query->condition('roles', $options['no-roles'], 'NOT IN');
      }

        if (isset($options['last-login'])) {
            $timestamp = strtotime($options['last-login']);
            $query->condition('login', 0, '!=');
            $query->condition('login', $timestamp, '>=');
        }

        $ids = $query->execute();

        if ($users = User::loadMultiple($ids)) {
            $rows = [];

            foreach ($users as $id => $user) {
                $rows[$id] = $this->infoArray($user);
            }

            $result = new RowsOfFields($rows);
            $result->addRendererFunction(function ($key, $cellData, FormatterOptions $options) {
                if (is_array($cellData)) {
                    return implode("\n", $cellData);
                }
                return $cellData;
            });

            return $result;
        } else {
            throw new \Exception(dt('No users found.'));
        }
    }

    /**
     * @hook validate users:list
     *
     * @param \Consolidation\AnnotatedCommand\CommandData $commandData
     * @return \Consolidation\AnnotatedCommand\CommandError|null
     *
     * @throws \Exception
     */
    public function validateList(CommandData $commandData)
    {
        $input = $commandData->input();

        $options = [
            'blocked',
            'active',
        ];

        if ($status = $input->getOption('status')) {
            if (!in_array($status, $options)) {
                throw new \Exception(dt('Unknown status @status. Status must be one of @options.', [
                    '@status' => $status,
                    '@options' => implode(', ', $options),
                ]));
            }

            // Set the status to the key of the options array.
            $input->setOption('status', array_search($status, $options));
        }

        // Set the roles option to an array but validate each one exists.
        if ($roles = $input->getOption('roles')) {
            $roles = explode(',', $roles);
            $actual = user_roles(true);
            $rids = [];

            // Throw an exception for non-existent roles.
            foreach ($roles as $role) {
                if (!isset($actual[$role])) {
                    throw new \Exception(dt('Role @role does not exist.', [
                      '@role' => $role
                    ]));
                }
            }

            $input->setOption('roles', $roles);
        }

        // Validate the last-login option.
        if ($last = $input->getOption('last-login')) {
            if (strtotime($last) === false) {
                throw new \Exception(dt('Unable to convert @last to a timestamp.', [
                    '@last' => $last,
                ]));
            }
        }
    }

    /**
     * Block and unblock users while keeping track of previous state.
     *
     * @command users:toggle
     * @usage users:toggle
     *   Block/unblock all users on the site. Based on previous state.
     * @aliases utog
     * @bootstrap full
     */
    public function toggle()
    {
        // Get all users.
        $ids = \Drupal::entityQuery('user')
            ->condition('uid', 0, '!=')
            ->execute();

        if ($users = User::loadMultiple($ids)) {
            // The toggle status is determined by the last command run.
            $status = \Drupal::state()->get('utog_status', 'unblocked');
            $previous = \Drupal::state()->get('utog_previous', []);

            $this->logger()->notice(dt('Toggle status: @status', [
                '@status' => $status
            ]));

            if ($status == 'unblocked') {
                if (\Drupal::configFactory()->getEditable('user.settings')->get('notify.status_blocked')) {
                    $this->logger()->warning(dt('Account blocked email notifications are currently enabled.'));
                }

                $block = [];

                foreach ($users as $user) {
                    $name = $user->getAccountName();

                    if ($user->isActive() == false) {
                        $previous[] = $name;
                    } else {
                        $block[] = $name;
                    }
                }

                $block_list = implode(', ', $block);

                if (!$this->io()->confirm(dt(
                    'You will block @names. Are you sure?',
                    ['@names' => $block_list]
                ))) {
                    throw new UserAbortException();
                }

                if (Drush::drush($this->siteAliasManager()->getSelf(), 'user:block', [$block_list])->mustRun()) {
                    \Drupal::state()->set('utog_previous', $previous);
                    \Drupal::state()->set('utog_status', 'blocked');
                }
            } else {
                if (\Drupal::configFactory()->getEditable('user.settings')->get('notify.status_activated')) {
                    $this->logger()->warning(dt('Account activation email notifications are currently enabled.'));
                }

                if (empty($previous)) {
                    $this->logger()->notice(dt('No previously-blocked users.'));
                } else {
                    $this->logger()->notice(dt('Previously blocked users: @names.', ['@names' => implode(', ', $previous)]));
                }

                $unblock = [];

                foreach ($users as $user) {
                    if (!in_array($user->getAccountName(), $previous)) {
                        $unblock[] = $user->getAccountName();
                    }
                }

                $unblock_list = implode(', ', $unblock);

                if (!$this->io()->confirm(dt('You will unblock @unblock. Are you sure?', ['@unblock' => $unblock_list]))) {
                    throw new UserAbortException();
                }

                if (Drush::drush($this->siteAliasManager()->getSelf(), 'user:unblock', [$unblock_list])->mustRun()) {
                     \Drupal::state()->set('utog_previous', []);
                    \Drupal::state()->set('utog_status', 'unblocked');
                }
            }
        }
    }

    /**
     * A flatter and simpler array presentation of a Drupal $user object.
     *
     * @param \Drupal\user\Entity\User $account
     *   A user account.
     *
     * @return array
     */
    protected function infoArray($account)
    {
        /** @var \Drupal\Core\DateTime\DateFormatter $date_formatter */
        $date_formatter = \Drupal::service('date.formatter');

        return [
            'uid' => $account->id(),
            'name' => $account->getAccountName(),
            'pass' => $account->getPassword(),
            'mail' => $account->getEmail(),
            'user_created' => $account->getCreatedTime(),
            'created' => $date_formatter->format($account->getCreatedTime()),
            'user_access' => $account->getLastAccessedTime(),
            'access' => $date_formatter->format($account->getLastAccessedTime()),
            'user_login' => $account->getLastLoginTime(),
            'login' => $date_formatter->format($account->getLastLoginTime()),
            'user_status' => $account->get('status')->value,
            'status' => $account->isActive() ? 'active' : 'blocked',
            'timezone' => $account->getTimeZone(),
            'roles' => $account->getRoles(),
            'langcode' => $account->getPreferredLangcode(),
            'uuid' => $account->uuid->value,
        ];
    }
}
