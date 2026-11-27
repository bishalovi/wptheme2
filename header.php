<?php
defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() . '/assets/fonts/HindSiliguri-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( get_template_directory_uri() . '/assets/fonts/Inter-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<?php wp_head(); // OFFICIAL HOOK — never removed ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); // OFFICIAL HOOK — never removed ?>

<?php if ( get_theme_mod( 'swift_announcement_show', '1' ) && get_theme_mod( 'swift_announcement', '' ) ) : ?>
<div class="swift-announcement-bar" role="banner">
	<p><?php echo esc_html( get_theme_mod( 'swift_announcement', '' ) ); ?></p>
</div>
<?php endif; ?>

<header class="swift-header" role="banner">
	<div class="swift-header__inner">
		<!-- Logo -->
		<div class="swift-header__logo">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="swift-site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
			}
			?>
		</div>

		<!-- Live AJAX Search -->
		<div class="swift-header__search" role="search">
			<form class="swift-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
				<label for="swift-search-input" class="screen-reader-text"><?php esc_html_e( 'পণ্য খুঁজুন', 'swift-woo' ); ?></label>
				<input
					type="search"
					id="swift-search-input"
					class="swift-search-input"
					name="s"
					placeholder="<?php esc_attr_e( 'পণ্য খুঁজুন...', 'swift-woo' ); ?>"
					autocomplete="off"
					aria-label="<?php esc_attr_e( 'পণ্য খুঁজুন', 'swift-woo' ); ?>"
				>
				<input type="hidden" name="post_type" value="product">
				<button type="submit" class="swift-search-submit" aria-label="<?php esc_attr_e( 'সার্চ করুন', 'swift-woo' ); ?>">
					<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				</button>
				<div class="swift-search-results" aria-live="polite"></div>
			</form>
		</div>

		<!-- Mini Cart -->
		<div class="swift-header__cart">
			<a href="<?php echo esc_url( swift_woo_get_wc_url( 'cart' ) ); ?>" class="swift-cart-link" aria-label="<?php esc_attr_e( 'শপিং কার্ট দেখুন', 'swift-woo' ); ?>">
				<svg aria-hidden="true" focusable="false" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
				<span class="swift-cart-count"><?php echo absint( swift_woo_cart_count() ); ?></span>
			</a>
		</div>

		<!-- Mobile Hamburger -->
		<button class="swift-menu-toggle" aria-label="<?php esc_attr_e( 'মেনু খুলুন', 'swift-woo' ); ?>" aria-expanded="false" aria-controls="swift-primary-nav">
			<span></span><span></span><span></span>
		</button>
	</div>

	<!-- Primary Navigation -->
	<nav id="swift-primary-nav" class="swift-primary-nav" aria-label="<?php esc_attr_e( 'প্রাথমিক মেনু', 'swift-woo' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'menu_class'     => 'swift-nav-list',
			'container'      => false,
			'fallback_cb'    => false,
		) );
		?>
	</nav>
</header>

<!-- Mobile Bottom Sticky Navigation -->
<nav class="swift-mobile-bottom-nav" aria-label="<?php esc_attr_e( 'মোবাইল নেভিগেশন', 'swift-woo' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="swift-bottom-nav__item <?php echo is_front_page() ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'হোম', 'swift-woo' ); ?>">
		<svg aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
		<span><?php esc_html_e( 'হোম', 'swift-woo' ); ?></span>
	</a>
	<a href="<?php echo esc_url( swift_woo_get_wc_url( 'shop' ) ); ?>" class="swift-bottom-nav__item" aria-label="<?php esc_attr_e( 'ক্যাটাগরি', 'swift-woo' ); ?>">
		<svg aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
		<span><?php esc_html_e( 'ক্যাটাগরি', 'swift-woo' ); ?></span>
	</a>
	<a href="<?php echo esc_url( swift_woo_get_wc_url( 'cart' ) ); ?>" class="swift-bottom-nav__item" aria-label="<?php esc_attr_e( 'কার্ট', 'swift-woo' ); ?>">
		<svg aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
		<span><?php esc_html_e( 'কার্ট', 'swift-woo' ); ?> <span class="swift-cart-count"><?php echo absint( swift_woo_cart_count() ); ?></span></span>
	</a>
</nav>
