<?php
/**
 * Admin Products management page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Products {

    public static function render() {
        $products = new BDC_Products();
        $results = $products->query(array(
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
        ));
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Products / 产品</h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / 产品名称</th>
                        <th>Brand / 品牌</th>
                        <th>Price / 价格</th>
                        <th>Status / 状态</th>
                        <th>Date / 日期</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)) : ?>
                        <tr><td colspan="6">No products found. / 未找到产品。</td></tr>
                    <?php else : ?>
                        <?php foreach ($results as $product) : ?>
                            <tr>
                                <td><?php echo esc_html($product->id); ?></td>
                                <td><?php echo esc_html($product->name); ?></td>
                                <td><?php echo esc_html($product->brand_id); ?></td>
                                <td><?php echo esc_html($product->price); ?></td>
                                <td><?php echo esc_html($product->status); ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($product->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
