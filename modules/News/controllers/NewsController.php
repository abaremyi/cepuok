
<?php
/**
 * News Controller
 * File: modules/News/controllers/NewsController.php
 * Handles news business logic
 */

// Fix the database path - go up 3 levels to reach the root, then to config
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/database.php';
require_once dirname(__FILE__) . '/../models/NewsModel.php';

class NewsController {
    private $db;
    public $newsModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->newsModel = new NewsModel($this->db);
    }

    /**
     * Get news items with pagination
     * @param array $params Parameters: limit, offset, category
     * @return array Response array with success, data, and metadata
     */
    public function getNewsItems($params = []) {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : 4;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $category = isset($params['category']) ? $params['category'] : null;
            $upcoming = isset($params['upcoming']) ? (bool)$params['upcoming'] : false;

            // Validate limit (max 50)
            if ($limit > 50) {
                $limit = 50;
            }

            // Get news items
            $newsItems = $this->newsModel->getNewsItems($limit, $offset, $category, $upcoming);

            // Format dates for display
            foreach ($newsItems as &$item) {
                $item['formatted_date'] = date('F j, Y', strtotime($item['published_date']));
                $item['short_date'] = date('M d', strtotime($item['published_date']));
                $item['day'] = date('d', strtotime($item['published_date']));
                $item['month'] = date('M', strtotime($item['published_date']));
                $item['year'] = date('Y', strtotime($item['published_date']));
            }

            // Get total count
            $total = $this->newsModel->getTotalCount($category);

            return [
                'success' => true,
                'data' => $newsItems,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'category' => $category
            ];

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve news items.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single news item by ID
     * @param int $id News ID
     * @return array Response array
     */
    public function getNewsById($id) {
        try {
            if (empty($id)) {
                return [
                    'success' => false,
                    'message' => 'News ID is required.'
                ];
            }

            $news = $this->newsModel->getNewsById($id);

            if ($news) {
                // Format date
                $news['formatted_date'] = date('F j, Y', strtotime($news['published_date']));
                
                return [
                    'success' => true,
                    'data' => $news
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'News item not found.'
                ];
            }

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve news item.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get related news items
     * @param int $currentId Current news ID
     * @param string $category Category
     * @param int $limit Number of items
     * @return array Response array
     */
    public function getRelatedNews($currentId, $category = null, $limit = 3) {
        try {
            $relatedNews = $this->newsModel->getRelatedNews($currentId, $category, $limit);

            // Format dates
            foreach ($relatedNews as &$item) {
                $item['formatted_date'] = date('F j, Y', strtotime($item['published_date']));
            }

            return [
                'success' => true,
                'data' => $relatedNews
            ];

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve related news.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get latest news items
     * @param int $limit Number of items
     * @return array Response array
     */
    public function getLatestNews($limit = 5) {
        try {
            $latestNews = $this->newsModel->getLatestNews($limit);

            // Format dates
            foreach ($latestNews as &$item) {
                $item['formatted_date'] = date('F j, Y', strtotime($item['published_date']));
            }

            return [
                'success' => true,
                'data' => $latestNews
            ];

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve latest news.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get featured news items
     * @param int $limit Number of items
     * @return array Response array
     */
    public function getFeaturedNews($limit = 5) {
        try {
            $featuredNews = $this->newsModel->getFeaturedNews($limit);

            // Format dates
            foreach ($featuredNews as &$item) {
                $item['formatted_date'] = date('F j, Y', strtotime($item['published_date']));
            }

            return [
                'success' => true,
                'data' => $featuredNews
            ];

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve featured news.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get upcoming events
     * @param int $limit Number of events
     * @return array Response array
     */
    public function getUpcomingEvents($limit = 4) {
        try {
            $upcomingEvents = $this->newsModel->getUpcomingEvents($limit);

            // Format dates
            foreach ($upcomingEvents as &$event) {
                $event['formatted_date'] = date('F j, Y', strtotime($event['published_date']));
                $event['formatted_time'] = date('g:i A', strtotime($event['published_date']));
            }

            return [
                'success' => true,
                'data' => $upcomingEvents
            ];

        } catch (Exception $e) {
            error_log("News Controller Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve upcoming events.',
                'error' => $e->getMessage()
            ];
        }
    }
}
?>