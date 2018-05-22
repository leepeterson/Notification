<?php
/**
 * Taxonomy ID merge tag
 *
 * Requirements:
 * - Trigger property of the post type slug with WP_Taxonomy object
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\MergeTag\Taxonomy;

use BracketSpace\Notification\Defaults\MergeTag\IntegerTag;


/**
 * Taxonomy ID merge tag class
 */
class TaxonomyID extends IntegerTag {

	/**
	 * Taxonomy Type slug
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
     * Merge tag constructor
     *
     * @since 5.0.0
     * @param array $params merge tag configuration params.
     */
    public function __construct( $params = array() ) {

    	if ( isset( $params['taxonomy'] ) ) {
    		$this->taxonomy = $params['taxonomy'];
    	} else {
    		$this->taxonomy = 'taxonomy';
    	}

    	$args = wp_parse_args( $params, array(
			'slug'        => $this->taxonomy . '_term_ID',
			// translators: singular taxonomy name.
			'name'        => sprintf( __( '%s ID', 'notification' ), $this->get_nicename() ),
			'description' => '35',
			'example'     => true,
			'resolver'    => function( $trigger ) {
				return $trigger->{ $this->taxonomy }->ID;
			},
		) );

    	parent::__construct( $args );

	}

	/**
	 * Function for checking requirements
	 *
	 * @return boolean
	 */
	public function check_requirements() {
		return isset( $this->trigger->{ $this->taxonomy }->ID );
	}

	/**
	 * Gets nice, translated taxonomy name
	 *
	 * @since  5.0.0
	 * @return string taxonomy name
	 */
	public function get_nicename() {
		$taxonomy = get_taxonomy( $this->taxonomy );
		if ( empty( $taxonomy ) ) {
			return '';
		}
		return $taxonomy->labels->singular_name;
	}

}
