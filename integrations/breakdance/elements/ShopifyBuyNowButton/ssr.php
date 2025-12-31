<?php
/**
 * @var array $propertiesData
 */
$text = $propertiesData['content']['text'] ?? 'Buy Now';

echo "<a href='#' class='bde-shoplink-buy-now-btn'>" . esc_html($text) . "</a>";
