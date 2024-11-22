#!/bin/bash
#
# tag.sh
#

# Set variables
version=$1
message=$2

# Check version is not empty
if [[ $version == "" ]]
  then
    echo "Add Version number"
    exit
fi

# Exit if version contains 'v'
if [[ $version == *"v"* ]]; then
    echo "Version number should not contain 'v'"
    exit 1
fi

# Check commit message is not empty
if [[ $message == "" ]]
  then
    echo "Add commit message"
    exit
fi

echo "Releasing version: " $version

git tag -a "$version" -m "$message"
git push origin $version
