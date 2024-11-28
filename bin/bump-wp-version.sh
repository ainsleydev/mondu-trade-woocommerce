#!/bin/bash
#
# bump-wp-version.sh
#
# This script increments the version number in both mondu-trade-account.php
# and README.txt based on the type of release (patch, minor, or major).

# Ensure the script is executed from the correct directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$SCRIPT_DIR/.."
PHP_FILE="$PROJECT_ROOT/mondu-trade-account.php"
README_FILE="$PROJECT_ROOT/README.txt"

# Function to extract the current version from mondu-trade-account.php
get_current_version() {
    grep -i "^[[:space:]]*\* Version:[[:space:]]*" "$PHP_FILE" | \
    sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/' | \
    tr -d '[:space:]'
}

# Function to update the version in mondu-trade-account.php
update_version_in_php_header() {
    local new_version=$1
    perl -pi -e 's/Version:\s*\d+\.\d+\.\d+/Version:\t\t\t'"$new_version"'/g' "$PHP_FILE"
}

# Function to update the MONDU_TRADE_PLUGIN_VERSION constant
update_version_in_php_constant() {
    local new_version=$1
    perl -pi -e "s/define\(\s*'MONDU_TRADE_PLUGIN_VERSION'\s*,\s*'[^']*'\s*\)/define('MONDU_TRADE_PLUGIN_VERSION', '$new_version')/g" "$PHP_FILE"
}

# Function to update the version in README.txt
update_version_in_readme() {
    local new_version=$1
    perl -pi -e 's/Stable tag: \d+\.\d+\.\d+/Stable tag: '"$new_version"'/g' "$README_FILE"
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

# Update the version in both files
update_version_in_php_header "$new_version"
update_version_in_php_constant "$new_version"
update_version_in_readme "$new_version"

# Verify the updates
echo "Verifying updates..."

# Check if the 'Version' field was updated
if grep -q "Version:[[:space:]]*$new_version" "$PHP_FILE"; then
    echo "✅  'Version' field successfully updated to $new_version in mondu-trade-account.php"
else
    echo "❌  Error: Failed to update the 'Version' field in mondu-trade-account.php"
    exit 1
fi

# Check if the MONDU_TRADE_PLUGIN_VERSION constant was updated
if grep -q "define('MONDU_TRADE_PLUGIN_VERSION', '$new_version')" "$PHP_FILE"; then
    echo "✅  MONDU_TRADE_PLUGIN_VERSION successfully updated to $new_version in mondu-trade-account.php"
else
    echo "❌  Error: Failed to update the MONDU_TRADE_PLUGIN_VERSION constant in mondu-trade-account.php"
    exit 1
fi

# Check if the README.txt was updated
if grep -q "Stable tag: $new_version" "$README_FILE"; then
    echo "✅  Version successfully updated to $new_version in README.txt"
else
    echo "❌  Error: Failed to update the version in README.txt"
    exit 1
fi

echo "Version bump completed successfully!"
