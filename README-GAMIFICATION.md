# E-Waste Gamification System

This module adds an AI-powered gamification and user engagement system to the E-Waste platform. The feature rewards users for waste disposal actions with points and badges, using OpenRouter AI to predict engagement and personalize rewards.

## Features

- **Action-based Rewards**: Users earn points for completing waste disposal actions
- **Badges and Achievements**: Users earn badges based on their point totals
- **Leaderboard**: Public and user-specific leaderboard rankings
- **AI Engagement Prediction**: Uses OpenRouter AI to predict user engagement
- **Admin Dashboard**: Complete management of actions, rewards, and user engagement
- **REST API**: API endpoints for integration with mobile apps or other services

## Setup Instructions

### 1. Install Dependencies

The system uses existing dependencies in the project. No additional packages are required.

### 2. Run Database Migrations

```bash
php bin/console doctrine:migrations:migrate
```

This will create the necessary database tables for the gamification system.

### 3. Configure OpenRouter API

Make sure your `.env` file contains the OpenRouter API key:

```
OPENROUTER_API_KEY=your-key-here
```

### 4. Schedule the Engagement Prediction Command

Add the following to your crontab to run the engagement prediction nightly:

```
0 0 * * * php /path/to/your/project/bin/console app:predict-engagement
```

Or run it manually:

```bash
php bin/console app:predict-engagement
```

## Usage

### Admin Interface

- **Dashboard**: `/admin/gamification` - Overview of actions, rewards, leaderboard, and AI predictions
- **Actions**: Create, edit, and delete gamification actions
- **Rewards**: Create, edit, and delete rewards and badges
- **Assign Rewards**: Manually assign rewards to users based on AI recommendations

### User Interface

- **Submit Actions**: `/user/actions` - Users can submit waste disposal actions
- **View Rewards**: `/user/rewards` - Users can view their points, badges, and activity history
- **Leaderboard**: `/user/leaderboard` - Public leaderboard showing top users

### API Endpoints

- **GET /api/user/rewards**: Get a user's rewards, badges, and actions
- **POST /api/action**: Submit a new action for a user

## Architecture

The gamification system consists of:

1. **Entities**:
   - `GamificationAction`: Defines actions users can take
   - `Reward`: Defines badges and rewards users can earn
   - `UserReward`: Tracks user points and rewards
   - `EngagementPrediction`: Stores AI predictions about user engagement

2. **Event System**:
   - `UserActionListener`: Handles user actions and awards points/badges

3. **AI Integration**:
   - Uses OpenRouter API to predict user engagement
   - Console command for batch processing

## Customization

### Adding New Actions

Create new actions through the admin interface or directly in the database:

```sql
INSERT INTO gamification_action (name, points, description) 
VALUES ('Recycle Electronics', 20, 'Properly dispose of electronic waste at a collection center');
```

### Adding New Rewards

Create new rewards through the admin interface or directly in the database:

```sql
INSERT INTO reward (name, type, points_required, description, image_url) 
VALUES ('Recycling Champion', 'badge', 100, 'Earned by accumulating 100 points', 'https://example.com/badge.png');
```

## Troubleshooting

- **AI Predictions Not Working**: Check your OpenRouter API key and ensure the API is accessible
- **Points Not Being Awarded**: Check the event listener is properly registered in services.yaml
- **Database Errors**: Ensure migrations have been run correctly
