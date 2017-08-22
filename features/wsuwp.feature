Feature: Test the wsuwp command

  Scenario: The wsuwp command exists
    Given a WP install

    When I run `wp wsuwp`
    Then STDOUT should contain:
      """
      usage: wp wsuwp user <command>
      """
