<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>

	<div id="primary">
		<div id="content" class="clearfix">
			<div class="horizontal">
			<?php

				while ( have_posts() ) :
					the_post();	 
					get_template_part( 'content', 'single' );

		/* Getting the post meta data from the database. */
				$fullName = get_post_meta(get_the_ID(), 'full-name', true);
				$email = get_post_meta(get_the_ID(), 'email', true);
				$bio = get_post_meta(get_the_ID(), 'bio', true);
				$location = get_post_meta(get_the_ID(), 'location', true);

			/* Creating an array of the post meta data. */
				$variables =["Full Name" => $fullName, "Email" => $email, "Bio" => $bio, "location" => $location];

				echo '<table class="displayTable" ';
				
/* Creating a table with the post meta data. */
				foreach ($variables as $key => $value) {
					if ($value) {
						echo '<tr>';
						echo '<td class="tkeydata">' . ucwords($key) .' :'.'</td>';
						echo '<td class="tvalue">' . esc_html($value) . '</td>';
						echo '</tr>';
					}
				}
				echo '</table>';

		/* Getting the image from the database and displaying it. */
				$image = wp_get_attachment_image_url( get_post_meta(get_the_ID(), 'profilePicture', true), 'large');
				echo '<img class="displayImage" src="' . $image . '" alt="user profile image" width="100%">';

			endwhile;
		echo '</div>'.'</div>'.'</div>';

// colormag_sidebar_select();

get_footer();
