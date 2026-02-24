<?php
/**
 * Supporters Controller
 * File: modules/Supporters/controllers/SupportersController.php
 */
require_once __DIR__ . '/../models/SupportersModel.php';

class SupportersController {
    private $model;
    public function __construct() { $this->model = new SupportersModel(); }

    public function list($filters, $page, $perPage) { return $this->model->getAllSupporters($filters, $page, $perPage); }
    public function get($id)                        { return $this->model->getSupporterById($id); }
    public function getStats()                      { return $this->model->getStats(); }

    public function create($data) {
        if (empty($data['firstname']) || empty($data['lastname']))
            return ['success'=>false,'message'=>'First and last name are required'];
        return $this->model->createSupporter($data);
    }
    public function update($id, $data) {
        $s = $this->model->getSupporterById($id);
        if (!$s) return ['success'=>false,'message'=>'Supporter not found'];
        return $this->model->updateSupporter($id, $data);
    }
    public function delete($id)                          { return $this->model->deleteSupporter($id); }
    public function addContribution($id, $data) {
        if (empty($data['contribution_date']))
            return ['success'=>false,'message'=>'Contribution date is required'];
        return $this->model->addContribution($id, $data);
    }
}