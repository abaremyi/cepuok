<?php
/**
 * Choir Controller
 * File: modules/Choir/controllers/ChoirController.php
 */
require_once __DIR__ . '/../models/ChoirModel.php';

class ChoirController {
    private $model;
    public function __construct() { $this->model = new ChoirModel(); }

    // Members
    public function listMembers($filters, $page, $perPage) { return $this->model->getAllChoirMembers($filters, $page, $perPage); }
    public function getStats()                             { return $this->model->getChoirStats(); }
    public function addMember($data) {
        if (empty($data['full_name'])) return ['success'=>false,'message'=>'Full name is required'];
        return $this->model->createChoirMember($data);
    }
    public function updateMember($id, $data) { return $this->model->updateChoirMember($id, $data); }
    public function removeMember($id)        { return $this->model->deleteChoirMember($id); }

    // Songs
    public function listSongs($filters, $page, $perPage) { return $this->model->getAllSongs($filters, $page, $perPage); }
    public function getSongStats()                        { return $this->model->getSongStats(); }
    public function addSong($data) {
        if (empty($data['title'])) return ['success'=>false,'message'=>'Song title is required'];
        return $this->model->createSong($data);
    }
    public function updateSong($id, $data) { return $this->model->updateSong($id, $data); }
    public function deleteSong($id)        { return $this->model->deleteSong($id); }

    // Rehearsals & Attendance
    public function listRehearsals($filters, $page, $perPage) { return $this->model->getAllRehearsals($filters, $page, $perPage); }
    public function createRehearsal($data) {
        if (empty($data['rehearsal_date'])) return ['success'=>false,'message'=>'Date is required'];
        return $this->model->createRehearsal($data);
    }
    public function getRehearsalAttendance($id) { return $this->model->getRehearsalAttendance($id); }
    public function saveAttendance($id, $rows)  { return $this->model->saveAttendance($id, $rows); }
    public function getMemberRate($id)           { return $this->model->getMemberAttendanceRate($id); }
}