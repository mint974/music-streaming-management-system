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
