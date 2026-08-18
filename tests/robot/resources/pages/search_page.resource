*** Settings ***
Resource    ../common/common.resource


*** Variables ***
${SEARCH_PATH}                 /search
${SEARCH_FORM}                 id=globalSearchForm
${SEARCH_INPUT}                id=globalSearchInput
${SEARCH_CONTENT}              id=searchTabContent
${SEARCH_HERO}                 css=.search-hero
${ARTISTS_TAB}                 css=a.search-tab-pill[href*="tab=artists"]
${SONGS_TAB}                   css=a.search-tab-pill[href*="tab=songs"]
${ALBUMS_TAB}                  css=a.search-tab-pill[href*="tab=albums"]
${ARTIST_RESULT}               css=.artist-card-search
${SONG_RESULT}                 css=.search-song-item
${SONG_RESULT_TITLE}           css=.ssi-title a
${SONG_RESULT_LINK}            css=.ssi-title a
${ALBUM_RESULT}                css=.search-album-card
${NO_RESULTS}                  css=.search-no-results
${SONG_DETAIL_TITLE}           css=.breadcrumb-current


*** Keywords ***
Open Search Page
    Go To    ${BASE_URL}${SEARCH_PATH}
    Wait Until Element Is Visible    ${SEARCH_INPUT}
    Wait Until Element Is Visible    ${SEARCH_CONTENT}

Search For Music
    [Arguments]    ${query}
    Input Text    ${SEARCH_INPUT}    ${query}
    Submit Form    ${SEARCH_FORM}
    Wait Until Location Contains    ${SEARCH_PATH}
    Wait Until Element Is Visible    ${SEARCH_CONTENT}

Search Initial State Should Be Visible
    Wait Until Element Is Visible    ${SEARCH_HERO}
    Page Should Not Contain Element    ${SONG_RESULT}
    Page Should Not Contain Element    ${NO_RESULTS}

Select Songs Tab
    ${current_url}=    Get Location
    Go To    ${current_url}&tab=songs
    Wait Until Location Contains    tab=songs
    Wait Until Element Is Visible    ${SEARCH_CONTENT}

Select Albums Tab
    ${current_url}=    Get Location
    Go To    ${current_url}&tab=albums
    Wait Until Location Contains    tab=albums
    Wait Until Element Is Visible    ${SEARCH_CONTENT}

Song Search Results Should Contain
    [Arguments]    ${title}
    Select Songs Tab
    Wait Until Element Is Visible    ${SONG_RESULT}
    Element Should Contain    ${SEARCH_CONTENT}    ${title}

Artist Search Results Should Contain
    [Arguments]    ${artist_name}
    Wait Until Element Is Visible    ${ARTIST_RESULT}
    Element Should Contain    ${SEARCH_CONTENT}    ${artist_name}

Album Search Results Should Contain
    [Arguments]    ${album_name}    ${artist_name}
    Select Albums Tab
    Wait Until Element Is Visible    ${ALBUM_RESULT}
    Element Should Contain    ${SEARCH_CONTENT}    ${album_name}
    Element Should Contain    ${SEARCH_CONTENT}    ${artist_name}

Search Should Show No Results
    Wait Until Element Is Visible    ${NO_RESULTS}
    Element Should Contain    ${NO_RESULTS}    Không tìm thấy kết quả

Open Song From Search Results
    [Arguments]    ${title}
    Song Search Results Should Contain    ${title}
    Execute Javascript    document.querySelector('.ssi-title a').scrollIntoView({block: 'center'});
    Click Link    ${SONG_RESULT_LINK}
    Wait Until Location Contains    /songs/
    Wait Until Element Is Visible    ${SONG_DETAIL_TITLE}
    Element Should Contain    ${SONG_DETAIL_TITLE}    ${title}
