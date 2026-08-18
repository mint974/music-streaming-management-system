*** Settings ***
Documentation    Premium playlist management UI automation with isolated fixtures.
Variables        ../../variables/test.py
Resource         ../../resources/pages/playlist_page.resource
Suite Setup      Configure Playlist Test Suite
Test Setup       Open Test Browser
Test Teardown    Close Test Browser
Suite Teardown   Close Test Browser


*** Test Cases ***
P01 Guest Cannot Access Playlist Management
    [Tags]    ui    playlist    authorization
    Guest Should Be Redirected To Login

P02 Premium User Sees Empty Playlist State
    [Tags]    ui    playlist    empty
    Login As Premium Robot User    ${ROBOT_PREMIUM_EMPTY_EMAIL}
    Open Playlist Page
    Empty Playlist State Should Be Visible

P03 Create Playlist
    [Tags]    ui    playlist    crud
    Login As Premium Robot User
    Open Playlist Page
    Create Playlist    ${ROBOT_PLAYLIST_CREATE}
    Playlist Should Exist    ${ROBOT_PLAYLIST_CREATE}

P04 Create Playlist With Description
    [Tags]    ui    playlist    crud
    Login As Premium Robot User
    Open Playlist Page
    Create Playlist    ${ROBOT_PLAYLIST_WITH_DESCRIPTION}    ${ROBOT_PLAYLIST_DESCRIPTION}
    Open Playlist    ${ROBOT_PLAYLIST_WITH_DESCRIPTION}
    Playlist Description Should Be    ${ROBOT_PLAYLIST_DESCRIPTION}

P05 Playlist Name Is Required
    [Tags]    ui    playlist    validation
    Login As Premium Robot User
    Open Playlist Page
    Submit Playlist Without Name

P06 Duplicate Playlist Names Are Allowed
    [Tags]    ui    playlist    business-rule
    Login As Premium Robot User
    Open Playlist Page
    Create Playlist    ${ROBOT_PLAYLIST_DUPLICATE}
    Playlist Name Should Appear Twice    ${ROBOT_PLAYLIST_DUPLICATE}

P07 Open Playlist From Index
    [Tags]    ui    playlist    navigation
    Login As Premium Robot User
    Open Playlist Page
    Open Playlist    ${ROBOT_PLAYLIST_OPEN}

P08 Rename Playlist
    [Tags]    ui    playlist    crud
    Login As Premium Robot User
    Open Playlist Page
    Open Playlist    ${ROBOT_PLAYLIST_RENAME}
    Rename Playlist    ${ROBOT_PLAYLIST_RENAMED}

P09 Add Song To Playlist
    [Tags]    ui    playlist    crud
    Login As Premium Robot User
    Open Playlist Page
    Open Playlist    ${ROBOT_PLAYLIST_ADD_SONG}
    Add Song To Playlist    ${ROBOT_SEARCH_SONG_ALPHA}

P10 Remove Song From Playlist
    [Tags]    ui    playlist    crud
    Login As Premium Robot User
    Open Playlist Page
    Open Playlist    ${ROBOT_PLAYLIST_REMOVE_SONG}
    Remove Song From Playlist    ${ROBOT_SEARCH_SONG_ALPHA}
