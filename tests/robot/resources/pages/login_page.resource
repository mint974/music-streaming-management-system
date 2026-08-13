*** Settings ***
Resource    ../common/common.resource


*** Variables ***
${LOGIN_PATH}                 /login
${EMAIL_INPUT}                id=email
${PASSWORD_INPUT}             id=password
${EMAIL_INPUT_CSS}            \#email
${PASSWORD_INPUT_CSS}         \#password
${LOGIN_BUTTON}               css=form[action$="/login"] button[type="submit"]
${LOGIN_FORM}                 css=form[action$="/login"]
${VALIDATION_ERROR}           css=.invalid-feedback
${AUTHENTICATED_USER_MENU}     css=button.mm-user-btn
${INVALID_CREDENTIALS_TEXT}   Email hoặc mật khẩu không chính xác.
${LOCKED_ACCOUNT_TEXT}        Tài khoản của bạn đã bị vô hiệu hóa.
${EMAIL_REQUIRED_TEXT}        Vui lòng nhập địa chỉ email.
${PASSWORD_REQUIRED_TEXT}     Vui lòng nhập mật khẩu.


*** Keywords ***
Open Login Page
    Go To    ${BASE_URL}${LOGIN_PATH}
    Wait Until Element Is Visible    ${LOGIN_FORM}

Enter Login Credentials
    [Arguments]    ${email}    ${password}
    Input Text        ${EMAIL_INPUT}       ${email}
    Input Password    ${PASSWORD_INPUT}    ${password}

Enter Email
    [Arguments]    ${email}
    Input Text    ${EMAIL_INPUT}    ${email}

Enter Password
    [Arguments]    ${password}
    Input Password    ${PASSWORD_INPUT}    ${password}

Submit Login
    Click Button    ${LOGIN_BUTTON}

User Should Be Logged In
    Wait Until Location Does Not Contain    ${LOGIN_PATH}
    Wait Until Page Does Not Contain Element    ${LOGIN_FORM}
    Wait Until Element Is Visible    ${AUTHENTICATED_USER_MENU}

Login Should Fail With Invalid Credentials
    Wait Until Location Contains    ${LOGIN_PATH}
    Wait Until Element Is Visible    ${VALIDATION_ERROR}
    Element Should Contain    ${VALIDATION_ERROR}    ${INVALID_CREDENTIALS_TEXT}
    Page Should Not Contain Element    ${AUTHENTICATED_USER_MENU}

Login Should Fail With Message
    [Arguments]    ${expected_message}
    Wait Until Location Contains    ${LOGIN_PATH}
    Wait Until Element Is Visible    ${VALIDATION_ERROR}
    Element Should Contain    ${VALIDATION_ERROR}    ${expected_message}
    Page Should Not Contain Element    ${AUTHENTICATED_USER_MENU}

Login With Credentials Should Produce Outcome
    [Arguments]    ${email}    ${password}    ${expected_outcome}    ${expected_message}=${EMPTY}
    Open Login Page
    Enter Login Credentials    ${email}    ${password}
    Submit Login
    IF    '${expected_outcome}' == 'SUCCESS'
        User Should Be Logged In
    ELSE
        Login Should Fail With Message    ${expected_message}
    END

Login Should Be Blocked By Required Fields
    [Arguments]    @{required_fields}
    Wait Until Location Contains    ${LOGIN_PATH}
    Wait Until Element Is Visible    ${LOGIN_FORM}
    FOR    ${selector}    IN    @{required_fields}
        ${value_missing}=    Execute Javascript
        ...    return document.querySelector(arguments[0]).validity.valueMissing;
        ...    ARGUMENTS
        ...    ${selector}
        Should Be True    ${value_missing}    ${selector} should be rejected by HTML5 required validation.
    END
    Page Should Not Contain Element    ${AUTHENTICATED_USER_MENU}

Login Should Be Blocked By Invalid Email Format
    Wait Until Location Contains    ${LOGIN_PATH}
    Wait Until Element Is Visible    ${LOGIN_FORM}
    ${type_mismatch}=    Execute Javascript
    ...    return document.querySelector(arguments[0]).validity.typeMismatch;
    ...    ARGUMENTS
    ...    ${EMAIL_INPUT_CSS}
    Should Be True    ${type_mismatch}    Email field should reject an invalid email format.
    Page Should Not Contain Element    ${AUTHENTICATED_USER_MENU}
