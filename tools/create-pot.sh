#!/bin/bash

plugin_slug=${PWD##*/}

# get project information from the plugin header
plugin_name=`awk -F: '/Plugin Name:/ { print $2 }' $plugin_slug.php | sed 's/^ *//g'`
plugin_author=`awk -F: '/Author:/ { print $2 }' $plugin_slug.php | sed 's/^ *//g'`

# create a template file for translations
mkdir -p languages
rm -f languages/$plugin_slug.pot
wp_keywords="-k__ -k_e -k_n:1,2 -k_x:1,2c -k_ex:1,2c -k_nx:4c,1,2 -kesc_attr__ -kesc_attr_e -kesc_attr_x:1,2c -kesc_html__ -kesc_html_e -kesc_html_x:1,2c -k_n_noop:1,2 -k_nx_noop:4c,1,2"
xgettext -d $plugin_slug -o languages/$plugin_slug.pot -L PHP --no-wrap --copyright-holder="$plugin_author" $wp_keywords *.php

# fix header information
now=$(date +%Y)
sed -i "s/SOME DESCRIPTIVE TITLE./This is the translation template file for $plugin_name./g" languages/$plugin_slug.pot
sed -i "s/(C) YEAR/(C) $now/g" languages/$plugin_slug.pot
sed -i "s/the PACKAGE package./the plugin./g" languages/$plugin_slug.pot
# current plural forms for english
sed 's/^"Plural-Forms:.*/"Plural-Forms: nplurals=2; plural=(n != 1);\\n"/' $plugin_slug.pot > $plugin_slug.pot