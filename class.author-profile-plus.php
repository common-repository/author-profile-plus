<?php

// Author_Profile_Plus class

final class Author_Profile_Plus {
	
	function __construct () {
	
		// Setup WordPress hooks
		add_action( 'init', array( &$this, 'initialize' ), 10 );
	
		// Setup custom user fields
		add_action( 'show_user_profile', array( &$this, 'user_profile_fields' ), 10 );
		add_action( 'edit_user_profile', array( &$this, 'user_profile_fields' ), 10 );
		add_action( 'profile_update', array( &$this, 'save_user_profile_fields' ), 10, 1 );
		
		// Enqueue admin scripts/styles
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ), 10 );
		
		// Setup options page
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		
		// Setup AJAX save hook (no nopriv, must be logged in)
		add_action( 'wp_ajax_save_author_fields', 'Author_Profile_Plus::save_user_profile_fields' );
					
	}
	
	public static function initialize () {
	
		// Shortcode handler
		add_shortcode( 'author-profile', 'Author_Profile_Plus::shortcode_author_profile' );
		
		// Content piggyback
		add_action( 'the_content', 'Author_Profile_Plus::the_content_piggyback' );
		
		// Enqueue font awesome in the front end
		if( !is_admin() ) {
		
			wp_enqueue_style( 'font-awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );
			wp_enqueue_style( 'author-profile-plus-theme-css', plugins_url( 'assets/css/theme.css', __FILE__ ) );
			
		}
	
	}
	
	public static function admin_scripts () {
	
		// Font Awesome
		wp_enqueue_style( 'font-awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );
		
		// Plugin styles
		wp_enqueue_style( 'author-profile-plus-css', plugins_url( 'assets/css/style.css', __FILE__ ) );
		
		// Plugin scripts
		wp_enqueue_script( 'author-profile-plus-scripts', plugins_url( 'assets/js/scripts.js', __FILE__ ), array( 'jquery' ) );
		
		// Initialize AJAX dependencies
		wp_localize_script( 'jquery', 'AJAX', array(
			'url' => admin_url( 'admin-ajax.php' ),
			'template_directory' => get_bloginfo( 'template_directory' )
		));
	
	}
	
	public static function shortcode_author_profile ( $atts ) {
	
		global $post;
		
		// Scrub user id
		$user_id 	= isset( $atts['id'] ) ? $atts['id'] : $post->post_author;
		$user 		= get_user_by( 'id', $user_id );
		$meta 		= get_user_meta( $user_id, '' );
		
		// Start output buffering
		ob_start();
		
		?>
		
		
		<div class="author-profile author-profile-<?php echo $user_id; ?>">
		
			<p class="about">About <?php echo empty( $meta['app_author_name'][0] ) ? $user->data->display_name : $meta['app_author_name'][0] ; ?></p>
			
			<?php if( !empty( $meta['app_author_bio'][0] ) ): ?>
			<blockquote class="bio">
				
				<?php
				if( !empty( $meta['app_author_avatar'][0] ) )
					echo wp_get_attachment_image( $meta['app_author_avatar'][0], 'thumbnail', false, array( 'class' => 'avatar' ) );
				else
					echo '<div class="gravatar-wrapper">' . get_avatar( $user_id, 96 ) . '</div>';
				?>
				
				<?php echo $meta['app_author_bio'][0]; ?>
				
			</blockquote>
			<?php endif; ?>
			
			<ul class="social">
				<?php if( !empty( $meta['app_author_facebook'][0] ) ): ?><li><a href="<?php echo $meta['app_author_facebook'][0]; ?>"><i class="fa fa-facebook-square"></i></a></li><?php endif; ?>
				<?php if( !empty( $meta['app_author_twitter'][0] ) ): ?><li><a href="https://twitter.com/<?php echo $meta['app_author_twitter'][0]; ?>"><i class="fa fa-twitter"></i></a></li><?php endif; ?>
				<?php if( !empty( $meta['app_author_gplus'][0] ) ): ?><li><a href="<?php echo $meta['app_author_gplus'][0]; ?>"><i class="fa fa-google-plus-square"></i></a></li><?php endif; ?>
				<?php if( !empty( $meta['app_author_linkedin'][0] ) ): ?><li><a href="<?php echo $meta['app_author_linkedin'][0]; ?>"><i class="fa fa-linkedin-square"></i></a></li><?php endif; ?>
			</ul>
			
		</div>
		
		<?php
		
		$buffer = ob_get_contents();
		ob_end_clean();
		
		return $buffer;
	
	}
	
	public static function the_content_piggyback ( $content ) {
	
		global $post;
		
		$post_types = get_option( 'app_post_types' );
		
		if( ( empty( $post_types ) && $post->post_type == 'post' ) || in_array( $post->post_type, $post_types ) )
			return $content .= do_shortcode('[author-profile]');
			
		return $content;
	
	}
	
	public static function admin_menu () {
	
		// Add menu page which allows the user to specify which post types to work with
		add_options_page( 'Author Profile Plus', 'Author Profile Plus', 'manage_options', 'author-profile-plus', array( __CLASS__, '_options_page' )  );
	
	}
	
	public static function user_profile_fields ( $user ) {
	
		?>
		
		<div id="author-profile-plus-wrapper">
			
			<hr />
			
			<h3><i class="fa fa-align-left"></i>&nbsp; Author Profile Plus</h3>
			
			<table class="form-table author-profile-plus-fields">
			
				<tbody>
				
					<!-- Author Name -->
					<tr>
						<th>
							<label for="app_author_name">Author name (optional)</label>
						</th>
						<td>
							<input class="regular-text" id="app_author_name" name="app_author_name" type="text" value="<?php echo get_user_meta( $user->ID, 'app_author_name', true ); ?>" /><br />
							<span class="description">Name to display on author. If blank, will fall back to default Display Name</span>
						</td>
					</tr>
					<!-- (End) Author Name -->
					
					<!-- Author Biography -->
					<tr>
						<th>
							<label for="app_author_bio">Biography</label>
						</th>
						<td>
							<textarea id="app_author_bio" name="app_author_bio" cols="30" rows="5"><?php echo get_user_meta( $user->ID, 'app_author_bio', true ); ?></textarea><br />
							<span class="description">Author biography. If blank, will fall back to user biography if one exists</span>
						</td>
					</tr>
					<!-- (End) Author Biography -->
					
					<!-- Author Custom Avatar -->
					<tr>
						<th>
							<label for="app_author_avatar">Custom Avatar</label>
						</th>
						<td>
							<?php if( $image_id = get_user_meta( $user->ID, 'app_author_avatar', true ) ): ?>
							
								<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
								<br />
								<p>
									<input type="checkbox" name="app_remove_avatar" />&nbsp;Remove avatar
								</p>
								<br />
							
							<?php endif; ?>
							<input id="app_author_avatar" name="app_author_avatar" type="file" /><br />
							<span class="description">Author custom avatar. If not set, will fall back to WordPress user avatar</span>
						</td>
					</tr>
					<!-- (End) Author Custom Avatar -->
					
					<!-- Author Facebook -->
					<tr>
						<th>
							<label for="app_author_facebook"><i class="fa fa-facebook-square"></i>&nbsp; Facebook</label>
						</th>
						<td>
							<input class="regular-text" id="app_author_facebook" name="app_author_facebook" type="text" value="<?php echo get_user_meta( $user->ID, 'app_author_facebook', true ); ?>" /><br />
							<span class="description">Author Facebook link</span>
						</td>
					</tr>
					<!-- (End) Author Facebook -->
					
					<!-- Author Twitter -->
					<tr>
						<th>
							<label for="app_author_twitter"><i class="fa fa-twitter"></i>&nbsp; Twitter</label>
						</th>
						<td>
							<input class="regular-text" id="app_author_twitter" name="app_author_twitter" type="text" value="<?php echo get_user_meta( $user->ID, 'app_author_twitter', true ); ?>" /><br />
							<span class="description">Author Twitter username (EG, &quot;@birdbrainlogic&quot;)</span>
						</td>
					</tr>
					<!-- (End) Author Twitter -->
					
					<!-- Author Google Plus -->
					<tr>
						<th>
							<label for="app_author_gplus"><i class="fa fa-google-plus-square"></i>&nbsp; Google Plus</label>
						</th>
						<td>
							<input class="regular-text" id="app_author_gplus" name="app_author_gplus" type="text" value="<?php echo get_user_meta( $user->ID, 'app_author_gplus', true ); ?>" /><br />
							<span class="description">Author Google Plus profile page</span>
						</td>
					</tr>
					<!-- (End) Author Google Plus -->
					
					<!-- Author LinkedIn -->
					<tr>
						<th>
							<label for="app_author_linkedin"><i class="fa fa-linkedin-square"></i>&nbsp; LinkedIn</label>
						</th>
						<td>
							<input class="regular-text" id="app_author_linkedin" name="app_author_linkedin" type="text" value="<?php echo get_user_meta( $user->ID, 'app_author_linkedin', true ); ?>" /><br />
							<span class="description">Author LinkedIn profile page</span>
						</td>
					</tr>
					<!-- (End) Author LinkedIn -->
					
				</tbody>
			
			</table>
		
			<hr />
			
			<input type="hidden" name="action" value="save_author_fields" />
			<input type="hidden" name="user_id" value="<?php echo $user->ID; ?>" />
			
		</div>
		
		<?php
	
	}
	
	public static function save_user_profile_fields ( $user_id = NULL ) {
		
		// Scrub user id
		if( $user_id == NULL && isset( $_REQUEST['user_id'] ) )
			$user_id = $_REQUEST['user_id'];

		// Save only these fields!
		$fields = array(
		
			'app_author_name',
			'app_author_bio',
			'app_author_facebook',
			'app_author_twitter',
			'app_author_gplus',
			'app_author_linkedin'
		
		);
		
		foreach( $fields as $field )
			update_user_meta( $user_id, $field, $_REQUEST[$field] );
			
		// If a new one has been uploaded, save custom avatar
		if( !empty( $_FILES['app_author_avatar']['name'] ) ) {
		
			$avatar_id = media_handle_upload( 'app_author_avatar', 0 );
			update_user_meta( $user_id, 'app_author_avatar', $avatar_id );
		
		}
		
		// Delete avatar if checkbox is ticked
		if( isset( $_REQUEST['app_remove_avatar'] ) && $_REQUEST['app_remove_avatar'] == 'on' )
			update_user_meta( $user_id, 'app_author_avatar', '' );
		
		if( defined( 'DOING_AJAX' ) && DOING_AJAX )
			wp_send_json_success();
		
	}
	
	// Handler for rendering the options page (Settings -> Post Navigator)
	public static function _options_page () {
	
		// Process form submission if there is one
		if( !empty( $_POST ) )
			update_option( 'app_post_types', $_POST['post_types'] );
		
		// Get all post types and filter by what the user has selected
		// Fallback to "post" and "page" types if no selection has been made yet
		$_post_types 	= get_post_types();
		$post_types 	= get_option( 'app_post_types' );

		if( empty( $post_types ) || !is_array( $post_types ) )
			$post_types = array( 'post' );
		
		// Render options in-line with WordPress core styling
		echo '<div class="wrap" id="author-profile-plus-settings">';
		
		echo '	<div class="icon32" id="icon-options-general"><br></div>';
		echo '	<h2>Author Profile Plus</h2>';
		
		echo '	<form id="author-profile-plus-options" method="post" action="#">';
		echo '	<table class="form-table">';
	
		echo '		<tr valign="top">';	
		echo '			<th scope="row">';
		echo '				<strong>Supported Post Types</strong><br /><em style="font-weight:100;">choose which post types Author Profile Plus should work with</em>';
		echo '			</th>';
		echo '			<td valign="top" align="left">';
		
		foreach( $_post_types as $post_type_slug ) {
			
			$post_type = get_post_type_object( $post_type_slug );
			
			if( $post_type->public == 1 )
				echo '<input type="checkbox" name="post_types[]" value="' . esc_attr( $post_type->name ) . '"' . ( in_array( $post_type_slug, $post_types ) ? ' checked="checked"' : NULL ) . '>&nbsp;&nbsp;' . $post_type->labels->singular_name . '<br />';
		
		}
		
		echo '			</td>';
		echo '		</tr>';
		
		echo '		<tr>';
		echo '			<td valign="top" align="left" colspan="100">';
		echo '				<input type="submit" class="button button-primary" value="Save Changes" />';
		echo '			</td>';
		echo '		</tr>';
		
		echo '	</table>';
		echo '</form>';
		
		echo '</div>';
	
	}
	
}

?>