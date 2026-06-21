<?php
namespace App\Services;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;

class GoogleAnalyticsService
{
    private BetaAnalyticsDataClient $client;
    private string $propertyId;

    public function __construct()
    {
        $this->propertyId = config('services.google_analytics.property_id');

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => config('services.google_analytics.credentials_path'),
        ]);
    }

    /** مصادر الزيارات آخر 30 يوم */
    public function getTrafficSources(): array
    {
        $request = new RunReportRequest([
            'property'   => $this->propertyId,
            'date_ranges'=> [new DateRange(['start_date' => '30daysAgo', 'end_date' => 'today'])],
            'dimensions' => [new Dimension(['name' => 'sessionDefaultChannelGroup'])],
            'metrics'    => [new Metric(['name' => 'sessions'])],
            'order_bys'  => [new OrderBy([
                'metric' => new MetricOrderBy(['metric_name' => 'sessions']),
                'desc'   => true,
            ])],
            'limit' => 6,
        ]);

        $response = $this->client->runReport($request);
        $total    = 0;
        $rows     = [];

        foreach ($response->getRows() as $row) {
            $count  = (int) $row->getMetricValues()[0]->getValue();
            $total += $count;
            $rows[] = ['channel' => $row->getDimensionValues()[0]->getValue(), 'count' => $count];
        }

        // تعريب أسماء القنوات وإضافة الأيقونة واللون
        $map = [
            'Organic Search' => ['label' => 'بحث جوجل',      'icon' => 'bi-search',   'color' => '#162FBB'],
            'Direct'         => ['label' => 'زيارة مباشرة',   'icon' => 'bi-link',     'color' => '#E7AE18'],
            'Organic Social' => ['label' => 'تواصل اجتماعي',  'icon' => 'bi-facebook', 'color' => '#0e7490'],
            'Referral'       => ['label' => 'مواقع خارجية',   'icon' => 'bi-globe',    'color' => '#7c3aed'],
            'Email'          => ['label' => 'بريد إلكتروني',  'icon' => 'bi-envelope', 'color' => '#059669'],
            'Paid Search'    => ['label' => 'إعلانات مدفوعة', 'icon' => 'bi-megaphone','color' => '#d97706'],
        ];

        return collect($rows)->map(function ($row) use ($total, $map) {
            $info = $map[$row['channel']] ?? ['label' => $row['channel'], 'icon' => 'bi-bar-chart', 'color' => '#94a3b8'];
            return [
                'label' => $info['label'],
                'icon'  => $info['icon'],
                'color' => $info['color'],
                'pct'   => $total > 0 ? round(($row['count'] / $total) * 100) : 0,
                'count' => $row['count'],
            ];
        })->toArray();
    }

    /** مؤشرات صحة الموقع — Core Web Vitals من GA4 */
    public function getSiteHealth(): array
    {
        $request = new RunReportRequest([
            'property'    => $this->propertyId,
            'date_ranges' => [new DateRange(['start_date' => '28daysAgo', 'end_date' => 'today'])],
            'metrics'     => [
                new Metric(['name' => 'bounceRate']),
                new Metric(['name' => 'averageSessionDuration']),
                new Metric(['name' => 'engagementRate']),
                new Metric(['name' => 'screenPageViewsPerSession']),
            ],
        ]);

        $response = $this->client->runReport($request);
        $vals     = [];

        if ($response->getRows()->count() > 0) {
            $mv   = $response->getRows()[0]->getMetricValues();
            $vals = [
                'bounce_rate'       => round((float) $mv[0]->getValue() * 100),
                'avg_session_sec'   => (int) $mv[1]->getValue(),
                'engagement_rate'   => round((float) $mv[2]->getValue() * 100),
                'pages_per_session' => round((float) $mv[3]->getValue(), 1),
            ];
        }

        $avgMin = isset($vals['avg_session_sec'])
            ? floor($vals['avg_session_sec'] / 60) . ':' . str_pad($vals['avg_session_sec'] % 60, 2, '0', STR_PAD_LEFT)
            : '0:00';

        $engagement = $vals['engagement_rate'] ?? 0;
        $bounce     = $vals['bounce_rate']     ?? 0;
        $pages      = $vals['pages_per_session'] ?? 0;

        // تحويل إلى نقاط 0-100 لعرض صحة الموقع
        $engagementScore = min(100, $engagement);
        $bounceScore     = max(0, 100 - $bounce);
        $pagesScore      = min(100, (int) ($pages * 20));

        $score = (int) round(($engagementScore + $bounceScore + $pagesScore) / 3);

        return [
            'engagement_rate'   => $engagement . '%',
            'avg_session'       => $avgMin,
            'pages_per_session' => $pages,
            'bounce_rate'       => $bounce . '%',
            'overall_score'     => $score,
        ];
    }

    /** الزيارات اليومية آخر 7 أيام */
    public function getWeeklyVisits(): array
    {
        $request = new RunReportRequest([
            'property'    => $this->propertyId,
            'date_ranges' => [new DateRange(['start_date' => '6daysAgo', 'end_date' => 'today'])],
            'dimensions'  => [new Dimension(['name' => 'date'])],
            'metrics'     => [new Metric(['name' => 'activeUsers'])],
            'order_bys'   => [new OrderBy([
                'dimension' => new OrderBy\DimensionOrderBy(['dimension_name' => 'date']),
                'desc'      => false,
            ])],
        ]);

        $response = $this->client->runReport($request);
        $days     = [];

        foreach ($response->getRows() as $row) {
            $dateStr = $row->getDimensionValues()[0]->getValue(); // YYYYMMDD
            $count   = (int) $row->getMetricValues()[0]->getValue();
            $carbon  = \Carbon\Carbon::createFromFormat('Ymd', $dateStr);
            $days[]  = ['date' => $carbon, 'count' => $count];
        }

        $max = collect($days)->max('count') ?: 1;

        $dayNames = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
        $shortNames = ['أحد','اثن','ثلا','أرب','خمي','جمع','سبت'];

        return collect($days)->map(function ($d) use ($max, $dayNames, $shortNames) {
            $dow = (int) $d['date']->dayOfWeek;
            return [
                'short' => $d['date']->isToday() ? 'اليوم' : $shortNames[$dow],
                'label' => $d['date']->isToday() ? 'اليوم'  : $dayNames[$dow],
                'count' => $d['count'],
                'pct'   => $max > 0 ? max(5, round(($d['count'] / $max) * 90)) : 5,
            ];
        })->toArray();
    }
}
