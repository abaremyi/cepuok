<?php
/**
 * Finance Controller — CEP UOK
 * File: modules/Finance/controllers/FinanceController.php
 */
require_once __DIR__ . '/../models/FinanceModel.php';

class FinanceController
{
    private $model;

    public function __construct()
    {
        $this->model = new FinanceModel();
    }

    // ── Dashboard ────────────────────────────────────────────
    public function getDashboard($session = null)
    {
        return [
            'stats'           => $this->model->getDashboardStats($session),
            'revenue_by_type' => $this->model->getRevenueByType($session),
            'monthly_trend'   => $this->model->getMonthlyTrend($session),
            'session_split'   => $this->model->getSessionSplit(),
            'budgets'         => $this->model->getBudgetUtilisation($session),
            'recent'          => $this->model->getRecentTransactions($session, 8),
        ];
    }

    // ── Revenue ──────────────────────────────────────────────
    public function listRevenue($filters, $page, $perPage)  { return $this->model->getAllRevenue($filters, $page, $perPage); }
    public function recordRevenue($data)
    {
        if (empty($data['cep_session']) || empty($data['revenue_type']) || empty($data['amount']) || empty($data['revenue_date']))
            return ['success'=>false,'message'=>'Session, type, amount and date are required'];
        if (!is_numeric($data['amount']) || $data['amount'] <= 0)
            return ['success'=>false,'message'=>'Amount must be a positive number'];
        return $this->model->createRevenue($data);
    }
    public function getRevenueById($id) { return $this->model->getRevenueById($id); }
    public function updateRevenue($data)
    {
        if (empty($data['id'])||empty($data['cep_session'])||empty($data['revenue_type'])||empty($data['amount'])||empty($data['revenue_date']))
            return ['success'=>false,'message'=>'All required fields must be filled'];
        return $this->model->updateRevenue($data);
    }
    public function deleteRevenue($id) { return $this->model->deleteRevenue($id); }
    public function getDailyTotal($session, $date = null) { return $this->model->getDailyTotal($session, $date); }

    // ── Budget Indicators ────────────────────────────────────
    public function getIndicator($session, $year) { return $this->model->getIndicator($session, $year); }
    public function getIndicatorById($id)         { return $this->model->getIndicatorById($id); }
    public function createIndicator($data, $pools, $userId)
    {
        if (empty($data['cep_session']) || empty($data['academic_year']) || empty($data['base_balance']))
            return ['success'=>false,'message'=>'Session, academic year and base balance are required'];
        if (empty($pools))
            return ['success'=>false,'message'=>'At least one pool is required'];
        return $this->model->createIndicator($data, $pools, $userId);
    }
    public function updateIndicator($id, $data, $pools, $userId) { return $this->model->updateIndicator($id, $data, $pools, $userId); }
    public function confirmIndicator($id, $userId)               { return $this->model->confirmIndicator($id, $userId); }
    public function deleteIndicator($id, $userId = null, $isSuperAdmin = false)
    {
        return $this->model->deleteIndicator($id, $userId, $isSuperAdmin);
    }
    
    // ── Budget Quarters ──────────────────────────────────────
    public function getQuartersByIndicator($indicatorId) { return $this->model->getQuartersByIndicator($indicatorId); }
    public function getQuarterById($id)                  { return $this->model->getQuarterById($id); }
    public function getActiveQuarter($session)           { return $this->model->getActiveQuarter($session); }
    public function createQuarter($data, $activities, $userId)
    {
        if (empty($data['indicator_id'])||empty($data['quarter'])||empty($data['budget_name']))
            return ['success'=>false,'message'=>'Indicator, quarter and budget name are required'];
        return $this->model->createQuarter($data, $activities, $userId);
    }
    public function updateQuarter($id, $data, $activities) { return $this->model->updateQuarter($id, $data, $activities); }
    public function approveQuarter($id, $userId)           { return $this->model->approveQuarter($id, $userId); }
    public function reactivateQuarter($id)                 { return $this->model->reactivateQuarter($id); }
    public function deleteQuarter($id)                     { return $this->model->deleteQuarter($id); }

    // ── Fund Requests ────────────────────────────────────────
    public function listFundRequests($filters, $page, $perPage) { return $this->model->getFundRequests($filters, $page, $perPage); }
    public function getFundRequestById($id)                     { return $this->model->getFundRequestById($id); }
    public function createFundRequest($data, $userId)
    {
        if (empty($data['title'])||empty($data['amount_requested'])||empty($data['cep_session'])||empty($data['description']))
            return ['success'=>false,'message'=>'Title, description, amount and session are required'];
        if (!is_numeric($data['amount_requested'])||$data['amount_requested']<=0)
            return ['success'=>false,'message'=>'Amount must be a positive number'];
        return $this->model->createFundRequest($data, $userId);
    }
    public function updateFundRequest($id, $data, $userId)        { return $this->model->updateFundRequest($id, $data, $userId); }
    public function submitFundRequest($id, $userId)               { return $this->model->submitFundRequest($id, $userId); }
    public function deleteFundRequest($id, $userId, $isSA = false){ return $this->model->deleteFundRequest($id, $userId, $isSA); }
    public function addComment($rid, $uid, $comment)              { return $this->model->addFundRequestComment($rid, $uid, $comment); }
    public function presidentAction($id, $action, $userId, $data) { return $this->model->presidentAction($id, $action, $userId, $data); }
    public function disburse($id, $userId, $data)                 { return $this->model->disburse($id, $userId, $data); }
    public function getPipeline($session = null)                   { return $this->model->getFundRequestPipelineCounts($session); }

    // ── Disbursements ────────────────────────────────────────
    public function listDisbursements($filters, $page, $perPage) { return $this->model->getDisbursements($filters, $page, $perPage); }

    // ── Reports ──────────────────────────────────────────────
    public function getFinanceOverview($session, $year)           { return $this->model->getFinanceOverview($session, $year); }
    public function getBudgetDistributionReport($s, $y, $q=null) { return $this->model->getBudgetDistributionReport($s, $y, $q); }
    public function getDisbursementReport($s, $y, $f=[])          { return $this->model->getDisbursementReport($s, $y, $f); }
    public function getFundRequestReport($s, $y, $f=[])           { return $this->model->getFundRequestReport($s, $y, $f); }
}