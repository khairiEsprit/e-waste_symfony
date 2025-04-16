<?php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Entity\Participation;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/analytics')]
class AnalyticsController extends AbstractController
{
    private $eventRepository;
    private $participationRepository;
    private $entityManager;
    private $chartBuilder;

    public function __construct(
        EventRepository $eventRepository,
        ParticipationRepository $participationRepository,
        EntityManagerInterface $entityManager,
        ChartBuilderInterface $chartBuilder = null
    ) {
        $this->eventRepository = $eventRepository;
        $this->participationRepository = $participationRepository;
        $this->entityManager = $entityManager;
        $this->chartBuilder = $chartBuilder;
    }

    #[Route('/', name: 'back_analytics_dashboard')]
    public function dashboard(): Response
    {
        // Get basic statistics
        $stats = $this->getBasicStatistics();
        
        // Get event participation data for charts
        $eventParticipationData = $this->getEventParticipationData();
        
        // Get location data
        $locationData = $this->getLocationData();
        
        // Get monthly trends
        $monthlyTrends = $this->getMonthlyTrends();
        
        // Predictive analytics
        $predictions = $this->getPredictiveAnalytics();

        return $this->render('back/analytics/dashboard.html.twig', [
            'stats' => $stats,
            'eventParticipationData' => $eventParticipationData,
            'locationData' => $locationData,
            'monthlyTrends' => $monthlyTrends,
            'predictions' => $predictions,
        ]);
    }

    #[Route('/event/{id}', name: 'back_analytics_event_detail')]
    public function eventDetail(Event $event): Response
    {
        // Get event-specific statistics
        $eventStats = $this->getEventStatistics($event);
        
        // Get participant demographics for this event
        $demographics = $this->getEventDemographics($event);

        return $this->render('back/analytics/event_detail.html.twig', [
            'event' => $event,
            'eventStats' => $eventStats,
            'demographics' => $demographics,
        ]);
    }

    private function getBasicStatistics(): array
    {
        // Total events
        $totalEvents = $this->eventRepository->countAllEvents();
        
        // Upcoming events
        $upcomingEvents = count($this->eventRepository->findUpcomingEvents());
        
        // Past events
        $pastEvents = count($this->eventRepository->findPastEvents());
        
        // Total participations
        $totalParticipations = $this->entityManager->createQuery('SELECT COUNT(p) FROM App\Entity\Participation p')
            ->getSingleScalarResult();
        
        // Average participations per event
        $avgParticipationsPerEvent = $totalEvents > 0 ? round($totalParticipations / $totalEvents, 1) : 0;
        
        return [
            'totalEvents' => $totalEvents,
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'totalParticipations' => $totalParticipations,
            'avgParticipationsPerEvent' => $avgParticipationsPerEvent,
        ];
    }

