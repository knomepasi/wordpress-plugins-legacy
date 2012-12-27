<?php

/*  Add widget
 *
 */

add_action( 'widgets_init', create_function( '', 'return register_widget("ProjectsCatalogWidget");' ) );

class ProjectsCatalogWidget extends WP_Widget {
	/** constructor */
	function ProjectsCatalogWidget() {
		$widget_ops = array( "description" => __( 'Show the projects catalog listing.', 'projects-catalog' ) );
		$control_ops = array( "width" => 200 );
		parent::WP_Widget( false, $name = _x( 'Projects Catalog', 'widget name', 'projects-catalog' ), $widget_ops, $control_ops );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$group = $instance['group'];
		$showtype = $instance['showtype'];

		echo $before_widget;
		if( $title ) { echo $before_title . $title . $after_title; }

		catalog_print( $group, $showtype );

		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['group'] = $new_instance['group'];
		$instance['showtype'] = $new_instance['showtype'];
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = esc_attr( $instance['title'] );
		$group = esc_attr( $instance['group'] );
		$showtype = esc_attr( $instance['showtype'] );
		?>

		<p>
			<label style="display: inline;" for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'projects-catalog' ); ?><br />
				<input style="width: 220px;" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label style="display: inline;" for="<?php echo $this->get_field_id( 'group' ); ?>"><?php _e( 'Project Group', 'projects-catalog' ); ?><br />
				<select style="width: 220px;" id="<?php echo $this->get_field_id( 'group' ); ?>" name="<?php echo $this->get_field_name( 'group' ); ?>"><?php _e( 'Project Group', 'projects-catalog' ); ?>
					<option value="all" <?php if( !$group ) { echo 'selected="selected"'; } ?>>All</option>
					<?php
						$groups = get_terms( "project_group" );
						foreach( $groups as $group_item ) {
							if( $group_item->slug == $group ) { $selected = 'selected="selected"'; } else { unset( $selected ); }
							print '<option value="' . $group_item->slug . '" ' . $selected . '>' . $group_item->name . '</option>';
						}
					?>
				</select>
			</label>
		</p>
		<p>
			<label style="display: inline;" for="<?php echo $this->get_field_id( 'showtype' ); ?>"><?php _e( 'Showtype', 'projects-catalog' ); ?><br />
				<select style="width: 220px;" id="<?php echo $this->get_field_id( 'showtype' ); ?>" name="<?php echo $this->get_field_name( 'showtype' ); ?>"><?php _e( 'Showtype', 'projects-catalog' ); ?>
					<option value="" <?php if( !$showtype ) { echo 'selected="selected"'; } ?>><?php _e( 'None (use defaults)', 'projects-catalog' ); ?></option>
					<?php echo get_showtype_options( $showtype ); ?>
				</select>
				<p style="font-size: 85%;"><?php _e( "If you specify a showtype here, all the category defaults will be overridden and all categories will use the selected showtype.", "projects-catalog" ); ?></p>
			</label>
		</p>
		<?php 
	}
}

/*  Add shortcode
 *
 */

add_shortcode( 'projects_catalog', 'ProjectsCatalogShortcode' );

function ProjectsCatalogShortcode( $atts, $content, $code ) {
	extract( shortcode_atts( array(
		'slug' => '',
		'show' => ''
	), $atts ) );

	if( strlen( $show ) > 0 ) {
		$show = "showtype_" . $show;
	}

	catalog_print( $slug, $show );

	return $out;
}

/*  Function that prints the catalog
 *
 */

function catalog_print( $group, $default_showtype ) {
	$args = array( 'post_type' => 'project', 'sort_column' => 'post_title', 'sort_order' => 'ASC', 'post_status' => 'publish' );
	$projects = get_pages( $args );

	if( !is_array( $projects ) ) { $projects = array( ); }

	foreach( $projects as $project ) {
		$project->permalink = get_permalink( $project->ID );

		$project->terms = get_the_terms( $project->ID, "project_group" );
		if( !is_array( $project->terms ) ) {
			$term_object->slug = "xxxxx_Undefined";
			$term_object->name = __( 'Undefined', 'projects-catalog' );
			$term_object->term_id = $project->ID;
			$project->terms = array( "0" => $term_object );
		}

		foreach( $project->terms as $term ) {
			if( $term->slug == $group || !$group ) {
				$menuorder = get_metadata( 'project_group', $term->term_id, 'project_group_menu_order' );
				$arrayorder = str_pad( $menuorder[0], 10, "0", STR_PAD_LEFT ) . $term->slug;

				$project->term_name = $term->name;
				$project->term_id = $term->term_id;

				$project_list[$arrayorder][] = $project;
			}
		}
	}

	if( is_array( $project_list ) ) {
		krsort( $project_list );

		foreach( $project_list as $current_group ) {
			if( !$default_showtype ) {
				$showtype = get_metadata( 'project_group', $current_group[0]->term_id, 'project_group_showtype' );
				$showtype = $showtype[0];
			} else {
				$showtype = $default_showtype;
			}

			if( is_callable( $showtype ) ) {
				print call_user_func( $showtype, $current_group, $current_group[0]->term_name );
			} else {
				/* Function is not callable, fallback to simple list */
				print showtype_list( $current_group, $current_group[0]->term_name );
			}
		}
	}
	/* Catalog end */
}
?>
