Feature: Test the wsuwp site command

  Scenario: WSUWP site command exists
    Given a WSUWP Platform install

    When I run `wp wsuwp site`
    Then STDOUT should contain:
      """
      usage: wp wsuwp site create <site-url> <wsu-nid> <site-name> <network-id> [--porcelain]
      """

  Scenario: A complete wsuwp site create command is issued
    Given a WSUWP Platform install

    When I run `wp wsuwp site create crimsonpages.org/jeremy-felt jeremy.felt@wsu.edu "Jeremy Felt's Portfolio" 1`
    Then STDOUT should contain:
      """
      Created site
      """

    When I try the previous command again
    Then the return code should be 1
    Then STDERR should contain:
      """
      crimsonpages.org/jeremy-felt is already a site
      """

  Scenario: A site URL with multiple paths is rejected
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages.org/jeremy-felt/second jeremy.felt@wsu.edu "Site 2" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      A site can only have one path
      """

  Scenario: A site URL with an invalid domain
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages/jeremy-felt jeremy.felt@wsu.edu "Site Invalid" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      'crimsonpages' is not a valid domain.
      """

  Scenario: A site URL with an invalid path
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages.org/jeremy.felt jeremy.felt@wsu.edu "Site Invalid" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      'jeremy.felt' is not a valid path.
      """