    private function getEventParticipationData(): array
    {
        // Get events with participation counts
        $events = $this->entityManager->createQuery(
            'SELECT e.title, COUNT(p.id) as participationCount 
            FROM App\Entity\Event e 
            LEFT JOIN App\Entity\Participation p WITH p.event = e 
            GROUP BY e.id 
            ORDER BY participationCount DESC'
        )
        ->setMaxResults(10)
        ->getResult();
        
        $labels = [];
        $data = [];
        
        foreach ($events as $event) {
            $labels[] = $event['title'];
            $data[] = $event['participationCount'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getLocationData(): array
    {
        // Get participation counts by location
        $locationData = $this->entityManager->createQuery(
            'SELECT p.city, COUNT(p.id) as participantCount 
            FROM App\Entity\Participation p 
            GROUP BY p.city 
            ORDER BY participantCount DESC'
        )
        ->setMaxResults(10)
        ->getResult();
        
        $labels = [];
        $data = [];
        
        foreach ($locationData as $location) {
            $labels[] = $location['city'];
            $data[] = $location['participantCount'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getMonthlyTrends(): array
    {
        // Get participation counts by month for the last 12 months
        $currentDate = new \DateTime();
        $oneYearAgo = (new \DateTime())->modify('-1 year');
        
        // Instead of using YEAR() and MONTH() in DQL, we'll fetch the events and process them in PHP
        $events = $this->entityManager->createQuery(
            'SELECT e, COUNT(p.id) as participationCount 
            FROM App\Entity\Event e 
            LEFT JOIN App\Entity\Participation p WITH p.event = e 
            WHERE e.date BETWEEN :start AND :end 
            GROUP BY e.id 
            ORDER BY e.date ASC'
        )
        ->setParameter('start', $oneYearAgo)
        ->setParameter('end', $currentDate)
        ->getResult();
        
        $monthlyData = [];
        
        // Process events and group by year and month
        foreach ($events as $result) {
            $event = $result[0]; // The event entity
            $count = $result['participationCount'];
            
            $date = $event->getDate();
            $year = $date->format('Y');
            $month = $date->format('n');
            
            $key = $year . '-' . $month;
            
            if (!isset($monthlyData[$key])) {
                $monthlyData[$key] = [
                    'year' => $year,
                    'month' => $month,
                    'count' => 0
                ];
            }
            
            $monthlyData[$key]['count'] += $count;
        }
        
        $labels = [];
        $data = [];
        
        // Initialize all months with 0
        for ($i = 0; $i < 12; $i++) {
            $monthDate = (clone $oneYearAgo)->modify("+$i month");
            $labels[] = $monthDate->format('M Y');
            $data[] = 0;
        }
        
        // Fill in actual data
        foreach ($monthlyData as $item) {
            $date = new \DateTime("{$item['year']}-{$item['month']}-01");
            $index = (int)$date->diff($oneYearAgo)->format('%m') + ($date->diff($oneYearAgo)->format('%y') * 12);
            if ($index >= 0 && $index < 12) {
                $data[$index] = $item['count'];
            }
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getPredictiveAnalytics(): array
    {
        // Simple prediction based on historical data
        // In a real implementation, this would use more sophisticated ML models
        
        // Get average growth rate of participation over time
        $pastEvents = $this->eventRepository->findPastEvents();
        
        // Sort events by date
        usort($pastEvents, function($a, $b) {
            return $a->getDate() <=> $b->getDate();
        });
        
        // Calculate participation growth trend
        $participationTrend = [];
        $previousCount = 0;
        
        foreach ($pastEvents as $event) {
            $participationCount = $this->entityManager->createQuery(
                'SELECT COUNT(p.id) FROM App\Entity\Participation p WHERE p.event = :event'
            )
            ->setParameter('event', $event)
            ->getSingleScalarResult();
            
            if ($previousCount > 0) {
                $growthRate = ($participationCount - $previousCount) / $previousCount;
                $participationTrend[] = $growthRate;
            }
            
            $previousCount = $participationCount;
        }
        
        // Calculate average growth rate
        $avgGrowthRate = count($participationTrend) > 0 ? array_sum($participationTrend) / count($participationTrend) : 0;
        
        // Predict next event participation
        $latestParticipationCount = $previousCount;
        $predictedNextParticipation = round($latestParticipationCount * (1 + $avgGrowthRate));
        
        // Get most popular locations
        $popularLocations = $this->entityManager->createQuery(
            'SELECT p.city, COUNT(p.id) as participantCount 
            FROM App\Entity\Participation p 
            GROUP BY p.city 
            ORDER BY participantCount DESC'
        )
        ->setMaxResults(3)
        ->getResult();
        
        return [
            'predictedParticipation' => $predictedNextParticipation,
            'growthRate' => round($avgGrowthRate * 100, 1),
            'recommendedLocations' => array_column($popularLocations, 'city'),
        ];
    }

    private function getEventStatistics(Event $event): array
    {
        // Get participation count for this event
        $participationCount = $this->entityManager->createQuery(
            'SELECT COUNT(p.id) FROM App\Entity\Participation p WHERE p.event = :event'
        )
        ->setParameter('event', $event)
        ->getSingleScalarResult();
        
        // Calculate fill rate
        $capacity = $participationCount + $event->getRemainingPlaces();
        $fillRate = $capacity > 0 ? round(($participationCount / $capacity) * 100, 1) : 0;
        
        return [
            'participationCount' => $participationCount,
            'capacity' => $capacity,
            'fillRate' => $fillRate,
            'remainingPlaces' => $event->getRemainingPlaces(),
        ];
    }

    private function getEventDemographics(Event $event): array
    {
        // Get city distribution
        $cityDistribution = $this->entityManager->createQuery(
            'SELECT p.city, COUNT(p.id) as participantCount 
            FROM App\Entity\Participation p 
            WHERE p.event = :event 
            GROUP BY p.city 
            ORDER BY participantCount DESC'
        )
        ->setParameter('event', $event)
        ->setMaxResults(5)
        ->getResult();
        
        // Get country distribution
        $countryDistribution = $this->entityManager->createQuery(
            'SELECT p.country, COUNT(p.id) as participantCount 
            FROM App\Entity\Participation p 
            WHERE p.event = :event 
            GROUP BY p.country 
            ORDER BY participantCount DESC'
        )
        ->setParameter('event', $event)
        ->setMaxResults(5)
        ->getResult();
        
        return [
            'cityDistribution' => $cityDistribution,
            'countryDistribution' => $countryDistribution,
        ];
    }
}
