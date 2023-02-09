<?php
/**
 * Enqueue the parent theme's style.css and the child theme's style.css.
 */
function colormag_child_enqueue_styles()
{
	$parent_style = 'colormag_style'; //parent theme style handle 'colormag_style'
	//Enqueue parent and chid theme style.css
	wp_enqueue_style($parent_style, get_template_directory_uri () . '/style.css');
	wp_enqueue_style(
		'colormag_child_style', get_stylesheet_directory_uri () . '/style.css',
		[$parent_style], wp_get_theme() -> get('Version')
	);
}
add_action('wp_enqueue_scripts', 'colormag_child_enqueue_styles');

/**
 * When WordPress is loading the scripts, load my custom script after jQuery.
 */
function enqueue_custom_scripts()
{
	wp_enqueue_script('cpmscript', get_stylesheet_directory_uri() . "/js/cpmscript.js",['jquery']);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

/**
 * It loads the required files for media uploads.
 */
function media_files()
{
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');
}

/**
 * It creates a new post type called "User Info Collection" and makes it available to the public.
 */
function create_user_info_collection_post_type()
{
	register_post_type(
		'user_info_collection',
		[
			'labels' =>
			[
				'name' => __('User Info Collection'),
				'singular_name' => __('User Info Collection'),
			],
			'public' => true,
			'has_archive' => true,
			'show_ui' => true,
		]
	);
}
add_action('init', 'create_user_info_collection_post_type');



// creating meta box and adding to the post type user_info_collection
function add_custom_meta_box()
{
	add_meta_box('custom_meta_box',
					'User Info Collection Details',
					'render_custom_meta_box',
					'user_info_collection',
					// specify the custom post type
					'normal',
					'high'
	);
}
add_action('add_meta_boxes', 'add_custom_meta_box');

/* A function that creates a custom meta box and adds it to the post type user_info_collection. */
function render_custom_meta_box($post)
{
	// Add a nonce field so we can check for it later
	wp_nonce_field('custom_meta_box', 'custom_meta_box_nonce');

	// Get the existing data
	$full_name = get_post_meta($post->ID, 'full-name', true);
	$email = get_post_meta($post->ID, 'email', true);
	$bio = get_post_meta($post->ID, 'bio', true);
	$location = get_post_meta($post->ID, 'location', true);
	$image = wp_get_attachment_image_url(get_post_meta(get_the_ID(), 'profilePicture', true), 'large');

	// Output the fields
	?>
	<p>
		<label for="full_name">Full Name:</label>
		<input class="widefat" type="text" id="full_name" name="full_name" value="<?php echo esc_attr($full_name); ?>" />
	</p>
	<p>
		<label for="email">Email:</label>
		<input class="widefat" type="email" id="_email" name="_email" value="<?php echo esc_attr($email); ?>" />
	</p>
	<p>
		<label for="bio">Bio:</label>
		<textarea class="widefat" id="bio" name="_bio" required><?php echo esc_textarea($bio); ?></textarea>
	</p>
	<p>
		<label for="location">Location:</label>
		<input class="widefat" type="text" id="location" name="_location" value="<?php echo esc_attr($location); ?>" />
	</p>
	<p>
		<?php echo '<img class="displayImage" src="' . $image . '" alt="user profile image" width="100%">'; ?>
	</p>
	<?php
}

/**
 * It checks if the nonce is set, verifies the nonce, checks if the post is an autosave, checks if the
 * user has permission to edit the post, and then updates the post meta fields
 * 
 * @param post_id The ID of the post that we're saving the meta data for.
 * 
 * @return the value of the post meta field.
 */
function save_custom_meta_box($post_id)
{
	// Check if nonce is
	if (!isset($_POST['custom_meta_box_nonce'])) {
		return;
	}
	// Verify nonce
	if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], 'custom_meta_box')) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything
	// if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	// 	return;
	// }

	// Check the user's permissions
	// if (!current_user_can('edit_post', $post_id)) {
	// 	return;
	// }

/* Updating the post meta fields. */
	update_post_meta($post_id, 'full-name', sanitize_text_field($_POST['full_name']));
	update_post_meta($post_id, 'email', sanitize_email($_POST['_email']));
	update_post_meta($post_id, 'bio', sanitize_textarea_field($_POST['_bio']));
	update_post_meta($post_id, 'location', sanitize_text_field($_POST['_location']));

}
add_action('save_post', 'save_custom_meta_box');


