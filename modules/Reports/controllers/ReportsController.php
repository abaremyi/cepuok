<?php
/**
 * Reports Controller
 * File: modules/Reports/controllers/ReportsController.php
 */
require_once __DIR__ . '/../models/ReportsModel.php';

class ReportsController {
    private $model;
    public function __construct() { $this->model = new ReportsModel(); }

    public function getMemberOverview($session = null) {
        return [
            'summary'    => $this->model->getMemberSummary($session),
            'by_faculty' => $this->model->getMembersByFaculty($session),
            'by_year'    => $this->model->getMembersJoinedByYear($session),
            'by_church'  => $this->model->getMembersByChurch($session),
            'by_family'  => $this->model->getMembersByFamily($session),
        ];
    }

    public function listMembers($filters, $page, $perPage) { return $this->model->getMembersList($filters, $page, $perPage); }

    public function getFinanceOverview($session = null, $year = null) {
        return [
            'summary'        => $this->model->getFinanceSummary($session, $year),
            'monthly'        => $this->model->getMonthlyFinanceReport($session, $year),
            'fund_requests'  => $this->model->getFundRequestReport($session, $year),
        ];
    }

    public function exportMembersCSV($filters) {
        $data = $this->model->exportMembersCSV($filters);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="cep_members_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['#','Membership No','First Name','Last Name','Email','Phone','Gender','Status','Session','Faculty','Program','Church','Family','Year Joined']);
        foreach ($data as $i => $r) {
            fputcsv($out, [
                $i + 1, $r['membership_number'] ?? '', $r['firstname'], $r['lastname'],
                $r['email'], $r['phone'], $r['gender'], $r['status'], $r['cep_session'],
                $r['faculty'] ?? '', $r['program'] ?? '', $r['church_name'] ?? '',
                $r['family_name'] ?? '', $r['year_joined_cep'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportRevenueCSV($filters) {
        // handled in FinanceController / inline
    }
}