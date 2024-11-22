#!/bin/bash
#
# bump-wp-version.sh
#
# This script increments the version number in mondu-trade-account.php
# based on the type of release (patch, minor, or major).

# Ensure the script is executed from the correct directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$SCRIPT_DIR/.."
PHP_FILE="$PROJECT_ROOT/mondu-trade-account.php"

# Function to extract the current version from mondu-trade-account.php
get_current_version() {
    grep -i "^[[:space:]]*\* Version:[[:space:]]*" "$PHP_FILE" | \
    sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/' | \
    tr -d '[:space:]'
}

# Function to update the version in mondu-trade-account.php
update_version_in_file() {
    local new_version=$1
    perl -pi -e 's/Version:\s*\d+\.\d+\.\d+/Version:\t\t\t'"$new_version"'/g' "$PHP_FILE"
}

# Main script starts here
# Get the current version
current_version=$(get_current_version)

if [[ -z $current_version ]]; then
    echo "Error: Unable to find the current version in $PHP_FILE."
    exit 1
fi

echo "Current version: $current_version"

# Prompt the user to choose the release type
echo "Choose release type: (1) Patch, (2) Minor, (3) Major"
read -p "Enter choice [1/2/3]: " choice

# Determine the new version based on the release type
case $choice in
    1)
        new_version=$(echo "$current_version" | awk -F. '{printf "%d.%d.%d", $1, $2, $3+1}')
        ;;
    2)
        new_version=$(echo "$current_version" | awk -F. '{printf "%d.%d.0", $1, $2+1}')
        ;;
    3)
        new_version=$(echo "$current_version" | awk -F. '{printf "%d.0.0", $1+1}')
        ;;
    *)
        echo "Invalid choice. Exiting."
        exit 1
        ;;
esac

echo "New version: $new_version"

# Update the version in mondu-trade-account.php
update_version_in_file "$new_version"

# Verify the update
if grep -q "$new_version" "$PHP_FILE"; then
    echo "Version successfully updated to $new_version in $PHP_FILE."
else
    echo "Error: Failed to update the version in $PHP_FILE."
    exit 1
fi
