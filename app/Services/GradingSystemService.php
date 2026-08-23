<?php

namespace App\Services;

use App\Core\Database;

class GradingSystemService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT gs.*, d.code AS department_code
             FROM grading_systems gs
             LEFT JOIN departments d ON d.id = gs.department_id
             ORDER BY gs.description'
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['components'] = $this->getComponentsForSystem((int) $row['id']);
        }
        return $rows;
    }

    public function getByDepartment(int $departmentId): array
    {
        $stmt = $this->db->prepare(
            'SELECT gs.*, d.code AS department_code
             FROM grading_systems gs
             LEFT JOIN departments d ON d.id = gs.department_id
             WHERE gs.department_id = ?
             ORDER BY gs.description'
        );
        $stmt->execute([$departmentId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['components'] = $this->getComponentsForSystem((int) $row['id']);
        }
        return $rows;
    }

    public function getForDropdown(): array
    {
        $stmt = $this->db->query('SELECT id, description, department_id FROM grading_systems ORDER BY description');
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM grading_systems WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['components'] = $this->getComponentsForSystem($id);
            $row['component_ids'] = array_column($row['components'], 'component_id');
        }
        return $row ?: null;
    }

    private function getComponentsForSystem(int $gradingSystemId): array
    {
        $stmt = $this->db->prepare(
            'SELECT gc.component_id, c.code, c.description, c.percentage
             FROM grading_components gc
             JOIN components c ON c.id = gc.component_id
             WHERE gc.grading_id = ?
             ORDER BY c.code'
        );
        $stmt->execute([$gradingSystemId]);
        return $stmt->fetchAll();
    }

    public function calculateTotalPercentage(array $componentIds): float
    {
        if (empty($componentIds)) return 0.0;
        $placeholders = implode(',', array_fill(0, count($componentIds), '?'));
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(percentage),0) FROM components WHERE id IN ($placeholders)");
        $stmt->execute($componentIds);
        return (float) $stmt->fetchColumn();
    }

    public function ensureValidTotal(float $total): void
    {
        if ($total > 100.0) {
            throw new \RuntimeException('Total percentage cannot exceed 100. Current total: ' . $total . '%');
        }
    }

    public function create(array $data, array $componentIds): int
    {
        $total = $this->calculateTotalPercentage($componentIds);
        $this->ensureValidTotal($total);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO grading_systems (description, total_percentage, department_id, created_at, updated_at)
                 VALUES (:description, :total_percentage, :department_id, NOW(), NOW())'
            );
            $stmt->execute([
                'description' => $data['description'],
                'total_percentage' => $total,
                'department_id' => $data['department_id'],
            ]);
            $gradingId = (int) $this->db->lastInsertId();

            $this->syncComponents($gradingId, $componentIds);

            $this->db->commit();
            return $gradingId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data, array $componentIds): bool
    {
        $total = $this->calculateTotalPercentage($componentIds);
        $this->ensureValidTotal($total);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE grading_systems SET description = :description, total_percentage = :total_percentage, department_id = :department_id, updated_at = NOW() WHERE id = :id'
            );
            $stmt->execute([
                'description' => $data['description'],
                'total_percentage' => $total,
                'department_id' => $data['department_id'],
                'id' => $id,
            ]);

            $this->syncComponents($id, $componentIds);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function syncComponents(int $gradingId, array $componentIds): void
    {
        $stmt = $this->db->prepare('DELETE FROM grading_components WHERE grading_id = ?');
        $stmt->execute([$gradingId]);

        if (empty($componentIds)) return;

        $stmt = $this->db->prepare('INSERT INTO grading_components (grading_id, component_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        foreach ($componentIds as $cid) {
            $stmt->execute([$gradingId, $cid]);
        }
    }

    public function delete(int $id): bool
    {
        // Check dependents before delete is done in controller, but also handle FK
        $stmt = $this->db->prepare('DELETE FROM grading_systems WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function hasDependents(int $id): array
    {
        $found = [];
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM subject_offerings WHERE grading_id = ? LIMIT 1');
            $stmt->execute([$id]);
            if ($stmt->fetch()) $found[] = 'subject_offerings';
        } catch (\Throwable $e) {}
        return $found;
    }
}
