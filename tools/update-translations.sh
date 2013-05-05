#!/bin/bash

PLUGIN_DIR="../plugins/*"

for DIR in $PLUGIN_DIR
do
	if [ -d $DIR ]; then
		PLUGIN_NAME=$(basename $DIR)
		if [ ! -d $DIR/languages/ ]; then
			mkdir $DIR/languages/
		fi
		xgettext -o $DIR/languages/$PLUGIN_NAME.pot -L PHP -k__ -k_e -k_n:1,2 -k_x:1,2c -k_ex:1,2c -k_nx:4c,1,2 -kesc_attr_-kesc_attr_e -kesc_attr_x:1,2c -kesc_html__ -kesc_html_e -kesc_html_x:1,2c -k_n_noop:1,2 -k_nx_noop:4c,1,2 $DIR/*.php
	fi
done
