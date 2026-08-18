*** Settings ***
Documentation    Public music search UI automation using deterministic testing data.
Variables        ../../variables/test.py
Resource         ../../resources/pages/search_page.resource
Suite Setup      Configure Search Test Suite
Test Setup       Open Test Browser
Test Teardown    Close Test Browser
Suite Teardown   Close Test Browser


*** Test Cases ***
S01 Open Search Without Query
    [Tags]    ui    search    smoke
    Open Search Page
    Search Initial State Should Be Visible

S02 Search With Only Whitespace
    [Tags]    ui    search    boundary
    Open Search Page
    Search For Music    ${SPACE}${SPACE}${SPACE}${SPACE}${SPACE}
    Search Initial State Should Be Visible

S03 Search Exact Song Title
    [Tags]    ui    search    positive
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_SONG_ALPHA}
    Song Search Results Should Contain    ${ROBOT_SEARCH_SONG_ALPHA}

S04 Search Partial Song Title
    [Tags]    ui    search    positive
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_PARTIAL}
    Song Search Results Should Contain    ${ROBOT_SEARCH_SONG_ALPHA}

S05 Search Song With Different Letter Case
    [Tags]    ui    search    positive
    Open Search Page
    Search For Music    robot search song alpha
    Song Search Results Should Contain    ${ROBOT_SEARCH_SONG_ALPHA}

S06 Search Existing Artist
    [Tags]    ui    search    positive
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_ARTIST}
    Artist Search Results Should Contain    ${ROBOT_SEARCH_ARTIST}

S07 Search Existing Album
    [Tags]    ui    search    positive
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_ALBUM}
    Album Search Results Should Contain    ${ROBOT_SEARCH_ALBUM}    ${ROBOT_SEARCH_ARTIST}

S08 Search Nonexistent Keyword
    [Tags]    ui    search    negative
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_NO_RESULT}
    Search Should Show No Results

S09 Open Song From Search Result
    [Tags]    ui    search    navigation
    Open Search Page
    Search For Music    ${ROBOT_SEARCH_SONG_ALPHA}
    Open Song From Search Results    ${ROBOT_SEARCH_SONG_ALPHA}


*** Keywords ***
Configure Search Test Suite
    Set Screenshot Directory    ${EXECDIR}/tests/robot/results/screenshots
