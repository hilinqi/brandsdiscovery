<?php
/**
 * Admin Submissions management page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Submissions {

    public static function render() {
        $submissions = new BDC_Submissions();
        $status_filter = isset($_GET['bdc_status']) ? sanitize_text_field($_GET['bdc_status']) : '';
        $results = $submissions->get_by_status($status_filter, 30);
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Submissions / 提交审核</h1>
            <p>Review submitted brands, requests, and reports. / 审核用户提交的品牌、需求和报告。</p>

            <div class="bdc-filters" style="margin-bottom:16px;">
                <a href="?page=brandsdiscovery-submissions" class="button <?php echo empty($status_filter) ? 'button-primary' : ''; ?>">All / 全部</a>
                <a href="?page=brandsdiscovery-submissions&bdc_status=new" class="button">New / 新建</a>
                <a href="?page=brandsdiscovery-submissions&bdc_status=reviewing" class="button">Reviewing / 审核中</a>
                <a href="?page=brandsdiscovery-submissions&bdc_status=approved" class="button">Approved / 已通过</a>
                <a href="?page=brandsdiscovery-submissions&bdc_status=rejected" class="button">Rejected / 已拒绝</a>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type / 类型</th>
                        <th>Details / 详情</th>
                        <th>Email / 邮箱</th>
                        <th>Status / 状态</th>
                        <th>Date / 日期</th>
                        <th>Actions / 操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)) : ?>
                        <tr><td colspan="7">No submissions found. / 未找到提交。</td></tr>
                    <?php else : ?>
                        <?php foreach ($results as $sub) : ?>
                            <?php
                            $data = json_decode($sub->data, true);
                            $type_label = str_replace('_', ' ', $sub->type);
                            $type_label = ucwords($type_label);
                            ?>
                            <tr>
                                <td>#<?php echo esc_html($sub->id); ?></td>
                                <td><?php echo esc_html($type_label); ?></td>
                                <td><?php echo esc_html($data['name'] ?? $data['description'] ?? '—'); ?></td>
                                <td><?php echo esc_html($sub->submitter_email); ?></td>
                                <td>
                                    <span class="bdc-status bdc-status-<?php echo esc_attr($sub->status); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $sub->status))); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($sub->created_at))); ?></td>
                                <td>
                                    <?php if ($sub->status === 'new' || $sub->status === 'reviewing') : ?>
                                        <button class="button button-small bdc-review-submission" data-id="<?php echo esc_attr($sub->id); ?>">Review / 审核</button>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
