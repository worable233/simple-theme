<?php
/**
 * WordPress 主题入口模板，交由 Vue 应用接管前台渲染。
 *
 * @package SimpleTheme
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'simple-theme-shell' ); ?>>
		<?php wp_body_open(); ?>
		<div id="app"></div>
		<?php wp_footer(); ?>
	</body>
</html>
