<?php
/**
 * Finance Controller
 * File: modules/Finance/controllers/FinanceController.php
 */
require_once __DIR__ . '/../models/FinanceModel.php';

class FinanceController {
    private $model;
    public function __construct() { $this->model = new FinanceModel(); }

    public function getDashboard($session = null) {
        return [
            'stats'           => $this->model->getDashboardStats($session),
            'revenue_by_type' => $this->model->getRevenueByType($session),
            'monthly_trend'   => $this->model->getMonthlyTrend($session),
            'session_split'   => $this->model->getSessionSplit(),
            'budgets'         => $this->model->getBudgetUtilisation($session),
            'recent'          => $this->model->getRecentTransactions($session, 8),
        ];
    }
    public function listRevenue($filters, $page, $perPage) { return $this->model->getAllRevenue($filters, $page, $perPage); }
    public function recordRevenue($data) {
        if (empty($data['cep_session']) || empty($data['revenue_type']) || empty($data['amount']) || empty($data['revenue_date']))
            return ['success'=>false,'message'=>'Session, type, amount and date are required'];
        if (!is_numeric($data['amount']) || $data['amount'] <= 0)
            return ['success'=>false,'message'=>'Amount must be a positive number'];
        return $this->model->createRevenue($data);
    }
    public function getDailyTotal($session, $date = null) { return $this->model->getDailyTotal($session, $date); }
    public function listBudgets($filters) { return $this->model->getAllBudgets($filters); }
    public function createBudget($data, $lines) {
        if (empty($data['budget_name']) || empty($data['cep_session']))
            return ['success'=>false,'message'=>'Budget name and session are required'];
        return $this->model->createBudget($data, $lines);
    }
    public function getBudget($id) { return $this->model->getBudgetById($id); }
    public function approveBudget($id, $userId) { return $this->model->updateBudgetStatus($id, 'approved', $userId); }
    public function listFundRequests($filters, $page, $perPage) { return $this->model->getFundRequests($filters, $page, $perPage); }
    public function getPipeline($session = null) { return $this->model->getFundRequestPipelineCounts($session); }
    public function submitFundRequest($data) {
        if (empty($data['title']) || empty($data['amount_requested']) || empty($data['cep_session']))
            return ['success'=>false,'message'=>'Title, amount and session are required'];
        return $this->model->createFundRequest($data);
    }
    public function advanceRequest($id, $action, $userId, $data) { return $this->model->advanceFundRequest($id, $action, $userId, $data); }
    public function listDisbursements($filters, $page, $perPage) { return $this->model->getDisbursements($filters, $page, $perPage); }
}