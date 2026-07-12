<?php
/**
 * Admin Claims management page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Claims {

    public static function render() {
        $claims = new BDC_Claims();
        $results = $claims->query(array(
            'orderby' => 'created_at',
            'order'   => 'DESC',
            'limit'   => 50,
        ));
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Claim Requests / 品牌认领申请</h1>
            <p>Review and approve brand ownership claims. / 审核品牌所有权认领。</p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand ID / 品牌ID</th>
                        <th>Company / 公司</th>
                        <th>Contact / 联系人</th>
                        <th>Email / 邮箱</th>
                        <th>Status / 状态</th>
                        <th>Date / 日期</th>
                        <th>Actions / 操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)) : ?>
                        <tr><td colspan="8">No claims found. / 未找到认领申请。</td></tr>
                    <?php else : ?>
                        <?php foreach ($results as $claim) : ?>
                            <tr>
                                <td>#<?php echo esc_html($claim->id); ?></td>
                                <td><?php echo esc_html($claim->brand_id); ?></td>
                                <td><?php echo esc_html($claim->company_name); ?></td>
                                <td><?php echo esc_html($claim->contact_name); ?></td>
                                <td><?php echo esc_html($claim->contact_email); ?></td>
                                <td>
                                    <span class="bdc-status bdc-status-<?php echo esc_attr($claim->status); ?>">
                                        <?php echo esc_html(ucfirst($claim->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($claim->created_at))); ?></td>
                                <td>
                                    <?php if ($claim->status === 'pending') : ?>
                                        <button class="button button-small bdc-approve-claim" data-id="<?php echo esc_attr($claim->id); ?>">
                                            Approve / 通过
                                        </button>
                                        <button class="button button-small bdc-reject-claim" data-id="<?php echo esc_attr($claim->id); ?>">
                                            Reject / 拒绝
                                        </button>
                                    <?php elseif ($claim->status === 'approved') : ?>
                                        <button class="button button-small bdc-revoke-claim" data-id="<?php echo esc_attr($claim->id); ?>">
                                            Revoke / 撤销
                                        </button>
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
