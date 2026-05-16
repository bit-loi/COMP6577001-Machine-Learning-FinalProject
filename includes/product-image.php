<?php
/**
 * Product Image Helper Functions
 * Shopmart E-Commerce Platform
 */

/**
 * Get product image URL with placeholder fallback
 */
function getProductImageUrl($image, $name = 'Product', $size = '300x300') {
    // If the product has a real image file
    if (!empty($image)) {
        // Check if it's already a full URL
        if (str_starts_with($image, 'http')) {
            return $image;
        }
        // Local image in assets/products/
        $localPath = dirname(__DIR__) . '/assets/products/' . $image;
        if (file_exists($localPath)) {
            return APPURL . 'assets/products/' . rawurlencode($image);
        }
    }
    
    // Fallback to placeholder
    return getProductPlaceholder($name, $size);
}

/**
 * Get placeholder image URL for products
 */
function getProductPlaceholder($name = 'Product', $size = '300x300') {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 300 300" preserveAspectRatio="xMidYMid meet">
  <rect width="100%" height="100%" fill="#F3F4F6"/>
  <g fill="#9CA3AF" text-anchor="middle" font-family="system-ui, -apple-system, sans-serif">
    <path d="M150 100 l-25 12.5 v30 l25 12.5 l25 -12.5 v-30 z m0 8 l16 8 l-16 8 l-16 -8 z m-21 15 v18 l16 8 v-18 z m26 26 l16 -8 v-18 l-16 8 z" />
    <text x="150" y="180" font-size="14" font-weight="600" fill="#6B7280">No Product Image</text>
    <text x="150" y="200" font-size="11" font-weight="600" letter-spacing="1" fill="#9CA3AF">SHOPMART</text>
  </g>
</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Get product image as an HTML <img> tag with onerror fallback
 */
function getProductImage($product, $size = '300x300', $class = '', $attributes = []) {
    $name = $product->name ?? 'Product';
    $image = $product->image ?? '';
    
    $imgUrl = getProductImageUrl($image, $name, $size);
    $fallbackUrl = getProductPlaceholder($name, $size);
    
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }
    
    $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
    $altText = htmlspecialchars($name);
    
    return '<img src="' . htmlspecialchars($imgUrl, ENT_QUOTES) . '" 
                 alt="' . $altText . '"' . 
                 $classAttr . 
                 $attrs . ' 
                 onerror="this.onerror=null; this.src=\'' . htmlspecialchars($fallbackUrl, ENT_QUOTES) . '\'"
                 loading="lazy">';
}
?>
