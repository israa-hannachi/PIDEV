<?php

namespace App\Controller;

use App\Entity\Meet;
use App\Repository\MeetRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(Request $request, MeetRepository $meetRepository, ParticipantRepository $participantRepository): Response
    {
        $now = new \DateTimeImmutable();

        $period = trim((string) $request->query->get('period', '30d'));
        $teacherIdRaw = $request->query->get('teacherId');
        $teacherId = (is_string($teacherIdRaw) || is_int($teacherIdRaw)) ? (int) $teacherIdRaw : null;
        if ($teacherId === 0) {
            $teacherId = null;
        }

        $fromRaw = trim((string) $request->query->get('from', ''));
        $toRaw = trim((string) $request->query->get('to', ''));

        $to = null;
        $from = null;

        if ($period === 'custom' && $fromRaw !== '' && $toRaw !== '') {
            try {
                $from = (new \DateTimeImmutable($fromRaw))->setTime(0, 0, 0);
            } catch (\Throwable $e) {
                $from = null;
            }
            try {
                $to = (new \DateTimeImmutable($toRaw))->setTime(23, 59, 59);
            } catch (\Throwable $e) {
                $to = null;
            }
        }

        if ($from === null || $to === null) {
            $to = $now->setTime(23, 59, 59);
            $days = 30;
            if ($period === '7d') {
                $days = 7;
            } elseif ($period === '30d') {
                $days = 30;
            } elseif ($period === '90d') {
                $days = 90;
            }
            $from = $now->modify('-' . ($days - 1) . ' days')->setTime(0, 0, 0);
        }

        $periodDays = (int) max(1, (int) (($to->getTimestamp() - $from->getTimestamp()) / 86400) + 1);
        $prevTo = $from->modify('-1 second');
        $prevFrom = $from->modify('-' . $periodDays . ' days');

        $meets = $meetRepository->findAll();

        $teachers = $participantRepository->findBy(['role' => 'enseignant'], ['nom' => 'ASC']);

        $current = $this->computeAnalytics($meets, $now, $from, $to, $teacherId);
        $previous = $this->computeAnalytics($meets, $now, $prevFrom, $prevTo, $teacherId);

        $trend = function (float|int $cur, float|int $prev): ?float {
            $prev = (float) $prev;
            $cur = (float) $cur;
            if ($prev <= 0) {
                return null;
            }
            return (($cur - $prev) / $prev) * 100.0;
        };

        $trends = [
            'meets' => $trend($current['kpi']['meets_total'], $previous['kpi']['meets_total']),
            'participants' => $trend($current['kpi']['participants_total'], $previous['kpi']['participants_total']),
            'duration' => $trend($current['kpi']['duration_total_minutes'], $previous['kpi']['duration_total_minutes']),
        ];

        return $this->render('dashboard/index_admin.html.twig', [
            'filters' => [
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'teacherId' => $teacherId,
            ],
            'teachers' => $teachers,
            'kpi' => $current['kpi'],
            'trends' => $trends,
            'nextMeet' => $current['nextMeet'],
            'topTeachers' => $current['topTeachers'],
            'charts' => $current['charts'],
        ]);
    }

    #[Route('/dashboard/export.csv', name: 'app_admin_dashboard_export', methods: ['GET'])]
    public function exportCsv(Request $request, MeetRepository $meetRepository, ParticipantRepository $participantRepository): Response
    {
        $now = new \DateTimeImmutable();
        $period = trim((string) $request->query->get('period', '30d'));
        $teacherIdRaw = $request->query->get('teacherId');
        $teacherId = (is_string($teacherIdRaw) || is_int($teacherIdRaw)) ? (int) $teacherIdRaw : null;
        if ($teacherId === 0) {
            $teacherId = null;
        }

        $fromRaw = trim((string) $request->query->get('from', ''));
        $toRaw = trim((string) $request->query->get('to', ''));

        $to = null;
        $from = null;
        if ($period === 'custom' && $fromRaw !== '' && $toRaw !== '') {
            try {
                $from = (new \DateTimeImmutable($fromRaw))->setTime(0, 0, 0);
            } catch (\Throwable $e) {
                $from = null;
            }
            try {
                $to = (new \DateTimeImmutable($toRaw))->setTime(23, 59, 59);
            } catch (\Throwable $e) {
                $to = null;
            }
        }
        if ($from === null || $to === null) {
            $to = $now->setTime(23, 59, 59);
            $days = 30;
            if ($period === '7d') {
                $days = 7;
            } elseif ($period === '30d') {
                $days = 30;
            } elseif ($period === '90d') {
                $days = 90;
            }
            $from = $now->modify('-' . ($days - 1) . ' days')->setTime(0, 0, 0);
        }

        $meets = $meetRepository->findAll();
        $analytics = $this->computeAnalytics($meets, $now, $from, $to, $teacherId);

        $lines = [];
        $lines[] = ['metric', 'value'];
        foreach ($analytics['kpi'] as $k => $v) {
            $lines[] = [$k, (string) $v];
        }

        $csv = '';
        foreach ($lines as $line) {
            $csv .= '"' . implode('";"', array_map(static fn ($x) => str_replace('"', '""', (string) $x), $line)) . '"' . "\n";
        }

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dashboard_stats.csv"',
        ]);
    }

    /**
     * @param array<int, mixed> $meets
     * @return array{ kpi: array<string, mixed>, nextMeet: ?Meet, topTeachers: array<string,int>, charts: array<string, mixed> }
     */
    private function computeAnalytics(array $meets, \DateTimeImmutable $now, \DateTimeImmutable $from, \DateTimeImmutable $to, ?int $teacherId): array
    {
        $filtered = [];
        foreach ($meets as $meet) {
            if (!$meet instanceof Meet) {
                continue;
            }
            $start = $meet->getDateDebut();
            if ($start === null) {
                continue;
            }
            if ($start < $from || $start > $to) {
                continue;
            }
            if ($teacherId !== null) {
                $teacher = $meet->getParticipant();
                if (!$teacher || $teacher->getId() !== $teacherId) {
                    continue;
                }
            }
            $filtered[] = $meet;
        }

        $meetsTotal = 0;
        $upcoming = 0;
        $current = 0;
        $completed = 0;

        $participantsTotal = 0;
        $durationTotalMinutes = 0;
        $durationCount = 0;

        $nextMeet = null;

        $byTeacher = [];
        $daily = [];
        $hourly = array_fill(0, 24, 0);

        $cursor = $from;
        while ($cursor <= $to) {
            $daily[$cursor->format('Y-m-d')] = 0;
            $cursor = $cursor->modify('+1 day');
        }

        foreach ($filtered as $meet) {
            $meetsTotal++;

            $start = $meet->getDateDebut();
            $end = $meet->getDateFin();

            if ($start !== null && $end !== null) {
                if ($now < $start) {
                    $upcoming++;
                    if ($nextMeet === null || ($nextMeet->getDateDebut() && $start < $nextMeet->getDateDebut())) {
                        $nextMeet = $meet;
                    }
                } elseif ($now >= $start && $now <= $end) {
                    $current++;
                } else {
                    $completed++;
                }

                $mins = (int) round(max(0, ($end->getTimestamp() - $start->getTimestamp()) / 60));
                $durationTotalMinutes += $mins;
                $durationCount++;
            }

            $participantsTotal += $meet->getParticipants()->count();

            if ($start !== null) {
                $dayKey = $start->format('Y-m-d');
                if (array_key_exists($dayKey, $daily)) {
                    $daily[$dayKey]++;
                }
                $hour = (int) $start->format('G');
                if ($hour >= 0 && $hour <= 23) {
                    $hourly[$hour] = (int) $hourly[$hour] + 1;
                }
            }

            $teacher = $meet->getParticipant();
            $teacherName = $teacher ? trim((string) ($teacher->getNom() . ' ' . $teacher->getPrenom())) : 'Inconnu';
            if ($teacherName === '') {
                $teacherName = 'Inconnu';
            }
            $byTeacher[$teacherName] = ($byTeacher[$teacherName] ?? 0) + 1;
        }

        arsort($byTeacher);
        $topTeachers = array_slice($byTeacher, 0, 5, true);

        $avgParticipants = $meetsTotal > 0 ? round($participantsTotal / $meetsTotal, 2) : 0;
        $avgDuration = $durationCount > 0 ? round($durationTotalMinutes / $durationCount, 2) : 0;

        $charts = [
            'daily' => [
                'labels' => array_keys($daily),
                'series' => array_values($daily),
            ],
            'status' => [
                'labels' => ['À venir', 'En cours', 'Terminées'],
                'series' => [$upcoming, $current, $completed],
            ],
            'hourly' => [
                'labels' => array_map(static fn (int $h) => str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23)),
                'series' => array_values($hourly),
            ],
            'drilldown' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ];

        $kpi = [
            'meets_total' => $meetsTotal,
            'meets_upcoming' => $upcoming,
            'meets_current' => $current,
            'meets_completed' => $completed,
            'participants_total' => $participantsTotal,
            'participants_avg' => $avgParticipants,
            'duration_total_minutes' => $durationTotalMinutes,
            'duration_avg_minutes' => $avgDuration,
        ];

        return [
            'kpi' => $kpi,
            'nextMeet' => $nextMeet,
            'topTeachers' => $topTeachers,
            'charts' => $charts,
        ];
    }
}
