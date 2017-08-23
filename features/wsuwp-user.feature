Feature: Test the wsuwp user command

  Scenario: WSUWP user command exists
    Given a WSUWP Platform install

    When I run `wp wsuwp user`
    Then STDOUT should contain:
      """
      usage: wp wsuwp user create <user-email> [--first_name=<first_name>] [--last_name=<last_name>] [--porcelain]
      """

  Scenario: Invalid emails are rejected
    Given a WSUWP Platform install

    When I try `wp wsuwp user create notanemail`
    Then the return code should be 1
    Then STDERR should contain:
      """
      'notanemail' is not a valid email.
      """

  Scenario: Non WSU emails are rejected
    Given a WSUWP Platform install

    When I try `wp wsuwp user create user@gmail.com`
    Then the return code should be 1
    Then STDERR should contain:
      """
      'user@gmail.com' is not a valid WSU email.
      """

  Scenario: Usernames under 3 characters are rejected
    Given a WSUWP Platform install

    When I try `wp wsuwp user create ab@wsu.edu`
    Then the return code should be 1
    Then STDERR should contain:
      """
      Username must be at least 3 characters.
      """

  Scenario: Valid WSU users are created
    Given a WSUWP Platform install

    When I run `wp wsuwp user create valid.user@wsu.edu`
    Then STDOUT should contain:
      """
      Created user
      """

    When I try the previous command again
    Then the return code should be 1
