<?php
/**
 * Projects Controller
 * File: modules/Projects/controllers/ProjectsController.php
 */
require_once __DIR__ . '/../models/ProjectsModel.php';

class ProjectsController {
    private $model;
    public function __construct() { $this->model = new ProjectsModel(); }

    public function list($filters, $page, $perPage) { return $this->model->getAllProjects($filters, $page, $perPage); }
    public function get($id)                        { return $this->model->getProjectById($id); }
    public function getStats($session = null)       { return $this->model->getStats($session); }

    public function create($data) {
        if (empty($data['title'])) return ['success'=>false,'message'=>'Project title is required'];
        return $this->model->createProject($data);
    }
    public function update($id, $data) {
        $p = $this->model->getProjectById($id);
        if (!$p) return ['success'=>false,'message'=>'Project not found'];
        return $this->model->updateProject($id, $data);
    }
    public function delete($id) { return $this->model->deleteProject($id); }

    public function addTask($projectId, $data) {
        if (empty($data['title'])) return ['success'=>false,'message'=>'Task title is required'];
        return $this->model->createTask($projectId, $data);
    }
    public function updateTaskStatus($taskId, $status) { return $this->model->updateTaskStatus($taskId, $status); }

    public function addUpdate($projectId, $text, $progress, $userId) {
        if (empty($text)) return ['success'=>false,'message'=>'Update text is required'];
        return $this->model->addUpdate($projectId, $text, $progress, $userId);
    }
}