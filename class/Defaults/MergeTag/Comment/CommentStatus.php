<?php
/**
 * Comment status merge tag
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\MergeTag\Comment;

use BracketSpace\Notification\Defaults\MergeTag\StringTag;


/**
 * Comment status merge tag class
 */
class CommentStatus extends StringTag {

	/**
	 * Trigger property to get the comment data from
	 *
	 * @var string
	 */
	protected $property_name = 'comment';

	/**
     * Merge tag constructor
     *
     * @since 5.0.0
     * @param array $params merge tag configuration params.
     */
    public function __construct( $params = array() ) {

    	if ( isset( $params['property_name'] ) && ! empty( $params['property_name'] ) ) {
    		$this->property_name = $params['property_name'];
    	}

		$args = wp_parse_args( $params, array(
			'slug'        => 'comment_status',
			'name'        => __( 'Comment status', 'notification' ),
			'description' => __( 'Approved', 'notification' ),
			'example'     => true,
			'resolver'    => function() {
				if ( $this->trigger->{ $this->property_name }->comment_approved  == 1 ) {
					return __( 'Approved', 'notification' );
				} elseif ( $this->trigger->{ $this->property_name }->comment_approved  == 0 ) {
					return __( 'Unapproved', 'notification' );
				} elseif ( $this->trigger->{ $this->property_name }->comment_approved  == 'spam' ) {
					return __( 'Marked as spam', 'notification' );
				} elseif ( $this->trigger->{ $this->property_name }->comment_approved  == 'trash' ) {
					return __( 'Trashed', 'notification' );
				}
 			},
		) );

    	parent::__construct( $args );

	}

	/**
	 * Function for checking requirements
	 *
	 * @return boolean
	 */
	public function check_requirements( ) {
		return isset( $this->trigger->{ $this->property_name }->comment_approved );
	}

}
