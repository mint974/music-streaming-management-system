import os


BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8080").rstrip("/")
BROWSER = os.getenv("BROWSER", "chrome")
CHROMEDRIVER_PATH = os.getenv("CHROMEDRIVER_PATH", "")
DEFAULT_TIMEOUT = os.getenv("DEFAULT_TIMEOUT", "10 seconds")
HEADLESS = os.getenv("HEADLESS", "true").lower() in {"1", "true", "yes", "on"}

# A dedicated, non-production automation account should provide these values.
ROBOT_TEST_EMAIL = os.getenv("ROBOT_TEST_EMAIL", "")
ROBOT_LOCKED_EMAIL = os.getenv("ROBOT_LOCKED_EMAIL", "")
ROBOT_TEST_PASSWORD = os.getenv("ROBOT_TEST_PASSWORD", "")

ROBOT_SEARCH_ARTIST = "Robot Search Artist"
ROBOT_SEARCH_ALBUM = "Robot Search Album"
ROBOT_SEARCH_SONG_ALPHA = "Robot Search Song Alpha"
ROBOT_SEARCH_SONG_BETA = "Robot Search Song Beta"
ROBOT_SEARCH_PARTIAL = "Search Song Alpha"
ROBOT_SEARCH_NO_RESULT = "robot-search-no-result-987654"

ROBOT_PREMIUM_EMAIL = os.getenv("ROBOT_PREMIUM_EMAIL", "")
ROBOT_PREMIUM_EMPTY_EMAIL = os.getenv("ROBOT_PREMIUM_EMPTY_EMAIL", "")
ROBOT_PREMIUM_PASSWORD = os.getenv("ROBOT_PREMIUM_PASSWORD", "")
ROBOT_PLAYLIST_CREATE = "Robot Playlist Created"
ROBOT_PLAYLIST_DESCRIPTION = "Created by Robot playlist automation"
ROBOT_PLAYLIST_WITH_DESCRIPTION = "Robot Playlist With Description"
ROBOT_PLAYLIST_DUPLICATE = "Robot Playlist Duplicate"
ROBOT_PLAYLIST_OPEN = "Robot Playlist Open"
ROBOT_PLAYLIST_RENAME = "Robot Playlist Rename"
ROBOT_PLAYLIST_RENAMED = "Robot Playlist Renamed Successfully"
ROBOT_PLAYLIST_ADD_SONG = "Robot Playlist Add Song"
ROBOT_PLAYLIST_REMOVE_SONG = "Robot Playlist Remove Song"
