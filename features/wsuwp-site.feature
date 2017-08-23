Feature: Test the wsuwp site command

  Scenario: WSUWP site command exists
    Given a WSUWP Platform install

    When I run `wp wsuwp site`
    Then STDOUT should contain:
      """
      usage: wp wsuwp site create <site-url> <admin-email> <site-name> <network-id> [--porcelain]
      """

  Scenario: A complete wsuwp site create command is issued
    Given a WSUWP Platform install

    When I run `wp wsuwp site create crimsonpages.org/jeremy-felt wsu.admin@wsu.edu "Jeremy Felt's Portfolio" 1`
    Then STDOUT should contain:
      """
      Created site
      """

    When I try the previous command again
    Then the return code should be 1
    Then STDERR should contain:
      """
      A site with this domain and path combination already exists.
      """

  Scenario: A site URL with multiple paths is rejected
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages.org/jeremy-felt/second wsu.admin@wsu.edu "Site 2" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      A site can only have one path.
      """

  Scenario: A site URL with an invalid domain
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages/jeremy-felt wsu.admin@wsu.edu "Site Invalid" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      'crimsonpages' is not a valid domain.
      """

  Scenario: A site URL with an invalid path
    Given a WSUWP Platform install

    When I try `wp wsuwp site create crimsonpages.org/jeremy.felt wsu.admin@wsu.edu "Site Invalid" 1`
    Then the return code should be 1
    Then STDERR should contain:
      """
      Invalid site path. Non standard characters were found in the path name.
      """
