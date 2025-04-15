<?php

namespace App\DataFixtures;

use App\Entity\GamificationAction;
use App\Entity\Reward;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GamificationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create actions
        $actions = [
            [
                'name' => 'Recycle Plastic',
                'points' => 10,
                'description' => 'Properly dispose of plastic waste in designated recycling bins.'
            ],
            [
                'name' => 'Recycle Electronics',
                'points' => 25,
                'description' => 'Bring electronic waste to a proper e-waste collection center.'
            ],
            [
                'name' => 'Report Illegal Dumping',
                'points' => 15,
                'description' => 'Report instances of illegal waste dumping in your community.'
            ],
            [
                'name' => 'Attend Recycling Workshop',
                'points' => 30,
                'description' => 'Participate in a community workshop about proper waste disposal.'
            ],
            [
                'name' => 'Organize Cleanup Event',
                'points' => 50,
                'description' => 'Organize a community cleanup event to collect and properly dispose of waste.'
            ],
        ];

        foreach ($actions as $actionData) {
            $action = new GamificationAction();
            $action->setName($actionData['name']);
            $action->setPoints($actionData['points']);
            $action->setDescription($actionData['description']);
            
            $manager->persist($action);
        }

        // Create rewards
        $rewards = [
            [
                'name' => 'Recycling Novice',
                'type' => Reward::TYPE_BADGE,
                'pointsRequired' => 50,
                'description' => 'Earned by accumulating 50 points through recycling activities.',
                'imageUrl' => 'https://cdn-icons-png.flaticon.com/512/2583/2583344.png'
            ],
            [
                'name' => 'Recycling Enthusiast',
                'type' => Reward::TYPE_BADGE,
                'pointsRequired' => 150,
                'description' => 'Earned by accumulating 150 points through recycling activities.',
                'imageUrl' => 'https://cdn-icons-png.flaticon.com/512/2583/2583319.png'
            ],
            [
                'name' => 'Recycling Champion',
                'type' => Reward::TYPE_BADGE,
                'pointsRequired' => 300,
                'description' => 'Earned by accumulating 300 points through recycling activities.',
                'imageUrl' => 'https://cdn-icons-png.flaticon.com/512/2583/2583434.png'
            ],
            [
                'name' => 'E-Waste Hero',
                'type' => Reward::TYPE_BADGE,
                'pointsRequired' => 500,
                'description' => 'Earned by accumulating 500 points through recycling activities.',
                'imageUrl' => 'https://cdn-icons-png.flaticon.com/512/2583/2583380.png'
            ],
            [
                'name' => 'Community Guardian',
                'type' => Reward::TYPE_BADGE,
                'pointsRequired' => 1000,
                'description' => 'Earned by accumulating 1000 points through recycling activities.',
                'imageUrl' => 'https://cdn-icons-png.flaticon.com/512/2583/2583370.png'
            ],
        ];

        foreach ($rewards as $rewardData) {
            $reward = new Reward();
            $reward->setName($rewardData['name']);
            $reward->setType($rewardData['type']);
            $reward->setPointsRequired($rewardData['pointsRequired']);
            $reward->setDescription($rewardData['description']);
            $reward->setImageUrl($rewardData['imageUrl']);
            
            $manager->persist($reward);
        }

        $manager->flush();
    }
}
