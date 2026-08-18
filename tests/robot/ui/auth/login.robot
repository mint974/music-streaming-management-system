*** Settings ***
Documentation    User Login UI automation based on the current Laravel login form.
Variables        ../../variables/test.py
Resource         ../../resources/pages/login_page.resource
Suite Setup      Configure Login Test Suite
Test Setup       Open Test Browser
Test Teardown    Close Test Browser
Suite Teardown   Close Test Browser


*** Variables ***
${WRONG_PASSWORD}        NotTheRightPassword!123
${UNKNOWN_EMAIL}         robot-user-that-does-not-exist@example.test
${VALID_FORMAT_EMAIL}    learner@example.test
${INVALID_FORMAT_EMAIL}  not-an-email-address


*** Test Cases ***
User Logs In With Valid Credentials
    [Tags]    ui    auth    smoke
    [Template]    Login With Credentials Should Produce Outcome
    ${ROBOT_TEST_EMAIL}    ${ROBOT_TEST_PASSWORD}    SUCCESS

User Cannot Log In With Wrong Password
    [Tags]    ui    auth    negative
    [Template]    Login With Credentials Should Produce Outcome
    ${ROBOT_TEST_EMAIL}    ${WRONG_PASSWORD}    FAILURE    ${INVALID_CREDENTIALS_TEXT}

Unknown User Cannot Log In
    [Tags]    ui    auth    negative
    [Template]    Login With Credentials Should Produce Outcome
    ${UNKNOWN_EMAIL}    ${WRONG_PASSWORD}    FAILURE    ${INVALID_CREDENTIALS_TEXT}

Locked User Cannot Log In
    [Tags]    ui    auth    negative
    [Template]    Login With Credentials Should Produce Outcome
    ${ROBOT_LOCKED_EMAIL}    ${ROBOT_TEST_PASSWORD}    FAILURE    ${LOCKED_ACCOUNT_TEXT}

Email Is Required
    [Tags]    ui    auth    validation
    Open Login Page
    Enter Password    ${WRONG_PASSWORD}
    Submit Login
    Login Should Be Blocked By Required Fields    ${EMAIL_INPUT_CSS}

Password Is Required
    [Tags]    ui    auth    validation
    Open Login Page
    Enter Email    ${VALID_FORMAT_EMAIL}
    Submit Login
    Login Should Be Blocked By Required Fields    ${PASSWORD_INPUT_CSS}

Email And Password Are Required
    [Tags]    ui    auth    validation
    Open Login Page
    Submit Login
    Login Should Be Blocked By Required Fields
    ...    ${EMAIL_INPUT_CSS}
    ...    ${PASSWORD_INPUT_CSS}

Email Must Have A Valid Format
    [Tags]    ui    auth    validation
    Open Login Page
    Enter Login Credentials    ${INVALID_FORMAT_EMAIL}    ${WRONG_PASSWORD}
    Submit Login
    Login Should Be Blocked By Invalid Email Format
