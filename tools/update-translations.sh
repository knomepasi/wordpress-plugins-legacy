#!/bin/bash

PLUGIN_DIR="../plugins"

if [ -n "$1" ]; then
	for PLUGIN in $@
	do
		xgettext -o $PLUGIN_DIR/$PLUGIN/languages/$PLUGIN.pot -L PHP -k__ -k_e -k_n:1,2 -k_x:1,2c -k_ex:1,2c -k_nx:4c,1,2 -kesc_attr_-kesc_attr_e -kesc_attr_x:1,2c -kesc_html__ -kesc_html_e -kesc_html_x:1,2c -k_n_noop:1,2 -k_nx_noop:4c,1,2 $PLUGIN_DIR/$PLUGIN/*.php
	done
else
	PLUGIN_DIR="$PLUGIN_DIR/*"
	for DIR in $PLUGIN_DIR
	do
		if [ -d "$DIR" ] && [ -d "$DIR/languages/" ]; then
			PLUGIN_NAME=$(basename $DIR)
			xgettext -o $DIR/languages/$PLUGIN_NAME.pot -L PHP -k__ -k_e -k_n:1,2 -k_x:1,2c -k_ex:1,2c -k_nx:4c,1,2 -kesc_attr_-kesc_attr_e -kesc_attr_x:1,2c -kesc_html__ -kesc_html_e -kesc_html_x:1,2c -k_n_noop:1,2 -k_nx_noop:4c,1,2 $DIR/*.php
		fi
	done
fi


