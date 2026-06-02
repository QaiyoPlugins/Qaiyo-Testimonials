<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Qt_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		wp_register_style( 'qt-frontend', QT_URL . 'assets/css/frontend.css', array(), QT_VERSION );
		wp_register_script( 'qt-frontend', QT_URL . 'assets/js/frontend.js', array(), QT_VERSION, true );
	}

	public static function enqueue() {
		wp_enqueue_style( 'qt-frontend' );
		wp_enqueue_script( 'qt-frontend' );
	}
}
