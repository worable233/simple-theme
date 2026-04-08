<?php
/**
 * Simple Theme 的主题引导与 WordPress 集成。
 *
 * @package SimpleTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SIMPLE_THEME_HANDLE' ) ) {
	define( 'SIMPLE_THEME_HANDLE', 'simple-theme-app' );
}

/**
 * 注册主题支持与菜单位置。
 */
function simple_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
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

/**
 * 读取 Vite manifest，供 WordPress 映射真实产物文件名。
 *
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

	$manifest_contents = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- 这里读取的是主题目录内的本地 manifest 文件。

	if ( false === $manifest_contents ) {
		$manifest = array();
		return $manifest;
	}

	$decoded_manifest = json_decode( $manifest_contents, true );
	$manifest         = is_array( $decoded_manifest ) ? $decoded_manifest : array();

	return $manifest;
}

/**
 * 生成前端资源版本号，避免浏览器缓存旧文件。
 *
 * @param string $relative_path 相对主题目录的文件路径.
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
 * 向前端注入主题配置，避免在 Vue 里写死站点地址。
 *
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
		),
	);
}

/**
 * 注册主题样式与打包后的前端资源。
 */
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
 * 注册前端需要的自定义 REST 路由。
 */
function simple_theme_register_rest_routes() {
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
}
add_action( 'rest_api_init', 'simple_theme_register_rest_routes' );

/**
 * 输出指定菜单位置的导航数据。
 *
 * @param WP_REST_Request $request REST 请求对象.
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
 * 将 WordPress 菜单条目转换为前端可直接使用的树形结构。
 *
 * @param array<int, WP_Post> $items 菜单条目集合.
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
 * 递归构建菜单树。
 *
 * @param array<int, array<int, int>>      $children_map 父子关系映射.
 * @param array<int, array<string, mixed>> $flat_items 平铺菜单数据.
 * @param int                              $parent_id 当前父节点 ID.
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
 * 将菜单 URL 转成站点内部可跳转路径。
 *
 * @param string $url 菜单 URL.
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
 * 解析当前前端路径对应的 WordPress 内容。
 *
 * @param WP_REST_Request $request REST 请求对象.
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
 * 规范化前端传来的路径，兼容绝对 URL 与相对路径。
 *
 * @param string $path 前端路径.
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
 * 根据路径推断分类或标签归档。
 *
 * @param string $path 当前前端路径.
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
