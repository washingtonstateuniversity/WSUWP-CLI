Feature: Test the wsuwp command

  Scenario: The wsuwp command exists
    Given a WSUWP Platform install

    When I run `wp wsuwp`
    Then STDOUT should contain:
      """
      usage: wp wsuwp
      """
