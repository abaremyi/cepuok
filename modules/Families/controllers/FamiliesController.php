<?php
/**
 * Families Controller
 * File: modules/Families/controllers/FamiliesController.php
 */
require_once __DIR__ . '/../models/FamiliesModel.php';

class FamiliesController {
    private $model;
    public function __construct() { $this->model = new FamiliesModel(); }

    public function listFamilies($session = null)     { return $this->model->getAllFamilies($session); }
    public function getFamily($id)                    { return $this->model->getFamilyById($id); }
    public function getFamilyMembers($id, $filters)   { return $this->model->getFamilyMembers($id, $filters); }
    public function getUnassigned($session, $search)  { return $this->model->getUnassignedMembers($session, $search); }
    public function getStats()                        { return $this->model->getFamilyStats(); }

    public function create($data) {
        if (empty($data['family_name']) || empty($data['cep_session']))
            return ['success'=>false,'message'=>'Name and session are required'];
        return $this->model->createFamily($data);
    }
    public function update($id, $data) { return $this->model->updateFamily($id, $data); }
    public function delete($id) {
        $f = $this->model->getFamilyById($id);
        if (!$f) return ['success'=>false,'message'=>'Family not found'];
        return $this->model->deleteFamily($id);
    }
    public function assignMembers($familyId, $memberIds) {
        if (empty($memberIds)) return ['success'=>false,'message'=>'No members selected'];
        return $this->model->assignMembers($familyId, $memberIds);
    }
    public function removeMember($memberId) { return $this->model->removeMemberFromFamily($memberId); }
}