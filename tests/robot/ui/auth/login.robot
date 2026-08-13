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


*** Test Cases ***
User Logs In With Valid Credentials
    [Tags]    ui    auth    smoke
    Open Login Page
    Enter Login Credentials    ${ROBOT_TEST_EMAIL}    ${ROBOT_TEST_PASSWORD}
    Submit Login
    User Should Be Logged In

User Cannot Log In With Wrong Password
    [Tags]    ui    auth    negative
    Open Login Page
    Enter Login Credentials    ${ROBOT_TEST_EMAIL}    ${WRONG_PASSWORD}
    Submit Login
    Login Should Fail With Invalid Credentials

Unknown User Cannot Log In
    [Tags]    ui    auth    negative
    Open Login Page
    Enter Login Credentials    ${UNKNOWN_EMAIL}    ${WRONG_PASSWORD}
    Submit Login
    Login Should Fail With Invalid Credentials

Email Is Required
    [Tags]    ui    auth    validation
    Open Login Page
    Enter Password    ${WRONG_PASSWORD}
    Submit Login
    Login Should Be Blocked By Required Fields    ${EMAIL_INPUT}

Password Is Required
    [Tags]    ui    auth    validation
    Open Login Page
    Enter Email    ${VALID_FORMAT_EMAIL}
    Submit Login
    Login Should Be Blocked By Required Fields    ${PASSWORD_INPUT}

Email And Password Are Required
    [Tags]    ui    auth    validation
    Open Login Page
    Submit Login
    Login Should Be Blocked By Required Fields
    ...    ${EMAIL_INPUT}
    ...    ${PASSWORD_INPUT}
