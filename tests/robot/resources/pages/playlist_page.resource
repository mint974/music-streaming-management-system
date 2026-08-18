*** Settings ***
Resource    ../common/common.resource
Resource    login_page.resource


*** Variables ***
${PLAYLIST_PATH}               /listener/playlists
${PLAYLIST_HEADING}            css=h2
${CREATE_BUTTON}               css=button[data-bs-target="#createPlaylistModal"]
${CREATE_MODAL}                id=createPlaylistModal
${CREATE_NAME}                 css=#createPlaylistModal input[name="name"]
${CREATE_DESCRIPTION}          css=#createPlaylistModal textarea[name="description"]
${CREATE_SUBMIT}               css=#createPlaylistModal button[type="submit"]
${EMPTY_STATE}                 css=.row .text-center.py-5
${SUCCESS_ALERT}               css=.alert-success
${DETAIL_TITLE}                css=h1
${DETAIL_DESCRIPTION}          css=h1 + p
${EDIT_BUTTON}                 css=button[data-bs-target="#editPlaylistModal"]
${EDIT_MODAL}                  id=editPlaylistModal
${EDIT_NAME}                   css=#editPlaylistModal input[name="name"]
${EDIT_SUBMIT}                 css=#editPlaylistModal button[type="submit"]
${ADD_SONG_BUTTON}             css=button[data-bs-target="#addSongModal"]
${ADD_SONG_MODAL}              id=addSongModal
${SONG_SEARCH_INPUT}           id=songSearchInput
${SONG_SEARCH_RESULTS}         id=songSearchResults
${SONG_LIST}                   id=sortableList
${REMOVE_SONG_BUTTON}          css=#sortableList button[data-confirm-message]
${CONFIRM_MODAL}               id=globalConfirmModal
${CONFIRM_ACCEPT}              css=#globalConfirmModal [data-confirm-accept]


*** Keywords ***
Configure Playlist Test Suite
    Should Not Be Empty    ${ROBOT_PREMIUM_EMAIL}          ROBOT_PREMIUM_EMAIL must be set.
    Should Not Be Empty    ${ROBOT_PREMIUM_EMPTY_EMAIL}    ROBOT_PREMIUM_EMPTY_EMAIL must be set.
    Should Not Be Empty    ${ROBOT_PREMIUM_PASSWORD}       ROBOT_PREMIUM_PASSWORD must be set.
    Set Screenshot Directory    ${EXECDIR}/tests/robot/results/screenshots

Login As Premium Robot User
    [Arguments]    ${email}=${ROBOT_PREMIUM_EMAIL}
    Open Login Page
    Enter Login Credentials    ${email}    ${ROBOT_PREMIUM_PASSWORD}
    Submit Login
    User Should Be Logged In

Open Playlist Page
    Go To    ${BASE_URL}${PLAYLIST_PATH}
    Wait Until Element Is Visible    ${PLAYLIST_HEADING}

Guest Should Be Redirected To Login
    Go To    ${BASE_URL}${PLAYLIST_PATH}
    Wait Until Location Contains    /login
    Wait Until Element Is Visible    ${LOGIN_FORM}

Empty Playlist State Should Be Visible
    Wait Until Element Is Visible    ${EMPTY_STATE}
    Element Should Contain    ${EMPTY_STATE}    Bạn chưa có Playlist nào

Create Playlist
    [Arguments]    ${name}    ${description}=${EMPTY}
    Execute Javascript    document.querySelector('button[data-bs-target="#createPlaylistModal"]').scrollIntoView({block: 'center'});
    Click Button    ${CREATE_BUTTON}
    Wait Until Element Is Visible    ${CREATE_MODAL}
    Input Text    ${CREATE_NAME}    ${name}
    IF    $description
        Input Text    ${CREATE_DESCRIPTION}    ${description}
    END
    Execute Javascript    document.querySelector('#createPlaylistModal button[type="submit"]').scrollIntoView({block: 'center'});
    Click Button    ${CREATE_SUBMIT}
    Wait Until Location Contains    ${PLAYLIST_PATH}
    Wait Until Element Is Visible    ${SUCCESS_ALERT}
    Wait Until Page Contains    ${name}

