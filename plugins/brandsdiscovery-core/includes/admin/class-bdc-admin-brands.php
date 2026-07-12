<?php
/**
 * Admin Brands management page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Brands {

    public static function render() {
        $brands = new BDC_Brands();
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $where = array();
        if (!empty($status_filter)) {
            $where['publication_status'] = $status_filter;
        }
        $results = $brands->query(array(
            'where'   => $where,
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
        ));
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Brands / 品牌</h1>

            <div class="bdc-filters" style="margin-bottom:16px;">
                <a href="?page=brandsdiscovery-brands" class="button <?php echo empty($status_filter) ? 'button-primary' : ''; ?>">All / 全部</a>
                <a href="?page=brandsdiscovery-brands&status=published" class="button">Published / 已发布</a>
                <a href="?page=brandsdiscovery-brands&status=pending" class="button">Pending Review / 待审核</a>
                <a href="?page=brandsdiscovery-brands&status=draft" class="button">Draft / 草稿</a>
                <a href="?page=brandsdiscovery-brands&status=paused" class="button">Paused / 已暂停</a>
                <a href="?page=brandsdiscovery-brands&status=rejected" class="button">Rejected / 已拒绝</a>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / 品牌名称</th>
                        <th>Slug</th>
                        <th>Category / 分类</th>
                        <th>Country / 国家</th>
                        <th>Status / 状态</th>
                        <th>Claim / 认领</th>
                        <th>Verified / 验证</th>
                        <th>Date / 日期</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)) : ?>
                        <tr>
                            <td colspan="9">No brands found. / 未找到品牌。</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($results as $brand) : ?>
                            <tr>
                                <td><?php echo esc_html($brand->id); ?></td>
                                <td>
                                    <strong><?php echo esc_html($brand->name); ?></strong>
                                    <div class="row-actions">
                                        <span><a href="#">Edit / 编辑</a> | </span>
                                        <span><a href="#">View / 查看</a> | </span>
                                        <?php if ($brand->publication_status === 'pending') : ?>
                                            <span><a href="#" class="bdc-approve" data-id="<?php echo esc_attr($brand->id); ?>">Approve / 通过</a> | </span>
                                            <span><a href="#" class="bdc-reject" data-id="<?php echo esc_attr($brand->id); ?>">Reject / 拒绝</a></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo esc_html($brand->slug); ?></td>
                                <td>—</td>
                                <td><?php echo esc_html($brand->origin_country); ?></td>
                                <td>
                                    <span class="bdc-status bdc-status-<?php echo esc_attr($brand->publication_status); ?>">
                                        <?php echo esc_html(ucfirst($brand->publication_status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(ucfirst($brand->claim_status)); ?></td>
                                <td><?php echo $brand->is_verified ? '✓' : '—'; ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($brand->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
