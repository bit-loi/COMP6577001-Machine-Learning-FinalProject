<?php
/**
 * Book Cover Helper Functions
 * Using Open Library Covers API (FREE, No API Key)
 * https://openlibrary.org/dev/docs/api/covers
 */

/**
 * Get book cover URL from Open Library API
 * 
 * @param string $isbn - Book ISBN (with or without hyphens)
 * @param string $size - S (small), M (medium), or L (large)
 * @return string - Cover image URL
 */
function getBookCover($isbn, $size = 'M') {
    // Remove hyphens and spaces from ISBN
    $isbn = str_replace(['-', ' '], '', $isbn);
    
    // Validate size
    $size = strtoupper($size);
    if (!in_array($size, ['S', 'M', 'L'])) {
        $size = 'M';
    }
    
    // Open Library Covers API with ?default=false to force 404 if missing
    return "https://covers.openlibrary.org/b/isbn/{$isbn}-{$size}.jpg?default=false";
}

/**
 * Get book cover smartly with basic empty check
 * 
 * @param string $isbn - Book ISBN
 * @param string $title - Book title (for placeholder text)
 * @param string $size - S, M, or L (default: M)
 * @return string - Cover image URL
 */
function getBookCoverSmart($isbn, $title, $size = 'M') {
    if (!empty($isbn)) {
        $isbn = str_replace(['-', ' '], '', $isbn);
        $openLibrary = "https://covers.openlibrary.org/b/isbn/{$isbn}-{$size}.jpg?default=false";
        return $openLibrary;
    }
    
    return getPlaceholderCover($title, $size);
}

/**
 * Get placeholder cover image
 * 
 * @param string $text - Text to display on placeholder
 * @param string $size - S, M, or L
 * @return string - Placeholder image URL
 */
function getPlaceholderCover($text = 'No Cover', $size = 'M') {
    // Determine dimensions based on size
    $dimensions = [
        'S' => '150x225',
        'M' => '300x450',
        'L' => '400x600'
    ];
    
    $dim = $dimensions[strtoupper($size)] ?? '300x450';
    $encodedText = urlencode($text);
    
    // Dark ocean theme colors
    $bgColor = '0D1B2A';  // Dark ocean blue
    $textColor = '00D9FF'; // Cyan
    
    return "https://placehold.co/{$dim}/{$bgColor}/{$textColor}?text={$encodedText}";
}

/**
 * Get book cover HTML img tag with fallback
 * 
 * @param string $isbn - Book ISBN
 * @param string $title - Book title
 * @param string $size - S, M, or L
 * @param string $class - CSS class for img tag
 * @param array $attributes - Additional HTML attributes
 * @return string - Complete HTML img tag
 */
function getBookCoverImage($isbn, $title, $size = 'M', $class = '', $attributes = []) {
    $coverUrl = getBookCoverSmart($isbn, $title, $size);
    $fallbackUrl = getPlaceholderCover($title, $size);
    
    $attrs = '';
    foreach ($attributes as $key => $value) {
        $attrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }
    
    $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
    $altText = htmlspecialchars($title);
    
    return '<img src="' . htmlspecialchars($coverUrl, ENT_QUOTES) . '" 
                 alt="' . $altText . '"' . 
                 $classAttr . 
                 $attrs . ' 
                 onerror="this.onerror=null; this.src=\'' . htmlspecialchars($fallbackUrl, ENT_QUOTES) . '\'"
                 loading="lazy">';
}

/**
 * Check if book cover exists (optional - for advanced caching)
 * Note: This makes an HTTP request, use sparingly
 * 
 * @param string $isbn - Book ISBN
 * @return bool - True if cover exists
 */
function bookCoverExists($isbn) {
    $isbn = str_replace(['-', ' '], '', $isbn);
    $url = "https://covers.openlibrary.org/b/isbn/{$isbn}-S.jpg?default=false";
    
    $headers = @get_headers($url);
    return $headers && strpos($headers[0], '200') !== false;
}

/**
 * Get multiple book covers (batch)
 * 
 * @param array $products - Array of product objects with isbn property
 * @param string $size - S, M, or L
 * @return array - Array of cover URLs indexed by product id
 */
function getBatchBookCovers($products, $size = 'M') {
    $covers = [];
    
    foreach ($products as $product) {
        $covers[$product->id] = getBookCoverSmart(
            $product->isbn ?? '', 
            $product->name ?? 'No Cover',
            $size
        );
    }
    
    return $covers;
}

/**
 * Get book cover from Google Books API (alternative)
 * Requires internet connection and has rate limits
 * 
 * @param string $isbn - Book ISBN
 * @return string - Cover URL or placeholder
 */
function getGoogleBookCover($isbn) {
    $isbn = str_replace(['-', ' '], '', $isbn);
    $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:{$isbn}";
    
    try {
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            
            if (isset($data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
                // Replace http with https and get larger image
                $thumbnail = $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
                return str_replace('http://', 'https://', $thumbnail);
            }
        }
    } catch (Exception $e) {
        // Fallback to Open Library
    }
    
    return getBookCover($isbn, 'M');
}
?>