Submit Playlist Without Name
    Execute Javascript    document.querySelector('button[data-bs-target="#createPlaylistModal"]').scrollIntoView({block: 'center'});
    Click Button    ${CREATE_BUTTON}
    Wait Until Element Is Visible    ${CREATE_MODAL}
    Execute Javascript    document.querySelector('#createPlaylistModal button[type="submit"]').scrollIntoView({block: 'center'});
    Click Button    ${CREATE_SUBMIT}
    ${missing}=    Execute Javascript    return document.querySelector('#createPlaylistModal input[name="name"]').validity.valueMissing;
    Should Be True    ${missing}

Playlist Should Exist
    [Arguments]    ${name}
    Page Should Contain Link    ${name}

Playlist Name Should Appear Twice
    [Arguments]    ${name}
    ${matches}=    Get WebElements    xpath=//a[normalize-space()="${name}"]
    Length Should Be    ${matches}    2

Open Playlist
    [Arguments]    ${name}
    Execute Javascript    [...document.querySelectorAll('a')].find(a => a.textContent.trim() === arguments[0]).scrollIntoView({block: 'center'});    ARGUMENTS    ${name}
    Click Link    ${name}
    Wait Until Element Is Visible    ${DETAIL_TITLE}
    Element Should Contain    ${DETAIL_TITLE}    ${name}

Playlist Description Should Be
    [Arguments]    ${description}
    Element Should Contain    ${DETAIL_DESCRIPTION}    ${description}

Rename Playlist
    [Arguments]    ${new_name}
    Execute Javascript    document.querySelector('button[data-bs-target="#editPlaylistModal"]').scrollIntoView({block: 'center'});
    Click Button    ${EDIT_BUTTON}
    Wait Until Element Is Visible    ${EDIT_MODAL}
    Clear Element Text    ${EDIT_NAME}
    Input Text    ${EDIT_NAME}    ${new_name}
    Execute Javascript    document.querySelector('#editPlaylistModal button[type="submit"]').scrollIntoView({block: 'center'});
    Click Button    ${EDIT_SUBMIT}
    Wait Until Element Is Visible    ${DETAIL_TITLE}
    Element Should Contain    ${DETAIL_TITLE}    ${new_name}
    Reload Page
    Wait Until Element Is Visible    ${DETAIL_TITLE}
    Element Should Contain    ${DETAIL_TITLE}    ${new_name}

Add Song To Playlist
    [Arguments]    ${song_title}
    Execute Javascript    document.querySelector('button[data-bs-target="#addSongModal"]').scrollIntoView({block: 'center'});
    Click Button    ${ADD_SONG_BUTTON}
    Wait Until Element Is Visible    ${ADD_SONG_MODAL}
    Input Text    ${SONG_SEARCH_INPUT}    ${song_title}
    Wait Until Element Contains    ${SONG_SEARCH_RESULTS}    ${song_title}
    Execute Javascript    document.querySelector('#songSearchResults button.btn-add-song').scrollIntoView({block: 'center'});
    Click Button    css=#songSearchResults button.btn-add-song
    Wait Until Element Contains    ${SONG_SEARCH_RESULTS}    Đã thêm
    Reload Page
    Wait Until Element Contains    ${SONG_LIST}    ${song_title}

Remove Song From Playlist
    [Arguments]    ${song_title}
    Element Should Contain    ${SONG_LIST}    ${song_title}
    Execute Javascript    document.querySelector('#sortableList button[data-confirm-message]').scrollIntoView({block: 'center'});
    Click Button    ${REMOVE_SONG_BUTTON}
    Wait Until Element Is Visible    ${CONFIRM_MODAL}
    Execute Javascript    document.querySelector('#globalConfirmModal [data-confirm-accept]').click();
    Execute Javascript    const form = document.querySelector('#sortableList form'); form.dataset.confirmed = '1'; form.submit();
    Wait Until Element Is Visible    ${SONG_LIST}
    Wait Until Element Does Not Contain    ${SONG_LIST}    ${song_title}
    Reload Page
    Element Should Not Contain    ${SONG_LIST}    ${song_title}
