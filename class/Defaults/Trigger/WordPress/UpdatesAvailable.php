<?php
/**
 * WordPress Updates Available trigger
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\Trigger\WordPress;

use BracketSpace\Notification\Defaults\MergeTag;
use BracketSpace\Notification\Abstracts;

/**
 * WordPress Updates Available trigger class
 */
class UpdatesAvailable extends Abstracts\Trigger {

	/**
	 * Update types
	 *
	 * @var array
	 */
	protected $update_types;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->update_types = array( 'core', 'plugin', 'theme' );

		parent::__construct( 'wordpress/updates_available', __( 'Available updates', 'notification' ) );

		$this->add_action( 'notification_check_wordpress_updates' );

		$this->set_group( __( 'WordPress', 'notification' ) );
		$this->set_description( __( 'Fires periodically when new updates are available', 'notification' ) );

	}

	/**
	 * Assigns action callback args to object
	 *
	 * @return mixed
	 */
	public function action() {

		require_once ABSPATH . '/wp-admin/includes/update.php';

		// Check if any updates are available.
		$has_updates = false;

		foreach ( $this->update_types as $update_type ) {
			if ( $this->has_updates( $update_type ) ) {
				$has_updates = true;
			}
		}

		// Don't send any empty notifications unless the Setting is enabled.
		if ( ! $has_updates && ! notification_get_setting( 'triggers/wordpress/updates_send_anyway' ) ) {
			return false;
		}

	}

	/**
	 * Registers attached merge tags
	 *
	 * @return void
	 */
	public function merge_tags() {

		$this->add_merge_tag( new MergeTag\HtmlTag( array(
			'slug'        => 'updates_list',
			'name'        => __( 'Updates list', 'notification' ),
			'description' => __( 'The lists for core, plugins and themes updates.', 'notification' ),
			'resolver'    => function( $trigger ) {
				$lists = array();

				foreach ( $this->update_types as $update_type ) {
					if ( $this->has_updates( $update_type ) ) {
						$html  = '<h3>' . $this->get_list_title( $update_type ) . '</h3>';
						$html .= call_user_func( array( $this, 'get_' . $update_type . '_updates_list' ) );
						$lists[] = $html;
					}
				}

				if ( empty( $lists ) ) {
					$lists[] = __( 'No updates available.', 'notification' );
				}

				return implode( '<br><br>', $lists );
			},
		) ) );

		$this->add_merge_tag( new MergeTag\IntegerTag( array(
			'slug'        => 'all_updates_count',
			'name'        => __( 'Number of all updates', 'notification' ),
			'resolver'    => function( $trigger ) {
				return $trigger->get_updates_count( 'all' );
			},
		) ) );

		$this->add_merge_tag( new MergeTag\IntegerTag( array(
			'slug'        => 'core_updates_count',
			'name'        => __( 'Number of core updates', 'notification' ),
			'resolver'    => function( $trigger ) {
				return $trigger->get_updates_count( 'core' );
			},
		) ) );

		$this->add_merge_tag( new MergeTag\IntegerTag( array(
			'slug'        => 'plugin_updates_count',
			'name'        => __( 'Number of plugin updates', 'notification' ),
			'resolver'    => function( $trigger ) {
				return $trigger->get_updates_count( 'plugin' );
			},
		) ) );

		$this->add_merge_tag( new MergeTag\IntegerTag( array(
			'slug'        => 'theme_updates_count',
			'name'        => __( 'Number of theme updates', 'notification' ),
			'resolver'    => function( $trigger ) {
				return $trigger->get_updates_count( 'theme' );
			},
		) ) );

	}

	/**
	 * Checks if specific updates are available
	 *
	 * @since  5.1.5
	 * @param  string $update_type update type, core | plugin | theme.
	 * @return boolean
	 */
	public function has_updates( $update_type ) {
		$updates = $this->get_updates_count( $update_type );
		return $updates > 0;
	}

	/**
	 * Gets specific update type title
	 *
	 * @since  5.1.5
	 * @param  string $update_type update type, core | plugin | theme.
	 * @return string
	 */
	public function get_list_title( $update_type ) {

		switch ( $update_type ) {
			case 'core':
				$title = __( 'Core updates', 'notification' );
				break;

			case 'plugin':
				$title = __( 'Plugin updates', 'notification' );
				break;

			case 'theme':
				$title = __( 'Theme updates', 'notification' );
				break;

			default:
				$title = __( 'Updates', 'notification' );
				break;
		}

		return $title;

	}

	/**
	 * Gets core updates list
	 *
	 * @since  5.1.5
	 * @return string
	 */
	public function get_core_updates_list() {

		$updates = get_core_updates();

		foreach ( $updates as $update_key => $update ) {
			if ( $update->current == $update->version ) {
				unset( $updates[ $update_key ] );
			}
		}

		if ( empty( $updates ) ) {
			return '';
		}

		$html = '<ul>';

		foreach ( $updates as $update ) {
			// translators: 1. Update type, 2. Version.
			$html .= '<li>' . sprintf( __( '<strong>WordPress</strong> <i>(%s)</i>: %s', 'notification' ), $update->response, $update->version ) . '</li>';
		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * Gets plugin updates list
	 *
	 * @since  5.1.5
	 * @return string
	 */
	public function get_plugin_updates_list() {

		$updates = get_plugin_updates();

		if ( empty( $updates ) ) {
			return '';
		}

		$html = '<ul>';

		foreach ( $updates as $update ) {
			// translators: 1. Plugin name, 2. Current version, 3. Update version.
			$html .= '<li>' . sprintf( __( '<strong>%s</strong> <i>(current version: %s)</i>: %s', 'notification' ), $update->Name, $update->Version, $update->update->new_version ) . '</li>';
		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * Gets theme updates list
	 *
	 * @since  5.1.5
	 * @return string
	 */
	public function get_theme_updates_list() {

		$updates = get_theme_updates();

		if ( empty( $updates ) ) {
			return '';
		}

		$html = '<ul>';

		foreach ( $updates as $update ) {
			// translators: 1. Theme name, 2. Current version, 3. Update version.
			$html .= '<li>' . sprintf( __( '<strong>%s</strong> <i>(current version: %s)</i>: %s', 'notification' ), $update->Name, $update->Version, $update->update['new_version'] ) . '</li>';
		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * Gets updates count
	 *
	 * @since  5.1.5
	 * @param  string $update_type optional, update type, core | plugin | theme | all, default: all.
	 * @return integer
	 */
	public function get_updates_count( $update_type = 'all' ) {

		if ( $update_type !== 'all' ) {
			$updates = call_user_func( 'get_' . $update_type . '_updates' );

			if ( $update_type == 'core'  ) {
				foreach ( $updates as $update_key => $update ) {
					if ( $update->current == $update->version ) {
						unset( $updates[ $update_key ] );
					}
				}
			}

			return count( $updates );
		}

		$count = 0;

		foreach ( $this->update_types as $update_type ) {
			$count += $this->get_updates_count( $update_type );
		}

		return $count;

	}

}
