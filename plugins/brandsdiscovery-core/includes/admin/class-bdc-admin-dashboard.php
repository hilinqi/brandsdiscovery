<?php
/**
 * Admin Dashboard page.
 *
 * @package BrandsDiscovery_Core
 */

class BDC_Admin_Dashboard {

    public static function render() {
        $brands       = new BDC_Brands();
        $submissions  = new BDC_Submissions();
        $claims       = new BDC_Claims();
        $visit_log    = new BDC_Visit_Log();

        $published_count  = $brands->count(array('publication_status' => 'published'));
        $pending_brands   = $brands->count(array('publication_status' => 'pending'));
        $pending_submissions = $submissions->count(array('status' => 'new'));
        $pending_claims   = $claims->count(array('status' => 'pending'));
        $recent_visits    = $visit_log->get_recent(5);
        ?>
        <div class="wrap bdc-admin-wrap">
            <h1>Dashboard / 工作台</h1>

            <div class="bdc-dashboard-grid">
                <div class="bdc-stat-card">
                    <h3><?php echo esc_html($published_count); ?></h3>
                    <p>Published Brands / 已发布品牌</p>
                </div>
                <div class="bdc-stat-card">
                    <h3><?php echo esc_html($pending_brands); ?></h3>
                    <p>Pending Reviews / 待审核品牌</p>
                </div>
                <div class="bdc-stat-card">
                    <h3><?php echo esc_html($pending_claims); ?></h3>
                    <p>Pending Claims / 待审核认领</p>
                </div>
                <div class="bdc-stat-card">
                    <h3><?php echo esc_html($pending_submissions); ?></h3>
                    <p>Pending Submissions / 待审核提交</p>
                </div>
            </div>

            <div class="bdc-dashboard-section">
                <h2>Recent Visit Store Clicks / 最近点击</h2>
                <?php if (!empty($recent_visits)) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Brand / 品牌</th>
                                <th>Time / 时间</th>
                                <th>Referrer / 来源</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_visits as $visit) : ?>
                                <tr>
                                    <td><?php echo esc_html($visit->brand_name); ?></td>
                                    <td><?php echo esc_html($visit->created_at); ?></td>
                                    <td><?php echo esc_html($visit->referrer_url ?: 'Direct / 直接访问'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No recent visits. / 暂无点击记录。</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
