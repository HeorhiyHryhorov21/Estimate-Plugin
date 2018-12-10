<?php 
/*
Plugin Name: Estimate Plugin
Description: Plugin that estimates
Version: 1.0
Author: George
*/
use Dompdf\Dompdf;

//Exit if Accessed Directly
if(!defined("ABSPATH")) {
	exit();
}

class Estimate {
	function __construct() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'estimate_menu_page') {
  			add_action('admin_enqueue_scripts', array($this, 'est_add_scripts'));
    	}
    	add_action('admin_enqueue_scripts', array($this, 'est_add_post_scripts'));
		add_action('init', array($this, 'est_register_estimate'));
		add_action('add_meta_boxes', array($this, 'est_add_fields_meta_box'));

		add_action('init', array($this, 'est_register_preset'));
		add_action('add_meta_boxes', array($this, 'est_add_presets_meta_box'));

		add_action('save_post', array($this, 'est_save_fields_meta'));

		add_action('save_post', array($this, 'est_save_presets_meta'));

		add_action('wp_ajax_add_save_preset', array($this, 'add_save_preset'));
		add_action('wp_ajax_nopriv_add_save_preset', array($this, 'add_save_preset'));

		add_action('wp_ajax_add_estimate', array($this, 'add_estimate'));
		add_action('wp_ajax_nopriv_add_estimate', array($this, 'add_estimate'));

		add_action('wp_ajax_est_table', array($this, 'est_table'));
		add_action('wp_ajax_nopriv_est_table', array($this, 'est_table'));

		add_action('admin_menu', array($this, 'est_menu_page'));

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'estimates') {
  			add_filter('post_row_actions', array($this, 'post_actions_add_print_link'), 10, 2 );
    	}

		add_action('init', array($this, 'print_estimate'));

	}

	function est_add_scripts() {
		wp_enqueue_style('est-bootstrap-style', plugins_url().'/estimate/css/bootstrap.css');
		wp_enqueue_style('timepicker-css', plugins_url().'/estimate/css/bootstrap-timepicker.min.css');
		wp_enqueue_style('est-style', plugins_url().'/estimate/css/style.css');
		wp_enqueue_script( 'bootstrap-js', plugins_url().'/estimate/js/bootstrap.js', array('jquery'), '4.1.3', true );
		wp_enqueue_script('bootstrap-poper-js', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js');
		wp_enqueue_script('timepicker-js', plugins_url().'/estimate/js/bootstrap-timepicker.min.js');
		wp_register_script('est-js', plugins_url().'/estimate/js/main.js', array('jquery'), '4.1.3', true );
		wp_localize_script('est-js', 'js_object', array('ajax_url' => admin_url('admin-ajax.php')));
		wp_enqueue_script('est-js');
	}


	function est_add_post_scripts() {
		if ( isset($_GET['post'])) {
			wp_enqueue_style('post-boostrap-css', plugins_url().'/estimate/css/bootstrap.css');
			wp_enqueue_style('style-post', plugins_url().'/estimate/css/style-post.css');
			wp_enqueue_script('post-boostrap-js', plugins_url().'/estimate/js/bootstrap.js', array('jquery'), '4.1.3', true);
            wp_enqueue_script('post-js', plugins_url().'/estimate/js/post-main.js', array('jquery'), '4.1.3', true);
		}
		
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
		wp_nonce_field(basename(__FILE__), 'wp_prst_nonce');
		$meta = get_post_meta($post->ID);?>
		<div class="wrapper estimate-fields">
			<div class="form-group">
				<p>
					<label for="est_rate">
						<?php esc_html_e('Rate', 'est_domain') ?>
					</label>
				
				<input type="number" name="est_rate" id="est_rate" value="<?php if(!empty($meta['est_rate'])) echo esc_attr($meta['est_rate'][0]);?>">
				</p>
				<p>
					<label for="est_items">
							<?php esc_html_e('Items', 'est_domain') ?>
					</label>
				</p>
				<table class="table table-striped">
					<tr> 
						<td>№</td>
						<td><?php _e('Title', 'est_domain') ?></td> 
						<td><?php _e('Time', 'est_domain')?></td>
						<td><?php _e('Cost', 'est_domain')?></td>
						<td></td>
					</tr> 

				<p name="est_items" id="est_items">
					<?php
                    $meta_table = get_post_meta($post->ID, 'est_items', true);
					if(!empty($meta_table)) 
						$i = 1;
						foreach ($meta_table as $key) :
                            if(is_array($key)) {
                                $time = explode(':', $key[1]);
                                $time = $time[0] + floor(($time[1] / 60) * 100) / 100 . PHP_EOL;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $key[0]; ?></td>
                                    <td id=<?php echo "time$i"; ?>><?php echo $key[1]; ?></td>
                                    <td id=<?php echo "cost$i"; ?>><?php echo $time * get_post_meta($post->ID, 'est_rate', true) . ' $'; ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                ++$i;
                            } else {
                                $time = explode(':', get_post_meta($key, 'time', true));
                                $time = $time[0] + floor(($time[1] / 60) * 100) / 100 . PHP_EOL;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo get_the_title($key); ?></td>
                                    <td id=<?php echo "time$i"; ?>><?php echo get_post_meta($key, 'time', true); ?></td>
                                    <td id=<?php echo "cost$i"; ?>><?php echo $time * get_post_meta($post->ID, 'est_rate', true) . ' $'; ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                ++$i;
                            }
						endforeach;

				?>
					
				</p>
				</table>


				<h3><?php _e('Summary:', 'est_domain') ?></h3>
					<ul name="est_summary"><?php if(!empty($meta['est_summary'])) echo $meta['est_summary'][0];?></ul>
				<p hidden name="time_sum"></p>
				
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

		if (isset($_POST['est_rate'])) {
			update_post_meta($post_id, 'est_rate', sanitize_text_field($_POST['est_rate']));
            update_post_meta($post_id, 'est_items', get_post_meta($post_id, 'est_items', true));
			update_post_meta($post_id, 'est_summary', '<li><h3 id="estimate_time_sum">'.(float)get_post_meta($post_id, 'time_sum', true).' hours</h3></li><li><h3 id="estimate_money_sum">'.(float)get_post_meta($post_id, 'time_sum', true)*$_POST['est_rate'].' $</h3></li>');	
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
			'show_in_menu' 		=> false,
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

				<input type="text" name="time" id="time" value="<?php if(!empty($meta['time'])) echo esc_attr($meta['time'][0]);?>">
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
				<th scope="col"></th>
				<th scope="col">
					<ul>
						<li>
							<?php _e("Estimate: ", 'est_domain') ?>
						</li>
						<li>
								<input type="text" name="est_title" id="est_title" placeholder="<?php __('Add estimate title') ?>">
						</li>
					</ul>
					</th>  
				<th scope="col">
					<ul>
					<li><?php _e("Rate: ", 'est_domain');?></li>
						<li>
							<a href="#" data-toggle="tooltip" data-placement="top" data-template='<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' title="<?php _e('Press arrow button or write to add rate in $', 'est_domain') ?>">
								    <input type="number" step="1" min="0" name="rate" id="rate" value="0">
							</a>
					</li>
					</ul>
					<th scope="col">
						<input type="button" class="button button-primary" data-toggle="modal" data-target="#add_prst_modal" value="<?php _e('Add Item', 'est_domain') ?>" />
					</th>
					<th scope="col"></th>
				</th>
				</tr>
			</thead>
			<tbody>
			<tr><td>№</td><td><?php _e('Title', 'est_domain') ?></td><td><?php _e('Time', 'est_domain')?></td>
				<td><?php _e('Cost', 'est_domain')?></td><td></td></tr> 
	</tbody>
	<tfoot>
			<tr>
				<td colspan="3"></td>
				<td><input type="submit" class="button button-primary" name="crt_est" id="crt_est" value="<?php _e('Create Estimate', 'est_domain') ?>"/></td>
			</tr>
	</tfoot>
</table>	
			<div class="summary card">
			<div class="card-body">
				<h3><?php _e('Summary:', 'est_domain') ?></h3>
					<p>
						<ul>
							<li><h3 id="estimate_time_sum"></h3></li>
							<li><h3 id="estimate_money_sum"></h3></li>
						</ul>
					</p>
				</div>
			</div>

			<div class="container">
					  <!-- Modal -->
					  <div class="modal fade" id="add_prst_modal" role="dialog">
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
					          			<option selected value="0"><?php _e('Add new Preset', 'est_domain') ?></option>
					          			 <?php
					                        $args = array('post_type'=>'presets', 'order' => 'ASC','numberposts' => -1,);
					                        $postSelect = get_posts( $args );
					                        foreach ( $postSelect as $post ) :
					                        $prstData = get_post($post->ID, OBJECT); ?>
					                            <option><?php echo $prstData->post_title; ?></option>
					                            <option hidden name="prst_select_time" data-id="<?php echo $prstData->ID;?>"><?php echo get_post_meta($prstData->ID, 'time', true); ?></option>
					                        <?php endforeach; 
					          			?>
					          		</select><br><br>

					          		<div class="add_new_preset">
					          		
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
                                    <div class="add_new">
									<input type="submit" class="button button-primary" name="add_save" id="add_save" value="<?php _e('Add and Save', 'est_domain') ?>">
                                    <input type="submit" class="button button-primary" name="add_custom" id="add_custom" value="<?php _e('Add', 'est_domain') ?>">
                                    </div>
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

		function add_save_preset() {
				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['prst_title'] ),
					'post_type' 		=> 'presets',
					'post_status' 		=> 'publish'
				);

				$prst_id = wp_insert_post($post_information);

				update_post_meta($prst_id, 'time', $_POST['prst_time']);

				$preset = get_post($prst_id, OBJECT);
				$resp_id = $preset->ID;
				$resp_title = $preset->post_title;
				$resp_time = get_post_meta($prst_id, 'time', true);
				echo json_encode(array($resp_title, $resp_time, $resp_id));
				wp_die();
			}


		function add_estimate() {
				$post_information = array(
					'post_title' 		=> wp_strip_all_tags( $_POST['est_title'] ),
					'post_type' 		=> 'estimates',
					'post_status' 		=> 'publish'
				);

				$est_id = wp_insert_post($post_information);

				update_post_meta($est_id, 'est_rate', $_POST['est_rate']);				
				update_post_meta($est_id, 'est_items', $_POST['est_items']);
				update_post_meta($est_id, 'est_summary', $_POST['est_summary']);
				update_post_meta($est_id, 'time_sum', $_POST['time_sum']);
				wp_die();
			}

		function post_actions_add_print_link( $actions, $post ) {
			$actions['print'] = '<a href="post.php?id='.$post->ID.'&action=est_print" id="print_link" name="print_link">'. __("Print") .'</a>';
			return $actions;

		}


    function print_estimate() {
        require_once 'dompdf/autoload.inc.php';
        $dompdf = new Dompdf();
        if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'est_print') {
            $estimate_row = get_post($_GET['id'], OBJECT);
            $meta_table = get_post_meta($estimate_row->ID, 'est_items', true);
            $estimate_row_title = $estimate_row->post_title;
            $estimate_row_rate = get_post_meta($estimate_row->ID, 'est_rate', true);
            $est_row_summary = get_post_meta($estimate_row->ID, 'est_summary', true);

            if(!empty($meta_table))
                $i = 1;
                ob_start(); ?>
            <html>
            <head>
                <link rel='stylesheet' href=<?php echo plugins_url().'/estimate/css/bootstrap.css'; ?> type='text/css'/>
                <link rel='stylesheet' href=<?php echo plugins_url().'/estimate/css/style-post-print.css'; ?> type='text/css'/>
            </head>
            <h4 id="est_print_title"><?php echo _e('Estimate title: ', 'est_domain').$estimate_row_title; ?></h4>
            <table class='table table-striped'>
                <tr scope='row'>
                    <td>#</td>
                    <td><?php _e('Title', 'est_domain') ?></td>
                    <td><?php _e('Time', 'est_domain') ?></td>
                    <td><?php _e('Cost', 'est_domain') ?></td>
                    <td></td>
                </tr> <?php
                foreach ($meta_table as $key) :
                    if(is_array($key)) {
                        $time = explode(':', $key[1]);
                        $time = $time[0] + floor(($time[1] / 60) * 100) / 100 . PHP_EOL;
                        ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $key[0]; ?></td>
                            <td id=<?php echo "time$i"; ?>><?php echo $key[1]; ?></td>
                            <td id=<?php echo "cost$i"; ?>><?php echo $time * $estimate_row_rate . ' $'; ?></td>
                            <td></td>
                        </tr>
                        <?php
                        ++$i;
                    } else {
                        $time = explode(':', get_post_meta($key, 'time', true));
                        $time = $time[0] + floor(($time[1] / 60) * 100) / 100 . PHP_EOL;
                        ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo get_the_title($key); ?></td>
                            <td id=<?php echo "time$i"; ?>><?php echo get_post_meta($key, 'time', true); ?></td>
                            <td id=<?php echo "cost$i"; ?>><?php echo $time * $estimate_row_rate . ' $'; ?></td>
                            <td></td>
                        </tr>
                        <?php
                        ++$i;
                    }
                endforeach;
                ?>
            </table>
            <div class='summary card'>
                <div class='card-body'>
                    <ul>
                        <li><h3><?php _e('Summary: ', 'est_domain') ?></h3></li>
                        <li><h3><?php echo _e('Rate: ', 'est_domain').$estimate_row_rate; ?></h3></li>
                        <?php echo $est_row_summary; ?>
                    </ul>
            </div>
            </div>
            </body>
            </html>
            <?php
            $dompdf->loadHtml(ob_get_clean());
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream(__("Estimate-".$estimate_row_title).".pdf");
        }
    }
}

new Estimate();