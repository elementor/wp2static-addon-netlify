#!/bin/bash

############################################
##
## Build WP2Static Netlify Deployment Addon
##
## script archive_name dont_minify
##
## places archive in $HOME/Downloads
##
############################################

# run script from project root
EXEC_DIR=$(pwd)

TMP_DIR=$HOME/plugintmp
rm -Rf $TMP_DIR
mkdir -p $TMP_DIR

rm -Rf $TMP_DIR/wp2static-addon-netlify
mkdir $TMP_DIR/wp2static-addon-netlify

# clear dev dependencies
rm -Rf $EXEC_DIR/vendor/*
# load prod deps and optimize loader
composer install --quiet --no-dev --optimize-autoloader

# cp all required sources to build dir
cp -r $EXEC_DIR/wp2static-addon-netlify.php $TMP_DIR/wp2static-addon-netlify/
cp -r $EXEC_DIR/src $TMP_DIR/wp2static-addon-netlify/
cp -r $EXEC_DIR/vendor $TMP_DIR/wp2static-addon-netlify/
cp -r $EXEC_DIR/views $TMP_DIR/wp2static-addon-netlify/

cd $TMP_DIR

# tidy permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

zip --quiet -r -9 ./$1.zip ./wp2static-addon-netlify

cd -

mkdir -p $HOME/Downloads/

cp $TMP_DIR/$1.zip $HOME/Downloads/

# reset dev dependencies
cd $EXEC_DIR
# clear dev dependencies
rm -Rf $EXEC_DIR/vendor/*
# load prod deps
composer install --quiet
