#!/usr/bin/env bash

# Exit if any command fails
set -eo pipefail

RELEASE_VERSION=$1
FILENAME_PREFIX="Plugin_CS-Cart_"
RELEASE_FOLDER=".dist"
FOLDER_PREFIX="plugin"

# If tag is not supplied, latest tag is used
if [ -z "$RELEASE_VERSION" ]
then
  RELEASE_VERSION=$(git describe --tags --abbrev=0)
fi

# Remove the old release folder
rm -rf "$RELEASE_FOLDER"  &&

# Create release from .git
mkdir "$RELEASE_FOLDER"  &&
git archive --format zip -9 --prefix="$FOLDER_PREFIX"/ --output "$RELEASE_FOLDER"/"$FILENAME_PREFIX""$RELEASE_VERSION".zip "$RELEASE_VERSION"  &&

# Unzip to manipulate the files
cd "$RELEASE_FOLDER" &&
unzip "$FILENAME_PREFIX""$RELEASE_VERSION".zip  &&

# Remove zip file.
rm "$FILENAME_PREFIX""$RELEASE_VERSION".zip

# Change to the extension folder
cd "$FOLDER_PREFIX" &&

# Zip everything excluding some specific files
zip -9 -r "$FILENAME_PREFIX""$RELEASE_VERSION".zip ./src/*  &&

# Move the zip file to the root of the release folder
mv "$FILENAME_PREFIX""$RELEASE_VERSION".zip ../ &&

# Remove the temporal directory to build the release
cd ../ &&
rm -rf "$FOLDER_PREFIX"
