<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. بارگذاری استایل‌های قالب والد و استایل‌های اختصاصی فرنووا
 */
add_action( 'wp_enqueue_scripts', 'furnova_enqueue_styles', 20 );
function furnova_enqueue_styles() {
    wp_enqueue_style( 'hello-elementor-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'furnova-main-style', get_stylesheet_uri(), array( 'hello-elementor-style' ), '1.0.0' );
    wp_enqueue_style( 'furnova-custom-css', get_stylesheet_directory_uri() . '/assets/css/furnova-custom.css', array( 'furnova-main-style' ), '1.0.0' );
}

/**
 * 2. اعلام پشتیبانی کامل از WooCommerce و گالری محصولات
 */
add_action( 'after_setup_theme', 'furnova_woocommerce_support' );
function furnova_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}

/**
 * 3. نمایش تصویر دوم گالری محصول در هاور کارت‌های فروشگاه
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'furnova_hover_secondary_image', 11 );
function furnova_hover_secondary_image() {
    global $product;
    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }
    $attachment_ids = $product->get_gallery_image_ids();
    if ( ! empty( $attachment_ids ) ) {
        $secondary_image_id = $attachment_ids[0];
        echo wp_get_attachment_image( $secondary_image_id, 'woocommerce_thumbnail', false, array( 'class' => 'furnova-secondary-img' ) );
    }
}

/**
 * 4. شورت‌کد نمایش دایره‌های رنگ محصول در Loop Grid المنتور
 * استفاده: [furnova_product_colors]
 */
add_shortcode( 'furnova_product_colors', 'furnova_display_product_color_dots' );
function furnova_display_product_color_dots() {
    global $product;
    if ( ! is_a( $product, 'WC_Product' ) || ! $product->is_type( 'variable' ) ) {
        return '';
    }

    $attributes = $product->get_variation_attributes();
    $color_taxonomy = 'pa_color'; 

    if ( isset( $attributes[ $color_taxonomy ] ) ) {
        $colors = $attributes[ $color_taxonomy ];
        $output = '<div class="furnova-color-swatches">';
        foreach ( $colors as $color_slug ) {
            $term = get_term_by( 'slug', $color_slug, $color_taxonomy );
            if ( $term ) {
                $hex = get_term_meta( $term->term_id, 'product_attribute_color', true );
                $bg_color = ! empty( $hex ) ? esc_attr( $hex ) : esc_attr( $color_slug );
                $output .= sprintf(
                    '<span class="furnova-swatch-dot" style="background-color: %s;" title="%s"></span>',
                    $bg_color,
                    esc_attr( $term->name )
                );
            }
        }
        $output .= '</div>';
        return $output;
    }
    return '';
}

/**
 * 5. شورت‌کدهای نوار بالای فروشگاه (تعداد محصولات و مرتب‌سازی)
 */
add_shortcode( 'furnova_shop_result_count', function() {
    if ( function_exists( 'woocommerce_result_count' ) ) {
        ob_start();
        woocommerce_result_count();
        return ob_get_clean();
    }
    return '';
});

add_shortcode( 'furnova_shop_ordering', function() {
    if ( function_exists( 'woocommerce_catalog_ordering' ) ) {
        ob_start();
        woocommerce_catalog_ordering();
        return ob_get_clean();
    }
    return '';
});

/**
 * 6. افزودن منوی راهنمای نصب سریع قالب فرنووا در پیشخوان وردپرس
 */
add_action( 'admin_menu', 'furnova_theme_setup_menu' );
function furnova_theme_setup_menu() {
    add_theme_page(
        'راه‌اندازی قالب فرنووا',
        'راه‌اندازی فرنووا',
        'manage_options',
        'furnova-setup',
        'furnova_setup_page_html'
    );
}

function furnova_setup_page_html() {
    ?>
    <div class="wrap" style="direction: rtl; font-family: Vazirmatn, Tahoma, sans-serif; background: #FFF9F3; padding: 30px; border-radius: 20px; border: 1px solid #E8D5C3; margin-top: 20px;">
        <h1 style="color: #29221E; font-weight: 800;">خوش آمدید به قالب اختصاصی فروشگاه فرنووا (Furnova)</h1>
        <p style="color: #744329; font-size: 15px;">برای تکمیل نصب در کمتر از ۵ دقیقه، مراحل زیر را به ترتیب انجام دهید:</p>
        <ol style="line-height: 2.2; color: #29221E; font-size: 14px;">
            <li>مطمئن شوید افزونه‌های <strong>WooCommerce</strong>، <strong>Elementor</strong> و <strong>Elementor Pro</strong> نصب و فعال هستند.</li>
            <li>به مسیر <strong>المنتور &gt; تنظیمات &gt; ویژگی‌ها</strong> بروید و <strong>Flexbox Container</strong> را فعال کنید.</li>
            <li>فایل‌های JSON موجود در پوشه <code>/templates/</code> قالب را در بخش <strong>قالب‌ها &gt; سازنده پوسته (Theme Builder)</strong> ایمپورت نمایید.</li>
            <li>در بخش <strong>محصولات &gt; ویژگی‌ها</strong>، یک ویژگی با نامک <code>color</code> بسازید تا دایره‌های رنگ روی کارت محصولات نمایش داده شوند.</li>
        </ol>
    </div>
    <?php
}