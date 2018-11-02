<?php 
/*
Plugin Name: Estimate Plugin
Description: Plugin that estimates
Version: 1.0
Author: George
*/

//Exit if Accessed Directly
if(!defined("ABSPATH")) {
	exit();
}

class Estimate {
	function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'est_add_scripts'));
		// Estimate
		add_action('init', array($this, 'est_register_estimate'));
		add_action('add_meta_boxes', array($this, 'est_add_fields_meta_box'));
		add_action('save_post', array($this, 'est_save_fields_meta'));
		// Presets
		add_action('init', array($this, 'est_register_preset'));
		add_action('add_meta_boxes', array($this, 'est_add_presets_meta_box'));
		add_action('save_post', array($this, 'est_save_presets_meta'));

		add_action('admin_menu', array($this, 'est_menu_page'));
		add_action('admin_init', array($this, 'add_preset'));

	}

	function est_add_scripts() {
		wp_enqueue_style('est-bootstrap-style', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css');
		wp_enqueue_style('est-style', plugins_url().'/estimate/css/style.css');
		wp_enqueue_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array('jquery'), '3.3.4', true );
	}

	// Create Estimate CPT
	function est_register_estimate() {
		$singular_name = apply_filters('est_label_single', 'Estimate');
		$plural_name = apply_filters('est_label_plural', 'Estimates');

		$labels = array(
			'name' 					=> __($plural_name, 'est_domain'),
			'singular_name' 		=> __($singular_name, 'est_domain'),
			'add_new' 				=> __('Add New', 'est_domain'),
			'add_new_item' 			=> __('Add New '. $singular_name, 'est_domain'),
			'edit' 					=> __('Edit', 'est_domain'),
			'edit_item' 			=> __('Edit '. $singular_name, 'est_domain'),
			'new_item' 				=> __('New '. $singular_name, 'est_domain'),
			'view' 					=> __('View', 'est_domain'),
			'view_item' 			=> __('View '.$singular_name, 'est_domain'),
			'search_items' 			=> __('Search '.$plural_name, 'est_domain'),
			'not_found' 			=> __('No '.$plural_name, 'est_domain'),
			'not_found_in_trash' 	=> __('No '.$plural_name.' found', 'est_domain'),
			'menu_name' 			=> __($plural_name, 'est_domain'),
		);

		$args = apply_filters('est_args',array(
			'labels' 				=> $labels,
			'description' 			=> __('Estimate custom posts', 'est_domain'),
			'taxonomies' 			=> array('category'),
			'public' 				=> true,
			'show_in_menu' 			=> true,
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-chart-bar',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'has_archive' 			=> true,
			'rewrite' 				=> true,
			'capabilty_type' 		=> 'post',
			'supports' 				=> array('title'),

		));
		register_post_type('estimates', $args);
	}

	function est_add_fields_meta_box() {
		add_meta_box(
			'est_fields',
			__('Estimates fields'),
			array($this, 'est_fields_callback'),
			'estimates',
			'normal',
			'default'
		);
	}

	function est_fields_callback($post) {
		wp_nonce_field(basename(__FILE__), 'wp_est_nonce');
		$meta = get_post_meta($post->ID);?>
		<div class="wrapper estimate-fields">
			<div class="form-group">
				<label for="time">
					<?php esc_html_e('Pick a time', 'est_domain') ?>
				</label>

				<input type="time" name="time" id="time" value="<?php if(!empty($meta['time'])) echo esc_attr($meta['time'][0]);?>">
			</div>
		</div>
		<?php
	}

	function est_save_fields_meta($post_id) {
		$is_autosave = wp_is_post_autosave($post_id);
		$is_revision = wp_is_post_revision($post_id);
		$is_valid_nonce = (isset($_POST['wp_est_nonce']) && wp_verify_nonce($_POST['wp_est_nonce'], basename(__FILE__))) ? 'true' : 'false';

		if ($is_autosave || $is_revision || !$is_valid_nonce) {
			return;
		}

		if (isset($_POST['time'])) {
			update_post_meta($post_id, 'time', sanitize_text_field($_POST['time']));
		}

	}

	// Create Presets CPT
	function est_register_preset() {
		$singular_name = apply_filters('prst_label_single', 'Preset');
		$plural_name = apply_filters('prst_label_plural', 'Presets');

		$labels = array(
			'name' 					=> __($plural_name, 'est_domain'),
			'singular_name' 		=> __($singular_name, 'est_domain'),
			'add_new' 				=> __('Add New', 'est_domain'),
			'add_new_item' 			=> __('Add New '. $singular_name, 'est_domain'),
			'edit' 					=> __('Edit', 'est_domain'),
			'edit_item' 			=> __('Edit '. $singular_name, 'est_domain'),
			'new_item' 				=> __('New '. $singular_name, 'est_domain'),
			'view' 					=> __('View', 'est_domain'),
			'view_item' 			=> __('View '.$singular_name, 'est_domain'),
			'search_items' 			=> __('Search '.$plural_name, 'est_domain'),
			'not_found' 			=> __('No '.$plural_name, 'est_domain'),
			'not_found_in_trash' 	=> __('No '.$plural_name.' found', 'est_domain'),
			'menu_name' 			=> __($plural_name, 'est_domain'),
		);

		$args = apply_filters('prst_args',array(
			'labels' 			=> $labels,
			'description' 		=> __('Presets custom posts', 'est_domain'),
			'taxonomies' 		=> array('category'),
			'public' 			=> true,
			'show_in_menu' 		=> true,
			'menu_position' 	=> 6,
			'menu_icon' 		=> 'dashicons-list-view',
			'show_in_nav_menus' => true,
			'query_var' 		=> true,
			'can_export' 		=> true,
			'has_archive' 		=> true,
			'rewrite' 			=> true,
			'capabilty_type' 	=> 'post',
			'supports' 			=> array('title'),

		));
		register_post_type('presets', $args);
	}

	function est_add_presets_meta_box() {
		add_meta_box(
			'prst_fields',
			__('Presets fields'),
			array($this, 'est_fields_preset_callback'),
			'presets',
			'normal',
			'default'
		);
	}

	function est_fields_preset_callback($post) {
		wp_nonce_field(basename(__FILE__), 'wp_prst_nonce');
		$meta = get_post_meta($post->ID);?>
		<div class="wrapper estimate-fields">
			<div class="form-group">
				<label for="time">
					<?php esc_html_e('Pick a time', 'est_domain') ?>
				</label>

				<input type="time" name="time" id="time" value="<?php if(!empty($meta['time'])) echo esc_attr($meta['time'][0]);?>">
			</div>
		</div>
		<?php
	}

	function est_save_presets_meta($post_id) {
		$is_autosave = wp_is_post_autosave($post_id);
		$is_revision = wp_is_post_revision($post_id);
		$is_valid_nonce = (isset($_POST['wp_prst_nonce']) && wp_verify_nonce($_POST['wp_prst_nonce'], basename(__FILE__))) ? 'true' : 'false';

		if ($is_autosave || $is_revision || !$is_valid_nonce) {
			return;
		}

		if (isset($_POST['time'])) {
			update_post_meta($post_id, 'time', sanitize_text_field($_POST['time']));
		}

	}

	// Creating Shortcode
	function est_menu_page() {
		add_menu_page( 
			'Estimate Table',
			'Estimate Table',
			'manage_options',
			'estimate_menu_page',
			array($this, 'est_table'),
			'dashicons-grid-view',
			8
		);
	}

	function est_table() {
		$args = array(
			'post_type'		=> array('estimates', 'presets'),
			'order'			=> 'DESC',
		);

		$getPost = new wp_query($args);
		global $post;

		?>
		<table> 
			<tr> 
				<th><h3><?php _e('Estimate', 'est_domain') ?></h3></th> 
				<th><h3><?php _e('Rate: ', 'est_domain') ?></h3></th> 
				<th>
					<form action="" method="POST">
						<input type="number" name="rate" id="rate" min="0" value="<?php echo $_POST['rate']; ?>">
					</form>
				</th>
			</tr> 
			<tr> 
				<td><p><?php _e('Title', 'est_domain') ?></p></td> 
				<td><p><?php _e('Time', 'est_domain')?></p></td>
			</tr> 
			<?php 
			$postData = get_posts(array(
				'order' 		=> 'DESC',
				'post_type'		=> array('estimates', 'presets'),
			));
			foreach($postData as $post) : ?>
				<tr>
					<td>
						<p><?php _e($post->post_title, 'est_domain'); ?></p>
						<hr>
					</td>
					<td>
						<?php
						echo '<p>' . get_post_meta($post->ID, 'time', true) . '</p>';
						$est_time += (int)get_post_meta($post->ID, 'time', true);
						?>
						<hr>
					</td>
				</tr>
				<?php
			endforeach;
			?>
			<tr>
				<td colspan="2"></td>
				<td>
					<div class="container">
  <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"><?php _e('Add', 'est_domain') ?></button>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
        	<h3><?php _e('Add Preset Form', 'est_domain') ?></h3>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
        	<div class="form-group">
          	<form action="" method="POST">
          		<label for="title"><?php _e('Title: ', 'est_domain') ?></label>
          		<input type="text" name="prst_title" id="prst_title"><br><br>
          		<label for="time">
					<?php esc_html_e('Pick a time', 'est_domain') ?>
				</label>
				<input type="time" name="prst_time" id="prst_time"><br><br>
				<?php wp_nonce_field('est_form', 'est_nonce_field');?>
          		<input type="submit" name="submit" value="<?php _e('Add', 'est_domain') ?>"><br><br>
          	</form>
          </div>
        </div>
    </div>
  </div>
  
</div>
				</td>

			</tr>
			<tr>
				<td colspan="1"><h3><?php _e('Summary:', 'est_domain') ?></h3></td>
			</tr>
			<tr>
				<td>
					<p>
						<ul>
							<li>
								<?php echo '<h3>' . $est_time . ' hours' . '</h3>'; ?>
								<?php
								if(isset($_POST['rate'])){
									echo '<h3>' . $est_time * $_POST['rate'] . ' $' . '</h3>';
								}
								?>
							</li>
						</ul>
					</p>
				</td>
			</tr>
		</table>
		<?php 
	
	}

	function add_preset() {
				// Form validation
			if(isset($_POST['est_nonce_field']) && wp_verify_nonce($_POST['est_nonce_field'], 'est_form')) {
				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['prst_title'] ),
					'post_type' 		=> 'presets',
					'post_status' 		=> 'publish'
				);

				$prst_id = wp_insert_post($post_information);

				update_post_meta($prst_id, 'time', $_POST['prst_time']);
			}
		}
}

new Estimate();