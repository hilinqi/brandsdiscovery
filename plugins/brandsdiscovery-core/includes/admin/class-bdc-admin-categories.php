<?php
/**
 * Admin Categories management page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Categories {

    public static function render() {
        $categories = new BDC_Categories();
        $tree = $categories->get_tree();
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Categories / 分类</h1>
            <p>Manage category hierarchy and attributes. / 管理分类层级和属性。</p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / 名称</th>
                        <th>Slug</th>
                        <th>Parent / 父级</th>
                        <th>Order / 排序</th>
                        <th>Status / 状态</th>
                    </tr>
                </thead>
                <tbody>
                    <?php self::render_tree_rows($tree, 0); ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private static function render_tree_rows($categories, $depth) {
        if (empty($categories)) {
            echo '<tr><td colspan="6">No categories found. / 未找到分类。</td></tr>';
            return;
        }

        foreach ($categories as $cat) {
            $indent = str_repeat('— ', $depth);
            ?>
            <tr>
                <td><?php echo esc_html($cat->id); ?></td>
                <td><?php echo esc_html($indent . $cat->name); ?></td>
                <td><?php echo esc_html($cat->slug); ?></td>
                <td><?php echo $cat->parent_id ? esc_html($cat->parent_id) : '—'; ?></td>
                <td><?php echo esc_html($cat->display_order); ?></td>
                <td><?php echo esc_html($cat->status); ?></td>
            </tr>
            <?php
            if (isset($cat->children)) {
                self::render_tree_rows($cat->children, $depth + 1);
            }
        }
    }
}
