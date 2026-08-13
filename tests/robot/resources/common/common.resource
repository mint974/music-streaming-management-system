*** Settings ***
Library    SeleniumLibrary    timeout=${DEFAULT_TIMEOUT}


*** Keywords ***
Configure Login Test Suite
    Should Not Be Empty    ${ROBOT_TEST_EMAIL}       ROBOT_TEST_EMAIL must be set.
    Should Not Be Empty    ${ROBOT_TEST_PASSWORD}    ROBOT_TEST_PASSWORD must be set.
    Set Screenshot Directory    ${EXECDIR}/tests/robot/results/screenshots

Open Test Browser
    ${options}=    Evaluate    sys.modules['selenium.webdriver'].ChromeOptions()    sys, selenium.webdriver
    IF    ${HEADLESS}
        Call Method    ${options}    add_argument    --headless\=new
    END
    Call Method    ${options}    add_argument    --window-size\=1440,1000
    Call Method    ${options}    add_argument    --no-sandbox
    Call Method    ${options}    add_argument    --disable-dev-shm-usage
    IF    $CHROMEDRIVER_PATH
        Open Browser    ${BASE_URL}    browser=${BROWSER}    options=${options}    executable_path=${CHROMEDRIVER_PATH}
    ELSE
        Open Browser    ${BASE_URL}    browser=${BROWSER}    options=${options}
    END
    Set Selenium Timeout    ${DEFAULT_TIMEOUT}

Close Test Browser
    Close All Browsers
