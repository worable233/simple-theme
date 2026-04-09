<?php
/**
 * Simple Theme 鐨勪富棰樺紩瀵间笌 WordPress 闆嗘垚銆? *
 * @package SimpleTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SIMPLE_THEME_HANDLE' ) ) {
	define( 'SIMPLE_THEME_HANDLE', 'simple-theme-app' );
}

/**
 * 娉ㄥ唽涓婚鏀寔涓庤彍鍗曚綅缃€? */
function simple_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'post-formats',
		array(
			'aside',
		)
	);
	add_theme_support(
		'html5',
		array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
			'search-form',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'simple-theme' ),
			'footer'  => __( 'Footer Navigation', 'simple-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'simple_theme_setup' );

function simple_theme_register_shuoshuo_post_type() {
	$labels = array(
		'name'               => __( '说说', 'simple-theme' ),
		'singular_name'      => __( '说说', 'simple-theme' ),
		'menu_name'          => __( '说说', 'simple-theme' ),
		'name_admin_bar'     => __( '说说', 'simple-theme' ),
		'add_new'            => __( '新建', 'simple-theme' ),
		'add_new_item'       => __( '新建说说', 'simple-theme' ),
		'new_item'           => __( '新说说', 'simple-theme' ),
		'edit_item'          => __( '编辑说说', 'simple-theme' ),
		'view_item'          => __( '查看说说', 'simple-theme' ),
		'all_items'          => __( '全部说说', 'simple-theme' ),
		'search_items'       => __( '搜索说说', 'simple-theme' ),
		'not_found'          => __( '还没有说说内容。', 'simple-theme' ),
		'not_found_in_trash' => __( '回收站里没有说说内容。', 'simple-theme' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'shuoshuo' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_icon'          => 'dashicons-format-status',
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
		'taxonomies'         => array( 'category', 'post_tag', 'post_format' ),
	);

	register_post_type( 'shuoshuo', $args );
}
add_action( 'init', 'simple_theme_register_shuoshuo_post_type' );

/**
 * 璇诲彇 Vite manifest锛屼緵 WordPress 鏄犲皠鐪熷疄浜х墿鏂囦欢鍚嶃€? *
 * @return array<string, mixed>
 */
function simple_theme_get_manifest() {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$manifest_path = get_theme_file_path( 'dist/.vite/manifest.json' );

	if ( ! is_readable( $manifest_path ) ) {
		$manifest = array();
		return $manifest;
	}

	$manifest_contents = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- 杩欓噷璇诲彇鐨勬槸涓婚鐩綍鍐呯殑鏈湴 manifest 鏂囦欢銆?
	if ( false === $manifest_contents ) {
		$manifest = array();
		return $manifest;
	}

	$decoded_manifest = json_decode( $manifest_contents, true );
	$manifest         = is_array( $decoded_manifest ) ? $decoded_manifest : array();

	return $manifest;
}

/**
 * 鐢熸垚鍓嶇璧勬簮鐗堟湰鍙凤紝閬垮厤娴忚鍣ㄧ紦瀛樻棫鏂囦欢銆? *
 * @param string $relative_path 鐩稿涓婚鐩綍鐨勬枃浠惰矾寰?
 * @return string
 */
function simple_theme_get_asset_version( $relative_path ) {
	$absolute_path = get_theme_file_path( $relative_path );

	if ( is_readable( $absolute_path ) ) {
		return (string) filemtime( $absolute_path );
	}

	return wp_get_theme()->get( 'Version' );
}

/**
 * 鍚戝墠绔敞鍏ヤ富棰橀厤缃紝閬垮厤鍦?Vue 閲屽啓姝荤珯鐐瑰湴鍧€銆? *
 * @return array<string, mixed>
 */
function simple_theme_get_frontend_config() {
	return array(
		'siteUrl'  => trailingslashit( site_url( '/' ) ),
		'homeUrl'  => trailingslashit( home_url( '/' ) ),
		'restRoot' => esc_url_raw( trailingslashit( rest_url() ) ),
		'themeUrl' => get_theme_file_uri(),
		'routes'   => array(
			'resolveUrl' => esc_url_raw( rest_url( 'simple-theme/v1/resolve-url' ) ),
			'menusBase'  => esc_url_raw( rest_url( 'simple-theme/v1/navigation' ) ),
			'siteInfo'   => esc_url_raw( rest_url( 'simple-theme/v1/site-info' ) ),
			'collection' => esc_url_raw( rest_url( 'simple-theme/v1/collection' ) ),
		),
	);
}

/**
 * 娉ㄥ唽涓婚鏍峰紡涓庢墦鍖呭悗鐨勫墠绔祫婧愩€? */
function simple_theme_enqueue_assets() {
	$manifest = simple_theme_get_manifest();

	wp_enqueue_style(
		'simple-theme-style',
		get_stylesheet_uri(),
		array(),
		simple_theme_get_asset_version( 'style.css' )
	);

	if ( empty( $manifest['src/main.ts'] ) || empty( $manifest['src/main.ts']['file'] ) ) {
		return;
	}

	$entry      = $manifest['src/main.ts'];
	$script_uri = get_theme_file_uri( 'dist/' . ltrim( $entry['file'], '/' ) );
	$script_ver = simple_theme_get_asset_version( 'dist/' . ltrim( $entry['file'], '/' ) );

	if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
		foreach ( $entry['css'] as $index => $css_file ) {
			$relative_css_path = 'dist/' . ltrim( $css_file, '/' );

			wp_enqueue_style(
				"simple-theme-bundle-{$index}",
				get_theme_file_uri( $relative_css_path ),
				array( 'simple-theme-style' ),
				simple_theme_get_asset_version( $relative_css_path )
			);
		}
	}

	wp_enqueue_script(
		SIMPLE_THEME_HANDLE,
		$script_uri,
		array(),
		$script_ver,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_add_inline_script(
		SIMPLE_THEME_HANDLE,
		'window.SimpleThemeConfig = ' . wp_json_encode( simple_theme_get_frontend_config() ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'simple_theme_enqueue_assets' );

/**
 * 娉ㄥ唽鍓嶇闇€瑕佺殑鑷畾涔?REST 璺敱銆? */
function simple_theme_register_rest_routes() {
	register_rest_route(
		'simple-theme/v1',
		'/site-info',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'simple_theme_get_site_info',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/navigation/(?P<location>[a-zA-Z0-9_-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'simple_theme_get_navigation',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/resolve-url',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'simple_theme_resolve_path',
			'permission_callback' => '__return_true',
			'args'                => array(
				'path' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/comments/(?P<post_id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'simple_theme_get_comments',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/comments',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'simple_theme_create_comment',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/comment-like',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'simple_theme_like_comment',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/collection',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'simple_theme_get_collection',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/home-posts',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'simple_theme_get_home_posts',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'simple-theme/v1',
		'/track-view',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'simple_theme_track_post_view',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'simple_theme_register_rest_routes' );

function simple_theme_sanitize_choice( $value, array $allowed, $default ) {
	$value = (string) $value;
	return in_array( $value, $allowed, true ) ? $value : $default;
}

function simple_theme_sanitize_bool( $value ) {
	return ! empty( $value );
}

function simple_theme_get_theme_mod_number( $key, $default, $min, $max ) {
	$value = (int) get_theme_mod( $key, $default );
	return max( $min, min( $max, $value ) );
}

function simple_theme_get_post_term_names( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $terms ) ) {
		return array();
	}

	return array_values(
		array_map(
			static function ( $term ) {
				return $term->name;
			},
			$terms
		)
	);
}

function simple_theme_calculate_post_stats( $post ) {
	$content_text = wp_strip_all_tags( (string) $post->post_content );
	$word_count   = str_word_count( $content_text ) + mb_strlen( preg_replace( '/\s+/u', '', $content_text ) ) / 2;
	$word_count   = (int) max( 0, floor( $word_count ) );
	$reading_time = (int) max( 1, ceil( $word_count / 260 ) );

	return array(
		'wordCount'   => $word_count,
		'readingTime' => $reading_time,
	);
}

function simple_theme_format_post_item( WP_Post $post ) {
	$post_id     = (int) $post->ID;
	$stats       = simple_theme_calculate_post_stats( $post );
	$view_count  = (int) get_post_meta( $post_id, 'simple_theme_view_count', true );
	$excerpt     = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 42, '…' );
	$excerpt_html = wpautop( wp_kses_post( $excerpt ) );

	return array(
		'id'             => $post_id,
		'date'           => get_post_time( DATE_RFC3339, true, $post_id ),
		'modified'       => get_post_modified_time( DATE_RFC3339, true, $post_id ),
		'link'           => get_permalink( $post_id ),
		'type'           => get_post_type( $post_id ),
		'comment_status' => $post->comment_status,
		'title'          => array( 'rendered' => get_the_title( $post_id ) ),
		'excerpt'        => array( 'rendered' => $excerpt_html ),
		'categories'     => simple_theme_get_post_term_names( $post_id, 'category' ),
		'tags'           => simple_theme_get_post_term_names( $post_id, 'post_tag' ),
		'featuredImage'  => get_the_post_thumbnail_url( $post_id, 'large' ) ?: '',
		'commentCount'   => (int) get_comments_number( $post_id ),
		'viewCount'      => max( 0, $view_count ),
		'wordCount'      => $stats['wordCount'],
		'readingTime'    => $stats['readingTime'],
	);
}

function simple_theme_get_site_info() {
	$comment_show_email   = (bool) get_theme_mod( 'simple_theme_comment_show_email', true );
	$comment_show_url     = (bool) get_theme_mod( 'simple_theme_comment_show_url', true );
	$comment_show_cookies = (bool) get_theme_mod( 'simple_theme_comment_show_cookies_optin', (bool) get_option( 'show_comments_cookies_opt_in' ) );

	return new WP_REST_Response(
		array(
			'name'          => get_bloginfo( 'name' ),
			'description'   => get_bloginfo( 'description' ),
			'url'           => home_url( '/' ),
			'introTitle'    => (string) get_theme_mod( 'simple_theme_intro_title', get_bloginfo( 'name' ) ),
			'introSubtitle' => (string) get_theme_mod( 'simple_theme_intro_subtitle', get_bloginfo( 'description' ) ),
			'footerHtml'    => (string) get_theme_mod( 'simple_theme_footer_html', '<p>© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '</p><p>感谢你的来访。</p>' ),
			'footerLinks'   => array_values(
				array_filter(
					array(
						array(
							'label' => (string) get_theme_mod( 'simple_theme_footer_link_one_label', 'WordPress' ),
							'url'   => (string) get_theme_mod( 'simple_theme_footer_link_one_url', 'https://wordpress.org/' ),
						),
						array(
							'label' => (string) get_theme_mod( 'simple_theme_footer_link_two_label', get_bloginfo( 'name' ) ),
							'url'   => (string) get_theme_mod( 'simple_theme_footer_link_two_url', home_url( '/' ) ),
						),
					),
					static function ( $item ) {
						return ! empty( $item['label'] ) && ! empty( $item['url'] );
					}
				)
			),
			'hero'          => array(
				'enabled'           => (bool) get_theme_mod( 'simple_theme_hero_enabled', true ),
				'displayMode'       => simple_theme_sanitize_choice( get_theme_mod( 'simple_theme_hero_display_mode', 'inset' ), array( 'full', 'half', 'inset' ), 'inset' ),
				'useImage'          => (bool) get_theme_mod( 'simple_theme_hero_use_image', false ),
				'image'             => (string) get_theme_mod( 'simple_theme_hero_image', '' ),
				'showAvatar'        => (bool) get_theme_mod( 'simple_theme_hero_show_avatar', false ),
				'avatar'            => (string) get_theme_mod( 'simple_theme_hero_avatar', '' ),
				'title'             => (string) get_theme_mod( 'simple_theme_hero_title', get_bloginfo( 'name' ) ),
				'subtitle'          => (string) get_theme_mod( 'simple_theme_hero_subtitle', get_bloginfo( 'description' ) ),
				'typewriterEnabled' => (bool) get_theme_mod( 'simple_theme_hero_typewriter_enabled', false ),
				'typewriterInterval'=> (int) get_theme_mod( 'simple_theme_hero_typewriter_interval', 110 ),
				'typewriterTexts'   => (string) get_theme_mod( 'simple_theme_hero_typewriter_texts', '' ),
			),
			'theme'         => array(
				'homePostColumns' => simple_theme_sanitize_choice( get_theme_mod( 'simple_theme_home_post_columns', '2' ), array( '1', '2', '4' ), '2' ),
				'primaryColor'    => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_primary_color', '#8a5a44' ) ) ?: '#8a5a44',
				'bodyFont'        => (string) get_theme_mod( 'simple_theme_body_font', '"Noto Sans SC", "PingFang SC", "Microsoft YaHei", sans-serif' ),
				'headingFont'     => (string) get_theme_mod( 'simple_theme_heading_font', '"Noto Serif SC", "Source Han Serif SC", serif' ),
				'radius'          => simple_theme_sanitize_choice( get_theme_mod( 'simple_theme_radius', 'medium' ), array( 'small', 'medium', 'large' ), 'medium' ),
				'shadow'          => simple_theme_sanitize_choice( get_theme_mod( 'simple_theme_shadow', 'small' ), array( 'none', 'small', 'medium', 'large' ), 'small' ),
				'backgroundLight' => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_background_light', '#fcfbf7' ) ) ?: '#fcfbf7',
				'backgroundDark'  => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_background_dark', '#111315' ) ) ?: '#111315',
				'cardLight'       => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_card_light', '#ffffff' ) ) ?: '#ffffff',
				'cardDark'        => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_card_dark', '#171a1d' ) ) ?: '#171a1d',
				'foregroundLight' => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_foreground_light', '#1f2937' ) ) ?: '#1f2937',
				'foregroundDark'  => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_foreground_dark', '#f7f7f2' ) ) ?: '#f7f7f2',
				'accentLight'     => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_accent_light', '#f3ecdf' ) ) ?: '#f3ecdf',
				'accentDark'      => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_accent_dark', '#22282d' ) ) ?: '#22282d',
				'borderLight'     => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_border_light', '#e5d8c5' ) ) ?: '#e5d8c5',
				'borderDark'      => sanitize_hex_color( (string) get_theme_mod( 'simple_theme_border_dark', '#343c44' ) ) ?: '#343c44',
				'containerMaxWidth'=> simple_theme_get_theme_mod_number( 'simple_theme_container_max_width', 1240, 960, 1680 ),
				'articleMaxWidth' => simple_theme_get_theme_mod_number( 'simple_theme_article_max_width', 860, 680, 1200 ),
				'heroOverlay'     => max( 0.1, min( 0.8, (float) get_theme_mod( 'simple_theme_hero_overlay', 0.34 ) ) ),
				'cardMeta'        => array(
					'showCategory'      => (bool) get_theme_mod( 'simple_theme_meta_show_category', true ),
					'showPublishDate'   => (bool) get_theme_mod( 'simple_theme_meta_show_publish_date', true ),
					'showModifiedDate'  => (bool) get_theme_mod( 'simple_theme_meta_show_modified_date', false ),
					'showCommentCount'  => (bool) get_theme_mod( 'simple_theme_meta_show_comment_count', true ),
					'showViewCount'     => (bool) get_theme_mod( 'simple_theme_meta_show_view_count', true ),
					'showReadingTime'   => (bool) get_theme_mod( 'simple_theme_meta_show_reading_time', true ),
					'showWordCount'     => (bool) get_theme_mod( 'simple_theme_meta_show_word_count', false ),
				),
			),
			'comments'      => array(
				'requireNameEmail' => (bool) get_option( 'require_name_email' ),
				'registrationOnly' => (bool) get_option( 'comment_registration' ),
				'showEmailField'   => $comment_show_email,
				'showUrlField'     => $comment_show_url,
				'showCookiesOptIn' => $comment_show_cookies,
			),
			'collections'   => array(
				'postsTitle'         => (string) get_theme_mod( 'simple_theme_posts_title', '最新文章' ),
				'postsSubtitle'      => (string) get_theme_mod( 'simple_theme_posts_subtitle', '整理过的长文、笔记与项目更新。' ),
				'shuoshuoTitle'      => (string) get_theme_mod( 'simple_theme_shuoshuo_title', '最近说说' ),
				'shuoshuoSubtitle'   => (string) get_theme_mod( 'simple_theme_shuoshuo_subtitle', '更轻量的动态、灵感和碎片记录。' ),
				'showShuoshuoSection'=> (bool) get_theme_mod( 'simple_theme_show_shuoshuo_section', true ),
				'homePostCount'      => simple_theme_get_theme_mod_number( 'simple_theme_home_post_count', 6, 3, 20 ),
				'homeShuoshuoCount'  => simple_theme_get_theme_mod_number( 'simple_theme_home_shuoshuo_count', 3, 0, 12 ),
				'shuoshuoPageSize'   => simple_theme_get_theme_mod_number( 'simple_theme_shuoshuo_page_size', 12, 6, 24 ),
			),
		),
		200
	);
}

function simple_theme_register_customizer_settings( $wp_customize ) {
	$wp_customize->add_section(
		'simple_theme_home_intro',
		array(
			'title'       => __( '首页文案', 'simple-theme' ),
			'description' => __( '设置首页主标题和副标题。', 'simple-theme' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting( 'simple_theme_intro_title', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_intro_title', array( 'label' => __( '首页主标题', 'simple-theme' ), 'section' => 'simple_theme_home_intro', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_intro_subtitle', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_intro_subtitle', array( 'label' => __( '首页副标题', 'simple-theme' ), 'section' => 'simple_theme_home_intro', 'type' => 'textarea' ) );

	$wp_customize->add_section(
		'simple_theme_appearance',
		array(
			'title'       => __( '前台主题样式', 'simple-theme' ),
			'description' => __( '调整列数、颜色、字体、圆角、阴影。', 'simple-theme' ),
			'priority'    => 31,
		)
	);
	$wp_customize->add_setting( 'simple_theme_home_post_columns', array( 'default' => '2', 'sanitize_callback' => static function( $v ) { return simple_theme_sanitize_choice( $v, array( '1', '2', '4' ), '2' ); }, 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_home_post_columns', array( 'label' => __( '首页文章列数', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'radio', 'choices' => array( '1' => __( '单列', 'simple-theme' ), '2' => __( '双列', 'simple-theme' ), '4' => __( '四列', 'simple-theme' ) ) ) );
	$wp_customize->add_setting( 'simple_theme_primary_color', array( 'default' => '#8a5a44', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_primary_color', array( 'label' => __( '主题主色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_body_font', array( 'default' => '"Noto Sans SC", "PingFang SC", "Microsoft YaHei", sans-serif', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_body_font', array( 'label' => __( '正文字体（font-family）', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_heading_font', array( 'default' => '"Noto Serif SC", "Source Han Serif SC", serif', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_heading_font', array( 'label' => __( '标题字体（font-family）', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_radius', array( 'default' => 'medium', 'sanitize_callback' => static function( $v ) { return simple_theme_sanitize_choice( $v, array( 'small', 'medium', 'large' ), 'medium' ); }, 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_radius', array( 'label' => __( '圆角强度', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'radio', 'choices' => array( 'small' => __( '小', 'simple-theme' ), 'medium' => __( '中', 'simple-theme' ), 'large' => __( '大', 'simple-theme' ) ) ) );
	$wp_customize->add_setting( 'simple_theme_shadow', array( 'default' => 'small', 'sanitize_callback' => static function( $v ) { return simple_theme_sanitize_choice( $v, array( 'none', 'small', 'medium', 'large' ), 'small' ); }, 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_shadow', array( 'label' => __( '阴影强度', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'radio', 'choices' => array( 'none' => __( '无', 'simple-theme' ), 'small' => __( '轻', 'simple-theme' ), 'medium' => __( '中', 'simple-theme' ), 'large' => __( '重', 'simple-theme' ) ) ) );
	$wp_customize->add_setting( 'simple_theme_background_light', array( 'default' => '#fcfbf7', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_background_light', array( 'label' => __( '浅色背景', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_background_dark', array( 'default' => '#111315', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_background_dark', array( 'label' => __( '深色背景', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_card_light', array( 'default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_card_light', array( 'label' => __( '浅色卡片背景', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_card_dark', array( 'default' => '#171a1d', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_card_dark', array( 'label' => __( '深色卡片背景', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_foreground_light', array( 'default' => '#1f2937', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_foreground_light', array( 'label' => __( '浅色正文颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_foreground_dark', array( 'default' => '#f7f7f2', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_foreground_dark', array( 'label' => __( '深色正文颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_accent_light', array( 'default' => '#f3ecdf', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_accent_light', array( 'label' => __( '浅色点缀颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_accent_dark', array( 'default' => '#22282d', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_accent_dark', array( 'label' => __( '深色点缀颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_border_light', array( 'default' => '#e5d8c5', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_border_light', array( 'label' => __( '浅色边框颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_border_dark', array( 'default' => '#343c44', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh' ) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'simple_theme_border_dark', array( 'label' => __( '深色边框颜色', 'simple-theme' ), 'section' => 'simple_theme_appearance' ) ) );
	$wp_customize->add_setting( 'simple_theme_container_max_width', array( 'default' => 1240, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_container_max_width', array( 'label' => __( '页面最大宽度（px）', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'number', 'input_attrs' => array( 'min' => 960, 'max' => 1680, 'step' => 10 ) ) );
	$wp_customize->add_setting( 'simple_theme_article_max_width', array( 'default' => 860, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_article_max_width', array( 'label' => __( '正文最大宽度（px）', 'simple-theme' ), 'section' => 'simple_theme_appearance', 'type' => 'number', 'input_attrs' => array( 'min' => 680, 'max' => 1200, 'step' => 10 ) ) );

	$wp_customize->add_section( 'simple_theme_hero', array( 'title' => __( '首页全屏封面', 'simple-theme' ), 'priority' => 32 ) );
	$wp_customize->add_setting( 'simple_theme_hero_enabled', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_enabled', array( 'label' => __( '启用首页封面', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_hero_display_mode', array( 'default' => 'inset', 'sanitize_callback' => static function( $v ) { return simple_theme_sanitize_choice( $v, array( 'full', 'half', 'inset' ), 'inset' ); }, 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_display_mode', array( 'label' => __( '封面展示模式', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'radio', 'choices' => array( 'full' => __( '占满全屏', 'simple-theme' ), 'half' => __( '占满半屏', 'simple-theme' ), 'inset' => __( '有外边距（当前样式）', 'simple-theme' ) ) ) );
	$wp_customize->add_setting( 'simple_theme_hero_use_image', array( 'default' => false, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_use_image', array( 'label' => __( '使用背景图', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_hero_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_image', array( 'label' => __( '背景图 URL', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'url' ) );
	$wp_customize->add_setting( 'simple_theme_hero_show_avatar', array( 'default' => false, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_show_avatar', array( 'label' => __( '显示头像', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_hero_avatar', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_avatar', array( 'label' => __( '头像 URL', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'url' ) );
	$wp_customize->add_setting( 'simple_theme_hero_title', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_title', array( 'label' => __( '封面主标题', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_hero_subtitle', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_subtitle', array( 'label' => __( '封面副标题', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'simple_theme_hero_typewriter_enabled', array( 'default' => false, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_typewriter_enabled', array( 'label' => __( '开启副标题打字动画', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_hero_typewriter_interval', array( 'default' => 110, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_typewriter_interval', array( 'label' => __( '打字间隔（毫秒）', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'number' ) );
	$wp_customize->add_setting( 'simple_theme_hero_typewriter_texts', array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_typewriter_texts', array( 'label' => __( '打字内容（每行一条）', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'simple_theme_hero_overlay', array( 'default' => 0.34, 'sanitize_callback' => 'floatval', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_hero_overlay', array( 'label' => __( '封面遮罩透明度（0.1 - 0.8）', 'simple-theme' ), 'section' => 'simple_theme_hero', 'type' => 'number', 'input_attrs' => array( 'min' => 0.1, 'max' => 0.8, 'step' => 0.01 ) ) );

	$wp_customize->add_section( 'simple_theme_footer', array( 'title' => __( '页脚', 'simple-theme' ), 'priority' => 33 ) );
	$wp_customize->add_setting( 'simple_theme_footer_html', array( 'default' => '<p>© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '</p><p>感谢你的来访。</p>', 'sanitize_callback' => 'wp_kses_post', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_footer_html', array( 'label' => __( '页脚 HTML（支持简单样式）', 'simple-theme' ), 'section' => 'simple_theme_footer', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'simple_theme_footer_link_one_label', array( 'default' => 'WordPress', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_footer_link_one_label', array( 'label' => __( '页脚按钮 1 文案', 'simple-theme' ), 'section' => 'simple_theme_footer', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_footer_link_one_url', array( 'default' => 'https://wordpress.org/', 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_footer_link_one_url', array( 'label' => __( '页脚按钮 1 链接', 'simple-theme' ), 'section' => 'simple_theme_footer', 'type' => 'url' ) );
	$wp_customize->add_setting( 'simple_theme_footer_link_two_label', array( 'default' => get_bloginfo( 'name' ), 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_footer_link_two_label', array( 'label' => __( '页脚按钮 2 文案', 'simple-theme' ), 'section' => 'simple_theme_footer', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_footer_link_two_url', array( 'default' => home_url( '/' ), 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_footer_link_two_url', array( 'label' => __( '页脚按钮 2 链接', 'simple-theme' ), 'section' => 'simple_theme_footer', 'type' => 'url' ) );

	$wp_customize->add_section( 'simple_theme_comment_form', array( 'title' => __( '评论表单', 'simple-theme' ), 'priority' => 34 ) );
	$wp_customize->add_setting( 'simple_theme_comment_show_email', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_comment_show_email', array( 'label' => __( '显示邮箱输入框', 'simple-theme' ), 'section' => 'simple_theme_comment_form', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_comment_show_url', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_comment_show_url', array( 'label' => __( '显示网址输入框', 'simple-theme' ), 'section' => 'simple_theme_comment_form', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_comment_show_cookies_optin', array( 'default' => (bool) get_option( 'show_comments_cookies_opt_in' ), 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_comment_show_cookies_optin', array( 'label' => __( '显示“记住信息”选项', 'simple-theme' ), 'section' => 'simple_theme_comment_form', 'type' => 'checkbox' ) );

	$wp_customize->add_section( 'simple_theme_post_card_meta', array( 'title' => __( '首页卡片信息', 'simple-theme' ), 'priority' => 35 ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_category', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_category', array( 'label' => __( '显示分类', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_publish_date', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_publish_date', array( 'label' => __( '显示发布日期', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_modified_date', array( 'default' => false, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_modified_date', array( 'label' => __( '显示修改日期', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_comment_count', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_comment_count', array( 'label' => __( '显示评论数', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_view_count', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_view_count', array( 'label' => __( '显示浏览量', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_reading_time', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_reading_time', array( 'label' => __( '显示预计阅读时间', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_meta_show_word_count', array( 'default' => false, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_meta_show_word_count', array( 'label' => __( '显示字数', 'simple-theme' ), 'section' => 'simple_theme_post_card_meta', 'type' => 'checkbox' ) );

	$wp_customize->add_section( 'simple_theme_collections', array( 'title' => __( '文章与说说列表', 'simple-theme' ), 'priority' => 36 ) );
	$wp_customize->add_setting( 'simple_theme_posts_title', array( 'default' => '最新文章', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_posts_title', array( 'label' => __( '文章区标题', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_posts_subtitle', array( 'default' => '整理过的长文、笔记与项目更新。', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_posts_subtitle', array( 'label' => __( '文章区副标题', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'simple_theme_show_shuoshuo_section', array( 'default' => true, 'sanitize_callback' => 'simple_theme_sanitize_bool', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_show_shuoshuo_section', array( 'label' => __( '首页显示说说区', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'simple_theme_shuoshuo_title', array( 'default' => '最近说说', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_shuoshuo_title', array( 'label' => __( '说说区标题', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'text' ) );
	$wp_customize->add_setting( 'simple_theme_shuoshuo_subtitle', array( 'default' => '更轻量的动态、灵感和碎片记录。', 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_shuoshuo_subtitle', array( 'label' => __( '说说区副标题', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'textarea' ) );
	$wp_customize->add_setting( 'simple_theme_home_post_count', array( 'default' => 6, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_home_post_count', array( 'label' => __( '首页文章数量', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'number', 'input_attrs' => array( 'min' => 3, 'max' => 20, 'step' => 1 ) ) );
	$wp_customize->add_setting( 'simple_theme_home_shuoshuo_count', array( 'default' => 3, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_home_shuoshuo_count', array( 'label' => __( '首页说说数量', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'number', 'input_attrs' => array( 'min' => 0, 'max' => 12, 'step' => 1 ) ) );
	$wp_customize->add_setting( 'simple_theme_shuoshuo_page_size', array( 'default' => 12, 'sanitize_callback' => 'absint', 'transport' => 'refresh' ) );
	$wp_customize->add_control( 'simple_theme_shuoshuo_page_size', array( 'label' => __( '说说页数量', 'simple-theme' ), 'section' => 'simple_theme_collections', 'type' => 'number', 'input_attrs' => array( 'min' => 6, 'max' => 24, 'step' => 1 ) ) );
}
add_action( 'customize_register', 'simple_theme_register_customizer_settings' );

function simple_theme_get_collection( WP_REST_Request $request ) {
	$type     = sanitize_key( (string) $request->get_param( 'type' ) );
	$limit    = max( 1, min( 24, (int) $request->get_param( 'limit' ) ?: 6 ) );
	$page     = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
	$taxonomy = sanitize_key( (string) $request->get_param( 'taxonomy' ) );
	$term_id  = (int) $request->get_param( 'termId' );
	$allowed  = array( 'post', 'page', 'shuoshuo' );

	if ( ! in_array( $type, $allowed, true ) ) {
		return new WP_Error( 'invalid_type', '不支持的内容类型。', array( 'status' => 400 ) );
	}

	$args = array(
		'post_type'           => $type,
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'paged'               => $page,
		'ignore_sticky_posts' => true,
	);

	if ( $term_id > 0 && in_array( $taxonomy, array( 'category', 'post_tag' ), true ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		);
	}

	$query = new WP_Query( $args );
	$items = array_map( 'simple_theme_format_post_item', $query->posts );

	wp_reset_postdata();

	return new WP_REST_Response(
		array(
			'items'      => $items,
			'total'      => (int) $query->found_posts,
			'totalPages' => (int) $query->max_num_pages,
			'page'       => $page,
			'perPage'    => $limit,
		),
		200
	);
}

function simple_theme_get_home_posts( WP_REST_Request $request ) {
	$limit = max( 1, min( 20, (int) $request->get_param( 'limit' ) ?: 6 ) );
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
		)
	);
	$items = array_map( 'simple_theme_format_post_item', $query->posts );
	wp_reset_postdata();
	return new WP_REST_Response( $items, 200 );
}

function simple_theme_track_post_view( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'postId' );
	if ( $post_id <= 0 ) {
		return new WP_Error( 'invalid_post', '无效文章。', array( 'status' => 400 ) );
	}
	$view_count = (int) get_post_meta( $post_id, 'simple_theme_view_count', true );
	$view_count++;
	update_post_meta( $post_id, 'simple_theme_view_count', $view_count );
	return new WP_REST_Response( array( 'viewCount' => $view_count ), 200 );
}

function simple_theme_detect_browser( $agent ) {
	$agent = (string) $agent;
	if ( false !== stripos( $agent, 'edg/' ) ) {
		return 'Edge';
	}
	if ( false !== stripos( $agent, 'chrome/' ) ) {
		return 'Chrome';
	}
	if ( false !== stripos( $agent, 'safari/' ) && false === stripos( $agent, 'chrome/' ) ) {
		return 'Safari';
	}
	if ( false !== stripos( $agent, 'firefox/' ) ) {
		return 'Firefox';
	}
	return '未知浏览器';
}

function simple_theme_detect_os( $agent ) {
	$agent = (string) $agent;
	if ( false !== stripos( $agent, 'windows' ) ) {
		return 'Windows';
	}
	if ( false !== stripos( $agent, 'android' ) ) {
		return 'Android';
	}
	if ( false !== stripos( $agent, 'iphone' ) || false !== stripos( $agent, 'ipad' ) || false !== stripos( $agent, 'ios' ) ) {
		return 'iOS';
	}
	if ( false !== stripos( $agent, 'mac os' ) || false !== stripos( $agent, 'macintosh' ) ) {
		return 'macOS';
	}
	if ( false !== stripos( $agent, 'linux' ) ) {
		return 'Linux';
	}
	return '未知系统';
}

function simple_theme_format_comment_item( WP_Comment $comment ) {
	$likes    = (int) get_comment_meta( $comment->comment_ID, 'simple_theme_like_count', true );
	$location = (string) get_comment_meta( $comment->comment_ID, 'simple_theme_location', true );
	$browser  = (string) get_comment_meta( $comment->comment_ID, 'simple_theme_browser', true );
	$os       = (string) get_comment_meta( $comment->comment_ID, 'simple_theme_os', true );
	$ip_mask  = (string) get_comment_meta( $comment->comment_ID, 'simple_theme_ip_mask', true );

	return array(
		'id'         => (int) $comment->comment_ID,
		'parent'     => (int) $comment->comment_parent,
		'date'       => mysql_to_rfc3339( $comment->comment_date_gmt ),
		'authorName' => (string) $comment->comment_author,
		'content'    => array(
			'rendered' => wpautop( wp_kses_post( $comment->comment_content ) ),
		),
		'likes'      => max( 0, $likes ),
		'metaInfo'   => array(
			'location' => '' !== $location ? $location : '未知地区',
			'browser'  => '' !== $browser ? $browser : simple_theme_detect_browser( (string) $comment->comment_agent ),
			'os'       => '' !== $os ? $os : simple_theme_detect_os( (string) $comment->comment_agent ),
			'ipMask'   => '' !== $ip_mask ? $ip_mask : '隐私保护',
		),
	);
}

function simple_theme_build_comment_tree( array $items, $parent_id = 0 ) {
	$branch = array();
	foreach ( $items as $item ) {
		if ( (int) $item['parent'] !== (int) $parent_id ) {
			continue;
		}
		$item['children'] = simple_theme_build_comment_tree( $items, (int) $item['id'] );
		$branch[]         = $item;
	}
	return $branch;
}

function simple_theme_get_comments( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'post_id' );
	if ( $post_id <= 0 ) {
		return new WP_REST_Response( array( 'items' => array() ), 200 );
	}

	$comments = get_comments(
		array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'order'   => 'ASC',
			'orderby' => 'comment_date_gmt',
			'number'  => 200,
		)
	);

	$formatted = array_map( 'simple_theme_format_comment_item', $comments );
	$tree      = simple_theme_build_comment_tree( $formatted, 0 );

	return new WP_REST_Response(
		array(
			'items' => $tree,
		),
		200
	);
}

function simple_theme_create_comment( WP_REST_Request $request ) {
	$post_id = (int) $request->get_param( 'post' );
	$post    = get_post( $post_id );
	if ( ! $post || 'open' !== $post->comment_status ) {
		return new WP_Error( 'comment_closed', '当前文章未开启评论。', array( 'status' => 403 ) );
	}

	$comment_data = array(
		'comment_post_ID'      => $post_id,
		'comment_parent'       => (int) $request->get_param( 'parent' ),
		'comment_author'       => sanitize_text_field( (string) $request->get_param( 'author_name' ) ),
		'comment_author_email' => sanitize_email( (string) $request->get_param( 'author_email' ) ),
		'comment_author_url'   => esc_url_raw( (string) $request->get_param( 'author_url' ) ),
		'comment_content'      => wp_kses_post( (string) $request->get_param( 'content' ) ),
		'comment_type'         => '',
		'comment_approved'     => 0,
	);

	if ( empty( $comment_data['comment_author'] ) || empty( $comment_data['comment_content'] ) ) {
		return new WP_Error( 'invalid_comment', '请填写昵称和评论内容。', array( 'status' => 400 ) );
	}

	$comment_id = wp_new_comment( wp_slash( $comment_data ), true );
	if ( is_wp_error( $comment_id ) ) {
		return $comment_id;
	}

	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$ip_m  = preg_replace( '/\d+$/', '***', $ip );

	update_comment_meta( $comment_id, 'simple_theme_location', '未知地区' );
	update_comment_meta( $comment_id, 'simple_theme_browser', simple_theme_detect_browser( $agent ) );
	update_comment_meta( $comment_id, 'simple_theme_os', simple_theme_detect_os( $agent ) );
	update_comment_meta( $comment_id, 'simple_theme_ip_mask', $ip_m ? $ip_m : '隐私保护' );
	update_comment_meta( $comment_id, 'simple_theme_like_count', 0 );

	$comment = get_comment( $comment_id );
	return new WP_REST_Response(
		array(
			'item' => $comment instanceof WP_Comment ? simple_theme_format_comment_item( $comment ) : null,
		),
		201
	);
}

function simple_theme_like_comment( WP_REST_Request $request ) {
	$comment_id = (int) $request->get_param( 'commentId' );
	$comment    = get_comment( $comment_id );
	if ( ! $comment instanceof WP_Comment ) {
		return new WP_Error( 'invalid_comment', '评论不存在。', array( 'status' => 404 ) );
	}

	$user_id = get_current_user_id();
	$agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$identity_source = $user_id > 0 ? 'user:' . $user_id : 'guest:' . $ip . '|' . $agent;
	$identity_hash   = md5( $identity_source );
	$like_lock_key   = 'simple_theme_like_lock_' . $comment_id . '_' . $identity_hash;

	if ( get_transient( $like_lock_key ) ) {
		return new WP_Error( 'already_liked', '你已经点过赞了。', array( 'status' => 429 ) );
	}

	$current = (int) get_comment_meta( $comment_id, 'simple_theme_like_count', true );
	$current++;
	update_comment_meta( $comment_id, 'simple_theme_like_count', $current );
	set_transient( $like_lock_key, 1, 5 * YEAR_IN_SECONDS );

	return new WP_REST_Response(
		array(
			'likes' => $current,
		),
		200
	);
}

/**
 * 杈撳嚭鎸囧畾鑿滃崟浣嶇疆鐨勫鑸暟鎹€? *
 * @param WP_REST_Request $request REST 璇锋眰瀵硅薄.
 * @return WP_REST_Response
 */
function simple_theme_get_navigation( WP_REST_Request $request ) {
	$location  = sanitize_key( (string) $request->get_param( 'location' ) );
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return new WP_REST_Response(
			array(
				'items' => array(),
			),
			200
		);
	}

	$menu_items = wp_get_nav_menu_items( $locations[ $location ] );

	if ( empty( $menu_items ) ) {
		return new WP_REST_Response(
			array(
				'items' => array(),
			),
			200
		);
	}

	return new WP_REST_Response(
		array(
			'items' => simple_theme_format_menu_items( $menu_items ),
		),
		200
	);
}

/**
 * 灏?WordPress 鑿滃崟鏉＄洰杞崲涓哄墠绔彲鐩存帴浣跨敤鐨勬爲褰㈢粨鏋勩€? *
 * @param array<int, WP_Post> $items 鑿滃崟鏉＄洰闆嗗悎.
 * @return array<int, array<string, mixed>>
 */
function simple_theme_format_menu_items( array $items ) {
	$flat_items   = array();
	$children_map = array();

	foreach ( $items as $item ) {
		$item_id   = (int) $item->ID;
		$parent_id = (int) $item->menu_item_parent;

		$flat_items[ $item_id ] = array(
			'id'          => $item_id,
			'title'       => html_entity_decode( wp_strip_all_tags( $item->title ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'         => $item->url,
			'path'        => simple_theme_get_internal_path( $item->url ),
			'target'      => $item->target,
			'description' => $item->description,
			'current'     => (bool) $item->current,
		);

		if ( ! isset( $children_map[ $parent_id ] ) ) {
			$children_map[ $parent_id ] = array();
		}

		$children_map[ $parent_id ][] = $item_id;
	}

	return simple_theme_build_menu_tree( $children_map, $flat_items, 0 );
}

/**
 * 閫掑綊鏋勫缓鑿滃崟鏍戙€? *
 * @param array<int, array<int, int>>      $children_map 鐖跺瓙鍏崇郴鏄犲皠.
 * @param array<int, array<string, mixed>> $flat_items 骞抽摵鑿滃崟鏁版嵁.
 * @param int                              $parent_id 褰撳墠鐖惰妭鐐?ID.
 * @return array<int, array<string, mixed>>
 */
function simple_theme_build_menu_tree( array $children_map, array $flat_items, $parent_id = 0 ) {
	$tree = array();

	if ( empty( $children_map[ $parent_id ] ) ) {
		return $tree;
	}

	foreach ( $children_map[ $parent_id ] as $child_id ) {
		if ( empty( $flat_items[ $child_id ] ) ) {
			continue;
		}

		$item             = $flat_items[ $child_id ];
		$item['children'] = simple_theme_build_menu_tree( $children_map, $flat_items, $child_id );
		$tree[]           = $item;
	}

	return $tree;
}

/**
 * 灏嗚彍鍗?URL 杞垚绔欑偣鍐呴儴鍙烦杞矾寰勩€? *
 * @param string $url 鑿滃崟 URL.
 * @return string
 */
function simple_theme_get_internal_path( $url ) {
	if ( empty( $url ) ) {
		return '/';
	}

	$site_parts = wp_parse_url( home_url( '/' ) );
	$url_parts  = wp_parse_url( $url );

	if ( false === $url_parts || false === $site_parts ) {
		return '/';
	}

	$site_host = isset( $site_parts['host'] ) ? $site_parts['host'] : '';
	$url_host  = isset( $url_parts['host'] ) ? $url_parts['host'] : $site_host;

	if ( $site_host !== $url_host ) {
		return $url;
	}

	$site_path = isset( $site_parts['path'] ) ? untrailingslashit( $site_parts['path'] ) : '';
	$path      = isset( $url_parts['path'] ) ? $url_parts['path'] : '/';

	if ( ! empty( $site_path ) && $path === $site_path ) {
		$path = '/';
	} elseif ( ! empty( $site_path ) && 0 === strpos( $path, $site_path . '/' ) ) {
		$path = substr( $path, strlen( $site_path ) );
	}

	$path = '/' . ltrim( (string) $path, '/' );

	if ( ! empty( $url_parts['query'] ) ) {
		$path .= '?' . $url_parts['query'];
	}

	if ( ! empty( $url_parts['fragment'] ) ) {
		$path .= '#' . $url_parts['fragment'];
	}

	return $path;
}

/**
 * 瑙ｆ瀽褰撳墠鍓嶇璺緞瀵瑰簲鐨?WordPress 鍐呭銆? *
 * @param WP_REST_Request $request REST 璇锋眰瀵硅薄.
 * @return WP_REST_Response
 */
function simple_theme_resolve_path( WP_REST_Request $request ) {
	$path = simple_theme_normalize_requested_path( (string) $request->get_param( 'path' ) );

	if ( '/' === $path ) {
		return new WP_REST_Response(
			array(
				'type' => 'home',
			),
			200
		);
	}

	$resolved_url = home_url( $path );
	$post_id      = url_to_postid( $resolved_url );

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );

		if ( $post instanceof WP_Post ) {
			$post_type_object = get_post_type_object( $post->post_type );
			$rest_base        = ( $post_type_object && ! empty( $post_type_object->rest_base ) ) ? $post_type_object->rest_base : $post->post_type;

			return new WP_REST_Response(
				array(
					'type'      => $post->post_type,
					'id'        => $post_id,
					'permalink' => get_permalink( $post_id ),
					'restUrl'   => rest_url( sprintf( 'wp/v2/%s/%d?_embed', $rest_base, $post_id ) ),
				),
				200
			);
		}
	}

	$term = simple_theme_path_to_term( $path );

	if ( $term instanceof WP_Term ) {
		$taxonomy  = get_taxonomy( $term->taxonomy );
		$rest_base = ( $taxonomy && ! empty( $taxonomy->rest_base ) ) ? $taxonomy->rest_base : $term->taxonomy;

		return new WP_REST_Response(
			array(
				'type'      => 'term',
				'id'        => (int) $term->term_id,
				'name'      => $term->name,
				'taxonomy'  => $term->taxonomy,
				'permalink' => get_term_link( $term ),
				'restUrl'   => rest_url( sprintf( 'wp/v2/%s/%d', $rest_base, $term->term_id ) ),
			),
			200
		);
	}

	return new WP_REST_Response(
		array(
			'type'    => '404',
			'message' => '未找到对应的 WordPress 内容。',
		),
		404
	);
}

/**
 * 瑙勮寖鍖栧墠绔紶鏉ョ殑璺緞锛屽吋瀹圭粷瀵?URL 涓庣浉瀵硅矾寰勩€? *
 * @param string $path 鍓嶇璺緞.
 * @return string
 */
function simple_theme_normalize_requested_path( $path ) {
	$trimmed_path = trim( $path );

	if ( '' === $trimmed_path ) {
		return '/';
	}

	if ( false !== strpos( $trimmed_path, '://' ) ) {
		$parsed_url = wp_parse_url( $trimmed_path );

		if ( is_array( $parsed_url ) ) {
			$trimmed_path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '/';

			if ( ! empty( $parsed_url['query'] ) ) {
				$trimmed_path .= '?' . $parsed_url['query'];
			}
		}
	}

	if ( '/' !== $trimmed_path[0] ) {
		$trimmed_path = '/' . $trimmed_path;
	}

	$parts    = explode( '?', $trimmed_path, 2 );
	$parts[0] = preg_replace( '#/+#', '/', $parts[0] );

	return empty( $parts[1] ) ? $parts[0] : $parts[0] . '?' . $parts[1];
}

/**
 * 鏍规嵁璺緞鎺ㄦ柇鍒嗙被鎴栨爣绛惧綊妗ｃ€? *
 * @param string $path 褰撳墠鍓嶇璺緞.
 * @return WP_Term|null
 */
function simple_theme_path_to_term( $path ) {
	$parsed_path = wp_parse_url( home_url( $path ), PHP_URL_PATH );

	if ( ! is_string( $parsed_path ) ) {
		return null;
	}

	$normalized_path = trim( $parsed_path, '/' );
	$site_base       = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$site_base       = is_string( $site_base ) ? trim( $site_base, '/' ) : '';

	if ( '' !== $site_base && $normalized_path === $site_base ) {
		return null;
	}

	if ( '' !== $site_base && 0 === strpos( $normalized_path, $site_base . '/' ) ) {
		$normalized_path = substr( $normalized_path, strlen( $site_base ) + 1 );
	}

	if ( '' === $normalized_path ) {
		return null;
	}

	$category_base = get_option( 'category_base' );
	$tag_base      = get_option( 'tag_base' );

	$taxonomy_map = array(
		'category' => $category_base ? trim( $category_base, '/' ) : 'category',
		'post_tag' => $tag_base ? trim( $tag_base, '/' ) : 'tag',
	);

	foreach ( $taxonomy_map as $taxonomy => $base_slug ) {
		if ( '' === $base_slug || 0 !== strpos( $normalized_path, $base_slug . '/' ) ) {
			continue;
		}

		$term_slug = trim( substr( $normalized_path, strlen( $base_slug ) ), '/' );

		if ( '' === $term_slug ) {
			continue;
		}

		$term = get_term_by( 'slug', $term_slug, $taxonomy );

		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

