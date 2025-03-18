<?php
/**
 * Template for displaying the broker table
 *
 * This template can be overridden by copying it to yourtheme/top10-brokers/table-template.php
 *
 * @package Top_10_Brokers
 * @version 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the items from the query vars
$items = get_query_var('top10_brokers_items', array());
?>

<div class="top10-brokers-container">
    <table class="top10-brokers-table">
        <thead>
            <tr>
                <th class="top10-brokers-rank-header"><?php _e('Rank', 'top10-brokers'); ?></th>
                <th class="top10-brokers-broker-header"><?php _e('Broker', 'top10-brokers'); ?></th>
                <th class="top10-brokers-rating-header"><?php _e('Rating', 'top10-brokers'); ?></th>
                <th class="top10-brokers-review-header"><?php _e('Review', 'top10-brokers'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="top10-brokers-rank"><?php echo esc_html($item['rank']); ?></td>
                        <td class="top10-brokers-broker">
                            <?php if (!empty($item['image'])): ?>
                                <div class="broker-info">
                                    <a href="<?php echo esc_url($item['link']); ?>">
                                        <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>" class="top10-brokers-image" />
                                    </a>
                                    <div class="star-rating" style="--rating: <?php echo (esc_html($item['rating']) / 5 * 100); ?>%">★★★★★</div>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo esc_url($item['link']); ?>"><?php echo esc_html($item['name']); ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="top10-brokers-rating">
                            <?php echo esc_html($item['rating']); ?>
                        </td>
                        <td class="top10-brokers-review">
                            <a href="<?php echo esc_url($item['link']); ?>" class="learn-more-button">Learn More</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4"><?php _e('No items found in this category.', 'top10-brokers'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div> 