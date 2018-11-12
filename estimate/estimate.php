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
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'estimate_menu_page') {
  			add_action('admin_enqueue_scripts', array($this, 'est_add_scripts'));
    	}
    	
		// Estimate
		add_action('init', array($this, 'est_register_estimate'));
		add_action('add_meta_boxes', array($this, 'est_add_fields_meta_box'));
		add_action('save_post', array($this, 'est_save_fields_meta'));
		// Presets
		add_action('init', array($this, 'est_register_preset'));
		add_action('add_meta_boxes', array($this, 'est_add_presets_meta_box'));
		add_action('save_post', array($this, 'est_save_presets_meta'));

		add_action('init', array($this, 'add_preset'));
		add_action('init', array($this, 'add_estimate'));
		add_action('admin_menu', array($this, 'est_menu_page'));

	}

	function est_add_scripts() {
		wp_enqueue_style('est-bootstrap-style', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css');
		wp_enqueue_style('timepicker-css', plugins_url().'/estimate/css/bootstrap-timepicker.min.css');
		wp_enqueue_style('est-style', plugins_url().'/estimate/css/style.css');
		wp_enqueue_script( 'bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array('jquery'), '4.1.3', true );
		wp_enqueue_script('bootstrap-poper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js');
		wp_enqueue_script('timepicker-js', plugins_url().'/estimate/js/bootstrap-timepicker.min.js');
		wp_enqueue_script('est-js', plugins_url().'/estimate/js/main.js', array('jquery'), '4.1.3', true );
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
			'supports' 				=> array(''),

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
		wp_nonce_field(basename(__FILE__), 'wp_prst_nonce');
		$meta = get_post_meta($post->ID);?>
		<div class="wrapper estimate-fields">
			<div class="form-group">
				<label for="text">
					<?php esc_html_e('Id of preset', 'est_domain') ?>
				</label>

				<input type="text" name="text" id="text" class="without_ampm" value="<?php if(!empty($meta['text'])) echo esc_attr($meta['text'][0]);?>">
			</div>
		</div>
		<?php
	}

	function est_save_fields_meta($post_id) {
		$is_autosave = wp_is_post_autosave($post_id);
		$is_revision = wp_is_post_revision($post_id);
		$is_valid_nonce = (isset($_POST['wp_prst_nonce']) && wp_verify_nonce($_POST['wp_prst_nonce'], basename(__FILE__))) ? 'true' : 'false';

		if ($is_autosave || $is_revision || !$is_valid_nonce) {
			return;
		}

		if (isset($_POST['text'])) {
			update_post_meta($post_id, 'text', sanitize_text_field($_POST['text']));
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

				<input type="time" name="time" id="time" class="without_ampm" value="<?php if(!empty($meta['time'])) echo esc_attr($meta['time'][0]);?>">
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
		?>
		<table class="table table-striped"> 
			<thead class="thead-dark">
				<tr>
					<?php 
						$est_sum = 0;
						$args = array('post_type'=>'estimates');
					        $postSelect = get_posts( $args );
					        foreach ( $postSelect as $post ) {
					        	++$est_sum;
					        } 
					?>
				<th scope="col"><?php _e("Estimate: $est_sum", 'est_domain') ?></th>  
				<th scope="col">
					<ul>
					<li><?php _e("Rate: ", 'est_domain');?></li>
						<li><form action="" method="POST">
							<a href="#" data-toggle="tooltip" data-placement="right" data-template='<div class="tooltip" role="tooltip"><div class="tooltip-inner"></div></div>' title="Press enter to add rate in $">
								<input type="number" min="0" name="rate" id="rate" value="<?php echo $_POST['rate']; ?>">
							</a>
						</form>
					</li>
					</ul>
				</th>
					<th scope="col">
					<button type="button" class="button button-primary" data-toggle="modal" data-target="#myModal"><?php _e('Add', 'est_domain') ?></button>
				</th>
				</tr>
			</thead>
			<tbody>
			<tr> 
				<th scope="col">
					<td><?php _e('Title', 'est_domain') ?></td> 
					<td><?php _e('Time', 'est_domain')?></td>
				</th>
			</tr> 
			<?php
				$postData = get_posts(array(
				'post_type'		=> 'estimates',
				'order'			=> 'ASC',
				'numberposts' 	=> -1,
			));

			$est_count = 1;
			foreach($postData as $post) : 
				setup_postdata($post);
				$prst_id = get_post_meta($post->ID, 'text', true);
				$prstData = get_post($prst_id, OBJECT);
				?>
				<tr>
					<th scope="col"><?php echo $est_count;?>
					<td>
						<p><?php _e($prstData->post_title, 'est_domain'); ?></p>
					</td>
					<td>
						<?php
						echo '<p>' . get_post_meta($prstData->ID, 'time', true) . '</p>';
						$est_time += (int)get_post_meta($prstData->ID, 'time', true);
						++$est_count;
						?>
					</td>
				</th>
				</tr>
				<?php
			wp_reset_postdata();
			endforeach;
			?>
			
	</tbody>
</table>
		<div class="summary card">
			<div class="card-body">
				<h3><?php _e('Summary:', 'est_domain') ?></h3>
					<p>
						<ul>
							<li>
								<?php echo '<h3>' . $est_time . ' hours' . '</h3>'; ?>
								<?php
									echo '<h3>' . $est_time * $_POST['rate'] . ' $' . '</h3>';
								?>
							</li>
						</ul>
					</p>
				</div>
			</div>
			<div class="container">
					  <!-- Modal -->
					  <div class="modal fade" id="myModal" role="dialog">
					    <div class="modal-dialog">
					    <div class="modal-dialog" role="document">
					      <!-- Modal content-->
					      <div class="modal-content">
					        <div class="modal-header">
					        	<h3><?php _e('Add Preset Form', 'est_domain') ?></h3>
					          <button type="button" class="close" data-dismiss="modal">&times;</button>
					        </div>
					        <div class="modal-body">
					        	<div class="form-group est-add">
					          	<form action="" method="POST">
					          		<select name="prst_select" id="prst_select">
					          			<option selected value="0">Add new Preset</option>
					          			 <?php
					                        $args = array('post_type'=>'presets');
					                        $postSelect = get_posts( $args );
					                        foreach ( $postSelect as $post ) : ?>
					                            <option><?php echo $post->post_title; ?></option>
					                        <?php endforeach; 
					          			?>
					          		</select><br><br>
					          		<div class="add_new_preset">
					          		<p>OR</p>
						          		<label for="prst_title"><?php _e('Title: ', 'est_domain') ?></label><br>
						          		<input type="text" name="prst_title" id="prst_title"><br><br>
						          		<label for="prst_time">
											<?php esc_html_e('Time: ', 'est_domain') ?>
										</label><br>
										<div class="input-group bootstrap-timepicker timepicker">
            								<input id="timepicker1" name="prst_time" type="text" class="form-control input-small">
        								</div>
							    	</div>
									<?php wp_nonce_field('est_form', 'est_nonce_field');?>
									<input type="submit" class="button button-primary" name="add" id="add" value="<?php _e('Add', 'est_domain') ?>">
									<input type="submit" class="button button-primary" name="add_save" value="<?php _e('Add and Save', 'est_domain') ?>">
					          	</form>
					          </div>
					        </div>
					    </div>
					  </div>
					  </div>
					</div>
				</div>

		<?php 
	
	}

	function add_preset() {
			// Form validation
			if(isset($_POST['add_save']) && isset($_POST['est_nonce_field']) && wp_verify_nonce($_POST['est_nonce_field'], 'est_form')) {
				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['prst_title'] ),
					'post_type' 		=> 'presets',
					'post_status' 		=> 'publish'
				);

				$prst_id = wp_insert_post($post_information);

				update_post_meta($prst_id, 'time', $_POST['prst_time']);

				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['prst_title'] ),
					'post_type' 		=> 'estimates',
					'post_status' 		=> 'publish'
				);

				$est_id = wp_insert_post($post_information);

				update_post_meta($est_id, 'text', $prst_id);
			}
		}

		function add_estimate() {
				// Form validation
			if(isset($_POST['add']) && isset($_POST['est_nonce_field']) && wp_verify_nonce($_POST['est_nonce_field'], 'est_form')) {
				$presetInfo = get_page_by_title( $_POST['prst_select'], OBJECT, $post_type = 'presets' );
				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['prst_select'] ),
					'post_type' 		=> 'estimates',
					'post_status' 		=> 'publish'
				);

				$prst_id = wp_insert_post($post_information);

				update_post_meta($prst_id, 'text', $presetInfo->ID);
			}
		}
}

new Estimate();