/**
 * It creates a shortcode(user_info_collection) that can be used in a page or post.
 * 
 * @return The function post_contact_form() is being returned.
 */
function user_info_collection()
{
	ob_start();
	post_contact_form();
	return ob_get_clean();
}
add_shortcode('user_info_collection', 'user_info_collection');


/**
 * It adds a section to the customizer, adds a setting to that section, adds a control to that section,
 * and then adds a partial refresh to that setting
 * 
 * @param wp_customize This is the  object.
 */
function theme_customizer_function($wp_customize)
{

	$wp_customize->add_section(
		'landing_panel_home',
		[
			'title' => 'Header color',
			'panel' => 'colormag_global_panel',
		]
	);
	$wp_customize->add_setting(
		'landing_sec_title',
		[
			'default' => 'Landing Panel heading',
			'sanitize_callback' => 'wp_filter_nohtml_kses'
		]
	);

	/* Adding a partial refresh to the landing_sec_title theme mod. */
	// $wp_customize->selective_refresh->add_partial(
	//   'landing_sec_title',
	//   [
	//     'selector' => '.navbar-brand',
	//     'container_inclusive' => false,
	//     'render_callback' => function () {
	//       get_theme_mod('landing_sec_title');
	//     }
	//   ]
	// );

	/* Adding a control to Header in the customizer. */
	$wp_customize->add_control(
		'landing_sec_title',
		[
			'label' => 'Header',
			'section' => 'landing_panel_home',
			'priority' => 1,
		]
	);

}
add_action('customize_register', 'theme_customizer_function');


//Function thst creates a html form and saved its input data into post meta
function post_contact_form()
{

	/* Creating a form. */
	echo '<form onsubmit="return validateForm()" method="post" enctype="multipart/form-data"> 
	
    <label for="full-name">Full Name:</label> 
    <input type="text" id="full-name" name="full-name"  /><br>   

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" /><br>

    <label for="bio">Bio:</label></br>
     <textarea id="bio" name="bio"  /></textarea><br> 

    <label for="location">Location:</label>
    <input type="text" id="location" name="location"  /><br>

		<label for="profilePicture">Profile Picture:</label>
		<input type="file" id="profilePicture" name="profilePicture"/> <br/> 
		

    <input type="submit" name="submit" value="Submit">
  </form>';

	if (isset($_POST['submit'])) {

		/* Taking the data from the form and assigning it to a variable. */
		$post_title = $_POST['full-name'];
		$post_fullname = $_POST['full-name'];
		$post_email = $_POST['email'];
		$post_bio = $_POST['bio'];
		$post_location = $_POST['location'];
		// $post_picture = $_FILES['profilePicture'];

		/* Creating an array of data that will be used to create a new post. */
		$post_arr = [
			'ID' => 0,
			'post_title' => $post_title,
			'post_type' => 'user_info_collection',
			'post_status' => 'publish',
			'meta-input' =>
			[
				'post_fullname' => $post_fullname,
				'post_email' => $post_email,
				'post_bio' => $post_bio,
				'post_location' => $post_location,
				// 'post_picture' => $post_picture,

			]
		];

		/* Inserting a post into the database. */
		$post_id = wp_insert_post($post_arr, true);

		/* Updating the post meta data. */
		update_post_meta($post_id, 'full-name', $post_fullname);
		update_post_meta($post_id, 'email', $post_email);
		update_post_meta($post_id, 'bio', $post_bio);
		update_post_meta($post_id, 'location', $post_location);
		// update_post_meta($post_id, 'profilePicture', $post_picture);

		if (!function_exists('wp_generate_attachment_metadata')) {
			media_files();
		}
		if ($_FILES) {
			foreach ($_FILES as $file => $array) {
				if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
					return "upload error : " . $_FILES[$file]['error'];
				}
				$attach_id = media_handle_upload($file, $post_id);
			}
		}
		if ($attach_id > 0) {
			//and if you want to set that image as Post  then use:
			update_post_meta($post_id, 'profilePicture', $attach_id);
		}


		//sucess message after submitting form 
		echo '<div class="success-message">Form submitted successfully! Thank you for your submission.</div>';
	}

}