<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\Common\Admin;

/**
 * Model for an admin notice.
 *
 * @package Inpsyde\MultilingualPress\Common\Admin
 * @since   3.0.0
 */
class AdminNotice {

	/**
	 * @var string
	 */
	private $classes;

	/**
	 * @var string
	 */
	private $content;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Admin notice content.
	 * @param array  $args    Optional. Configuration arguments. Defaults to empty array.
	 */
	public function __construct( $content, array $args = [] ) {

		$this->content = (string) $content;

		$args = array_merge( [
			'type'        => 'success',
			'classes'     => [],
			'dismissible' => true,
		], $args );

		$classes = array_merge( [
			'notice',
			"notice-{$args['type']}",
		], (array) $args['classes'] );
		if ( $args['dismissible'] ) {
			$classes[] = 'is-dismissible';
		}
		$this->classes = implode( ' ', array_unique( array_map( 'trim', $classes ) ) );
	}

	/**
	 * Renders the admin notice.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function render() {

		?>
		<div class="<?php echo esc_attr( $this->classes ); ?>">
			<?php echo $this->content; ?>
		</div>
		<?php
	}
}
