<?php
/**
 * Credential Wallet Controller
 * File: modules/CredentialWallet/controllers/CredentialWalletController.php
 */
require_once __DIR__ . '/../models/CredentialWalletModel.php';

class CredentialWalletController
{
    private $model;

    public function __construct()
    {
        $this->model = new CredentialWalletModel();
    }

    public function list(array $filters = []): array
    {
        $rows = $this->model->getAll($filters);
        return ['success' => true, 'data' => $rows, 'total' => count($rows)];
    }

    public function get(int $id, int $userId): array
    {
        $row = $this->model->getById($id);
        if (!$row) return ['success' => false, 'message' => 'Credential not found'];
        $this->model->logView($id, $userId);
        return ['success' => true, 'data' => $row];
    }

    public function create(array $data, int $userId): array
    {
        return $this->model->create($data, $userId);
    }

    public function update(int $id, array $data, int $userId): array
    {
        return $this->model->update($id, $data, $userId);
    }

    public function delete(int $id, int $userId): array
    {
        return $this->model->delete($id, $userId);
    }

    public function toggleStatus(int $id, int $userId): array
    {
        return $this->model->toggleStatus($id, $userId);
    }

    public function revealPassword(int $id, int $userId): array
    {
        return $this->model->revealPassword($id, $userId);
    }

    public function stats(): array
    {
        return ['success' => true, 'data' => $this->model->getStats()];
    }

    public function auditLog(int $id): array
    {
        return ['success' => true, 'data' => $this->model->getAuditLog($id)];
    }

    public function categories(): array
    {
        return ['success' => true, 'data' => CredentialWalletModel::categories()];
    }
}