#!/usr/bin/env bash
set -e

REPO="danielneiva/todo-cli"
INSTALL_DIR="/usr/local/bin"
BIN_NAME="todo-cli"

echo "Fetching latest release from $REPO..."

# Get the latest release from GitHub API
LATEST_RELEASE=$(curl -s "https://api.github.com/repos/$REPO/releases/latest")
OS="$(uname -s)"
ARCH="$(uname -m)"

# Determine download URL
DOWNLOAD_URL=""

if [ "$OS" = "Linux" ] && [ "$ARCH" = "x86_64" ]; then
    # Try to get the standalone linux-x64 binary if it exists
    DOWNLOAD_URL=$(echo "$LATEST_RELEASE" | grep "browser_download_url" | grep "linux-x64" | cut -d '"' -f 4 | head -n 1)
fi

# Fallback to the .phar distribution
if [ -z "$DOWNLOAD_URL" ]; then
    echo "No native binary found for your system. Falling back to the PHAR archive (requires PHP)..."
    DOWNLOAD_URL=$(echo "$LATEST_RELEASE" | grep "browser_download_url" | grep "todo-cli.phar" | cut -d '"' -f 4 | head -n 1)
fi

if [ -z "$DOWNLOAD_URL" ]; then
    echo "Error: Could not find a suitable release payload."
    exit 1
fi

echo "Downloading from $DOWNLOAD_URL..."

# Download to a temporary file
TMP_FILE=$(mktemp)
curl -sL "$DOWNLOAD_URL" -o "$TMP_FILE"
chmod +x "$TMP_FILE"

echo "Installing to $INSTALL_DIR/$BIN_NAME..."
if [ -w "$INSTALL_DIR" ]; then
    mv "$TMP_FILE" "$INSTALL_DIR/$BIN_NAME"
else
    sudo mv "$TMP_FILE" "$INSTALL_DIR/$BIN_NAME"
fi

echo "Installation complete! Run '$BIN_NAME' to get started."
